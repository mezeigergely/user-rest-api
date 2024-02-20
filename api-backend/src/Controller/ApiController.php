<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private ManagerRegistry $doctrine;
    private SerializerInterface $serializer;

    public function __construct(
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        SerializerInterface $serializer)
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }
    
    #[Route('/user', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
    
        if ($firstname === null || $lastname === null || $email === null || $password === null) {
            return new Response("Missing data(s)!", Response::HTTP_BAD_REQUEST);
        }
   
        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $user->setEmail($email);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new Response((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $saveUser = $this->userRepository->saveUser($this->doctrine, $user);
        if ($saveUser){
            return new Response("Stored successfully!", Response::HTTP_ACCEPTED);
        }

        return new Response("Error!", Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route('/user/{id}', name: 'user_list', methods: ['GET'])]
    public function list(Request $request,int $id = null): Response
    {

        if ($id) {
            $user = $this->userRepository->find($id);
            $usersArray = $this->getUserDataArray($user);
            if (!$user) {
                return new Response("Invalid ID!", Response::HTTP_NOT_FOUND);
            }
        }else{
            $users = $this->userRepository->findAll();
            foreach($users as $user){
                $usersArray[] = $this->getUserDataArray($user);
            }
        }

        $isYaml = in_array("yaml", $request->getAcceptableContentTypes());

        $response = ($isYaml) ? Yaml::dump($usersArray) : $this->serializer->serialize($usersArray, 'json');
    
        return new Response($response, Response::HTTP_OK, ['Content-Type' => ($isYaml) ? 'text/yaml' : 'application/json']);
    }
    
    private function getUserDataArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
        ];
    }
}