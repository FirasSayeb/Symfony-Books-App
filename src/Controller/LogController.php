<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\BookRepository;
use App\Repository\AuteurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogController extends AbstractController
{
    #[Route('/log', name: 'app_log')]
    public function index(Request $request, AuteurRepository $auteurRepository): Response
    {  
        $auteur = new Auteur();
        
        $form = $this->createFormBuilder($auteur)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)  
            ->getForm();
        
        $form->handleRequest($request);
            
        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve the author from the database based on the email
            $existingAuteur = $auteurRepository->findOneBy(['email' => $auteur->getEmail()]);
            
            if ($existingAuteur && $existingAuteur->getPassword() === $auteur->getPassword()) { 
                // Store the authenticated author's ID in the session for future use
                $request->getSession()->set('authenticated_auteur_id', $existingAuteur->getId());
                
                // Redirect the author to a secure page
                return $this->redirectToRoute('app_book_index');
            } else {
                // Display an error message
                $this->addFlash('error', 'Invalid email or password.');
            }
        }   
        
        return $this->render('log/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/books', name: 'app_book_index')]
    public function bookIndex(Request $request, AuteurRepository $auteurRepository, BookRepository $bookRepository): Response
    {
        // Check if the authenticated author ID is stored in the session
        $authenticatedAuteurId = $request->getSession()->get('authenticated_auteur_id');
        
        if (!$authenticatedAuteurId) {
            // If the author is not logged in, redirect them to the login page
            return $this->redirectToRoute('app_log');
        }
        
        // Retrieve the authenticated author from the database
        $authenticatedAuteur = $auteurRepository->find($authenticatedAuteurId);
        
        // Retrieve the books added by the authenticated author
        $books = $bookRepository->findBy(['auteur' => $authenticatedAuteur]);
        
        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }
}
