<?php

namespace App\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;
use App\Entity\Book;
use App\Form\AuthorType;
use App\Form\BookType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;

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
		$page = $request->query->get('page');
		if (!is_numeric($page) || $page < 0) $page = 0;
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
		$page = $request->query->get('page');
		if (!is_numeric($page) || $page < 0) $page = 0;
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
				$filename = md5(uniqid()) . '.' . $file->guessExtension(); 
				$file->move(
					$this->getParameter('brochures_directory'),
					$filename
				);
				$book->setBrochure($filename);
			}
			else $book->setBrochure(null);
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
		$page = $request->query->get('page');
		if (!is_numeric($page) || $page < 0) $page = 0;
		$authors = $this->getDoctrine()->getRepository(Author::class)->getAtPage($page, $this->getParameter('authors_per_page'));
		foreach ($authors as $author) 
		{
			$author->setName(substr($author->getName(), 0, 1) . '.');
			if (strlen($author->getMiddleName()))
				$author->setMiddleName(substr($author->getMiddleName(), 0, 1) . '.');
		}
		
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
		$page = $request->query->get('page');
		if (!is_numeric($page) || $page < 0) $page = 0;
		$books = $this->getDoctrine()->getRepository(Book::class)->getAtPage($page, $this->getParameter('books_per_page'));
		
		return $this->render('list/book_list.html.twig', array(
			'list' => $books));
	}
    /**
     * @Route("/authors/{author_id}", name="author_page")
     */
    public function manageAuthor($author_id, Request $request)
    {
		$page = $request->query->get('page');
		if (!is_numeric($page) || $page < 0) $page = 0;
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
			->add('save', SubmitType::class, array('label' => 'Submit'))
			->add('delete', SubmitType::class, array('label' => 'Delete'))
			->add('books_save', SubmitType::class, array('label' => 'Save Books'))
			->add('books_delete', SubmitType::class, array('label' => 'Remove Books from Author'));
		$form->get('save')->getData();
		
		$author->getBooks()->takeSnapshot();
		$arr = new ArrayCollection();
		foreach ($author->getBooks() as $book)
		{
			if (!$arr->contains($book)) {
				$arr[] = $book;
			}
		}
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			if ($form->get('delete')->isClicked())
			{
				$entityManager->remove($author);
			}
			else
			{
				$author = $form->getData();
				$books = $author->getBooks();
				$books2 = $author->getBooks()->getSnapshot();
				if ($form->get('books_save')->isClicked())
				{
					/*echo "DIFFS<br>";
					foreach ($author->getBooks()->getDeleteDiff() as $book) echo $book->getName() . "<br>"; echo "<br>";*/
					foreach ($books2 as $book)
					{
						$author->addBook($book);
					}
				}
				if ($form->get('books_delete')->isClicked())
				{
					foreach ($books as $book)
					{
						if ($arr->contains($book))
						{
							$arr->removeElement($book);
						}
					}
					$books->clear();
					foreach ($arr as $book)
					{
						$author->addBook($book);
					}
				}
				$entityManager->flush();
			}
			//return $this->redirectToRoute('authors');
		}
        return $this->render('forms/author_manage.html.twig', array(
            'form' => $form->createView(),
			'page' => $page,
        ));
	}
	/**
     * @Route("/books/{book_id}", name="book_page")
     */
    public function manageBook($book_id, Request $request)
    {
		$page = $request->query->get('page');
		if (!is_numeric($page) || $page < 0) $page = 0;
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
			->add('authors_save', SubmitType::class, array('label' => 'Save Authors'))
			->add('authors_delete', SubmitType::class, array('label' => 'Remove Authors from Book'));
		$current_brochure = $book->getBrochure();
		$is_brochure_exists = $current_brochure != null && file_exists($this->getParameter('brochures_directory') . $current_brochure);
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
				if ($form->get('authors_save')->isClicked())
				{
					foreach ($authors as $author)
					{
						$author->addBook($book);
					}
				}
				if ($form->get('authors_delete')->isClicked())
				{
					foreach ($authors as $author)
					{
						$author->removeBook($book);
					}
				}
				if ($book->getBrochure() != null)
				{
					if ($is_brochure_exists) unlink($this->getParameter('brochures_directory') . $current_brochure);
					$file =  $form->get('brochure')->getData();
					$filename = md5(uniqid()) . '.' . $file->guessExtension(); 
					$file->move(
						$this->getParameter('brochures_directory'),
						$filename
					);
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
			}
			$entityManager->flush();
			//return $this->redirectToRoute('books');
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
}
