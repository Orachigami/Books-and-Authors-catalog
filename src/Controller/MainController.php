<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;
use App\Entity\Book;
use App\Form\AuthorType;
use App\Form\BookType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MainController extends AbstractController
{
    /**
     * @Route("/main", name="main")
     */
    public function index()
    {
		/*$entityManager = $this->getDoctrine()->getManager();
		$author = $this->getDoctrine()->getRepository(Author::class)->findById(2);
		$book = $this->getDoctrine()->getRepository(Book::class)->findById(1);
		$book->addAuthor($author);
		$entityManager->flush();*/
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
	
    /**
     * @Route("/create-author", name="create-author")
     */
    public function createAuthor()
    {
		$author = new Author();
		$form = $this->createForm(AuthorType::class, $author)
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
        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
	
    /**
     * @Route("/create-book", name="create-book")
     */
    public function createBook(Request $request)
    {
		$book = new Book();
		$form = $this->createForm(BookType::class, $book);
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$book = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($book);
			$entityManager->flush();

			return $this->redirectToRoute('main');
		}
        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
	
    /**
     * @Route("/authors", name="authors")
     */
    public function showAuthors(Request $request)
    {
		$page = $request->query->get('page');
		if (is_numeric($page) && $page < 0) $page = 0;
		$authors = $this->getDoctrine()->getRepository(Author::class)->getAll($page);
		
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
		if (is_numeric($page) && $page < 0) $page = 0;
		$books = $this->getDoctrine()->getRepository(Book::class)->getAll($page);
		
		return $this->render('list/book_list.html.twig', array(
			'list' => $books));
	}
    /**
     * @Route("/authors/{author_id}", name="author_page")
     */
    public function manageAuthor($author_id, Request $request)
    {
		$author = $this->getDoctrine()->getRepository(Author::class)->findById($author_id);
		$form = $this->createForm(AuthorType::class, $author)
			->add('delete', SubmitType::class, array('label' => 'Delete'));
		
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
			}
			$entityManager->flush();
			return $this->redirectToRoute('authors');
		}
        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
	}
	/**
     * @Route("/books/{book_id}", name="book_page")
     */
    public function manageBook($book_id, Request $request)
    {
		$book = $this->getDoctrine()->getRepository(Book::class)->findById($book_id);
		$form = $this->createForm(BookType::class, $book)
			->add('delete', SubmitType::class, array('label' => 'Delete'))
            ->add('saveBook', SubmitType::class, array('label' => 'Submit'));
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			if ($form->get('delete')->isClicked())
			{
				$entityManager->remove($book);
			}
			else
			{
				$book = $form->getData();
				
			}
			$entityManager->flush();
			return $this->redirectToRoute('books');
		}
        return $this->render('form_book.html.twig', array(
            'form' => $form->createView(),
        ));
	}
}
