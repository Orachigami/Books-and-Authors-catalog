<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AuthorType;
use App\Form\BookType;
use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
		$author = new Author();
		$form = $this->createForm(AuthorType::class, $author);
		
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
    public function showAuthors()
    {
		$authors = $this->getDoctrine()->getRepository(Author::class)->getAll();
		
		foreach ($authors as $author) 
		{
			$author->setName(substr($author->getName(), 0, 1) . '.');
			if (strlen($author->getMiddleName()))
				$author->setMiddleName(substr($author->getMiddleName(), 0, 1) . '.');
		}
		
		return $this->render('list/author_list.html.twig', array(
			'list' => $authors));
	}
	
    /**
     * @Route("/books", name="books")
     */
    public function showBooks()
    {
		$books = $this->getDoctrine()->getRepository(Book::class)->getAll();
		
		return $this->render('list/book_list.html.twig', array(
			'list' => $books));
	}
    /**
     * @Route("/authors/{author_id}", name="author_page")
     */
    public function manageAuthor($author_id, Request $request)
    {
		$author = $this->getDoctrine()->getRepository(Author::class)->findById($author_id);
		$form = $this->createForm(AuthorType::class, $author)->add('delete', SubmitType::class, array('label' => 'Delete'));
		
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
		$form = $this->createForm(BookType::class, $book)->add('delete', SubmitType::class, array('label' => 'Delete'));
		
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
        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
	}
	
}
