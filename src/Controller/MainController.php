<?php

namespace App\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use App\Entity\Book;
use App\Form\AuthorType;
use App\Form\BookType;
use App\Service\FileUploader;

class MainController extends AbstractController
{
    /**
     * @Route("/main", name="main")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
	
    /**
     * @Route("/create-author", name="create-author")
     */
    public function createAuthor(Request $request)
    {
		$page = $this->getPage($request->query->get('page'));
		$author = new Author();
		$form = $this->createForm(AuthorType::class, $author)
			->add('books', EntityType::class, array(
					'class' => Book::class,
					'choice_label' => 'Name',
					'multiple' => true,
					'expanded' => true,
					'mapped' => true,
					'query_builder' => function (EntityRepository $er) use ($page) {
						return $er->createQueryBuilder('a')
							->orderBy('a.Name', 'ASC')
							->setFirstResult($page * $this->getParameter('authors_per_page'))
							->setMaxResults($this->getParameter('authors_per_page'));
						}
					)
				)
			->add('save', SubmitType::class, array('label' => 'Create'))
			->add('delete', SubmitType::class, array('label' => 'Delete'));
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$author = $form->getData();
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($author);
			$entityManager->flush();
			return $this->redirectToRoute('authors');
		}
        return $this->render('forms/author_create.html.twig', array(
            'form' => $form->createView(),
			'page' => $page,
        ));
    }
	
    /**
     * @Route("/create-book", name="create-book")
     */
    public function createBook(Request $request)
    {
		$page = $this->getPage($request->query->get('page'));
		$book = new Book();
		$form = $this->createForm(BookType::class, $book)
		->add('authors', EntityType::class, array(
				'class' => Author::class,
				'choice_label' => 'SurnameAndInitials',
				'multiple' => true,
				'expanded' => true,
                'mapped' => true,
				'query_builder' => function (EntityRepository $er) use ($page) {
					return $er->createQueryBuilder('a')
						->orderBy('a.Surname', 'ASC')
						->setFirstResult($page * $this->getParameter('books_per_page'))
						->setMaxResults($this->getParameter('books_per_page'));
					}
				)
			);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$book = $form->getData();
			$entityManager = $this->getDoctrine()->getManager();
			$file =  $form->get('brochure')->getData();
			if ($file != null)
			{
				$filename = $fileUploader->upload($file);
				$book->setBrochure($filename);
			}
			foreach ($book->getAuthors() as $author) $author->addBook($book);
			$entityManager->persist($book);
			$entityManager->flush();
			return $this->redirectToRoute('books');
		}
        return $this->render('forms/book_create.html.twig', array(
            'form' => $form->createView(),
			'page' => $page,
        ));
    }
	
    /**
     * @Route("/authors", name="authors")
     */
    public function showAuthors(Request $request)
    {
		$page = $this->getPage($request->query->get('page'));
		$authors = $this->getDoctrine()->getRepository(Author::class)->getAtPage($page, $this->getParameter('authors_per_page'));
		return $this->render('list/author_list.html.twig', array(
			'list' => $authors,
			'page' => $page
			));
	}
	
    /**
     * @Route("/books", name="books")
     */
    public function showBooks(Request $request)
    {
		$page = $this->getPage($request->query->get('page'));
		$books = $this->getDoctrine()->getRepository(Book::class)->getAtPage($page, $this->getParameter('books_per_page'));
		return $this->render('list/book_list.html.twig', array(
			'list' => $books,
			'page' => $page,
			));
	}
	
    /**
     * @Route("/authors/{author_id}", name="author_page")
     */
    public function manageAuthor($author_id, Request $request)
    {
		$page = $this->getPage($request->query->get('page'));
		$author = $this->getDoctrine()->getRepository(Author::class)->findById($author_id);
		if ($author === null) return $this->redirectToRoute('authors');
		$form = $this->createForm(AuthorType::class, $author)
			->add('books', EntityType::class, array(
				'class' => Book::class,
				'choice_label' => 'Name',
				'multiple' => true,
				'expanded' => true,
                'mapped' => true,
				'query_builder' => function (EntityRepository $er) use ($page) {
					return $er->createQueryBuilder('a')
						->orderBy('a.Name', 'ASC')
						->setFirstResult($page * $this->getParameter('authors_per_page'))
						->setMaxResults($this->getParameter('authors_per_page'));
					}
				)
			)
			->add('save', SubmitType::class, array('label' => 'Save'))
			->add('delete', SubmitType::class, array('label' => 'Delete'))
			->add('books_delete', SubmitType::class, array('label' => 'Remove Books from Author'));
		
		$books_old = new ArrayCollection();
		foreach ($author->getBooks() as $book)
		{
			if (!$books_old->contains($book)) {
				$books_old[] = $book;
			}
		}
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			if ($form->get('delete')->isClicked())
			{
				$entityManager->remove($author);
				$entityManager->flush();
				return $this->redirectToRoute('authors');
			}
			else
			{
				$author = $form->getData();
				$books = $author->getBooks();
				// Delete books only when books_delete button is clicked
				// Otherwise, just save Author Entity
				if ($form->get('books_delete')->isClicked())
				{
					foreach ($books as $book)
					{
						if ($books_old->contains($book))
						{
							$books_old->removeElement($book);
						}
					}
					$books->clear();
					foreach ($books_old as $book)
					{
						$author->addBook($book);
					}
					$entityManager->flush();
					return $this->redirect($this->generateUrl('authors') . '/' . $author_id);
				}
				else
				{
					/*echo "DIFFS<br>";
					foreach ($author->getBooks()->getDeleteDiff() as $book) echo $book->getName() . "<br>"; echo "<br>";*/
					// Unfortunately, when the form is submitted an Author Entity refreshes completely.
					// Which means, if I am to add any book to an Author - books array should be refilled
					foreach ($books_old as $book)
					{
						$author->addBook($book);
					}
					$entityManager->flush();
				}
			}
		}
        return $this->render('forms/author_manage.html.twig', array(
            'form' => $form->createView(),
			'page' => $page,
        ));
	}
	
	/**
     * @Route("/books/{book_id}", name="book_page")
     */
    public function manageBook($book_id, Request $request, FileUploader $fileUploader)
    {
		$page = $this->getPage($request->query->get('page'));
		$book = $this->getDoctrine()->getRepository(Book::class)->findById($book_id);
		if ($book === null) return $this->redirectToRoute('books');
		$form = $this->createForm(BookType::class, $book)
			->add('authors', EntityType::class, array(
				'class' => Author::class,
				'choice_label' => 'SurnameAndInitials',
				'multiple' => true,
				'expanded' => true,
                'mapped' => true,
				'query_builder' => function (EntityRepository $er) use ($page) {
					return $er->createQueryBuilder('a')
						->orderBy('a.Surname', 'ASC')
						->setFirstResult($page * $this->getParameter('books_per_page'))
						->setMaxResults($this->getParameter('books_per_page'));
					}
				)
			)
			->add('delete', SubmitType::class, array('label' => 'Delete Book'))
			->add('authors_delete', SubmitType::class, array('label' => 'Remove Authors from Book'));
		$current_brochure = $book->getBrochure();
		$is_brochure_exists = $current_brochure != null && file_exists($fileUploader->getTargetDirectory() . $current_brochure);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			$book = $form->getData();
			$authors = $book->getAuthors();
			if ($form->get('delete')->isClicked())
			{
				foreach($authors as $author)
				{
					$author->removeBook($book);
				}
				$entityManager->remove($book);
				$entityManager->flush();
				return $this->redirectToRoute('books');
			}
			else
			{
				if ($form->get('authors_delete')->isClicked())
				{
					foreach ($authors as $author)
					{
						$author->removeBook($book);
					}
					$entityManager->flush();
					return $this->redirect($this->generateUrl('books') . '/' . $book_id);
				}
				else
				{
					foreach ($authors as $author)
					{
						$author->addBook($book);
					}
				}
				$file =  $form->get('brochure')->getData();
				if ($file != null)
				{
					if ($is_brochure_exists) unlink($fileUploader->getTargetDirectory() . $current_brochure);
					$filename = $fileUploader->upload($file);
					$book->setBrochure($filename);
					$current_brochure = $filename;
				}
				else
				{
					if ($is_brochure_exists)
					{
						$book->setBrochure($current_brochure);
					}
					else
					{
						$book->setBrochure(null);
						$current_brochure = $this->getParameter('brochures_default_file');
					}
				}
				$entityManager->flush();
			}
		}
		else
		{
			if (!$is_brochure_exists) $current_brochure = $current_brochure = $this->getParameter('brochures_default_file');
		}
        return $this->render('forms/book_manage.html.twig', array(
            'form' => $form->createView(),
			'brochure' => $current_brochure,
			'page' => $page,
        ));
	}
	
	// Returns 0 or page number if $page is numeric
	private function getPage($page)
	{
		return (!is_numeric($page) || $page < 0) ? 0 : $page;
	}
}
