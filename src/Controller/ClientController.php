<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;



#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/client/edit/{id}', name: 'editClient', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $email = $request->request->get('email');

            $client->setFirstName($firstName);
            $client->setLastName($lastName);
            $client->setEmail($email);

            $entityManager->flush();

            return new Response('success');
        }

        return new Response('error');
    }


    #[Route('/client/delete/{id}', name: 'deleteClient', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {    
        if ($this->isCsrfTokenValid('delete' , $request->request->get('_token'))) {   
            $entityManager->remove($client);
            $entityManager->flush();
        }
        return new Response('success');
    }


    public function register(Request $request): Response
    {
        $errors = $request->query->get('errors');
        return $this->render('registerClients.html.twig', [
            'errors' => $errors,
        ]);
    }


    public function handleRegister(Request $request , EntityManagerInterface $entityManager , ClientRepository $clientRepository , ValidatorInterface $validator): Response
    {

            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $email = $request->request->get('email');
            $client = new Client();
            $client->setFirstName($firstName);
            $client->setLastName($lastName);
            $client->setEmail($email);

            $existingClient = $clientRepository->findOneBy(['email' => $email]);

            
            if ($existingClient) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'This email already exists in our database'
                ]);
            }

            $errors = $validator->validate($client);
       
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }      
            }

            $entityManager->persist($client);
            $entityManager->flush();

            return new JsonResponse([
                'status' => 'success',
            ]);
        
        
    }



}
