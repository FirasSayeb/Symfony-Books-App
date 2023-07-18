<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Client;
use App\Repository\BookRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(Request $request, ClientRepository $repo): Response
    {
        $client = new Client();

        $form = $this->createFormBuilder($client)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingClient = $repo->findOneBy(['email' => $client->getEmail()]);

            if ($existingClient && $existingClient->getPassword() === $client->getPassword()) {
                $request->getSession()->set('authenticated_client_id', $existingClient->getId());
                // Redirect the client to a secure page
                return $this->redirectToRoute('app_book_list');
            } else {
                // Display an error message
                $this->addFlash('error', 'Invalid email or password.');
            }
        }

        return $this->render('login/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book-list', name: 'app_book_list')]  
    public function bookIndex(Request $request, BookRepository $bookRepository): Response
    {
        

        // Retrieve all the books
        $books = $bookRepository->findAll();

        return $this->render('book/list.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/buy/{id}', name: 'app_book_buy')]
    public function buy(Request $request, BookRepository $bookRepository, EntityManagerInterface $entityManager, $id): Response
    {
        // Check if the authenticated client ID is stored in the session
        $authenticatedClientId = $request->getSession()->get('authenticated_client_id');
    
        if (!$authenticatedClientId) {
            // If the client is not logged in, redirect them to the login page
            return $this->redirectToRoute('app_login');
        }
    
        // Retrieve the client by ID
        $client = $entityManager->getRepository(Client::class)->find($authenticatedClientId);
    
        if (!$client) {
            // Handle the case where the client is not found
            // For example, redirect to an error page or display an error message
        }
    
        // Retrieve the book by ID
        $book = $bookRepository->find($id);
    
        if (!$book) {
            // Handle the case where the book is not found
            // For example, redirect to an error page or display an error message
        }
    
        // Add the book to the client's list of books
        $client->addBook($book);
    
        // Update the quantity of the book
        $book->setQuantity($book->getQuantity() - 1);
    
        // Save the changes to the database
        $entityManager->flush();
    
        // Redirect the client to a success page or display a success message
        // For demonstration purposes, let's redirect back to the book list page
        return $this->redirectToRoute('app_book_list');
    }
    #[Route('/client_books', name: 'app_client_books')]
public function clientBooks(Request $request, ClientRepository $clientRepository): Response
{
    // Get the authenticated client ID from the session
    $authenticatedClientId = $request->getSession()->get('authenticated_client_id');

    // Retrieve the client by ID
    $client = $clientRepository->find($authenticatedClientId);  

    if (!$client) {
        // Handle the case where the client is not found
        // For example, redirect to an error page or display an error message
    }
   
    // Get the books bought by the client
    $books = $client->getBooks();

    return $this->render('client/books.html.twig', [
        'books' => $books   
    ]);
}


    
      
}
