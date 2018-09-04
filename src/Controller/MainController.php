<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AuthorType;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;

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
		
        return $this->render('authortype.html.twig', array(
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
		
		return $this->render('list/list.html.twig', array(
			'list' => $authors));
	}
	
    /**
     * @Route("/authors/{author_id}", name="author_page")
     */
    public function manageAuthor($author_id, Request $request)
    {
		$author = $this->getDoctrine()->getRepository(Author::class)->findById($author_id);
		$form = $this->createForm(AuthorType::class, $author);
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			$author = $form->getData();
			$entityManager->flush();

			return $this->redirectToRoute('authors');
		}
		
        return $this->render('authortype.html.twig', array(
            'form' => $form->createView(),
        ));
	}
}
