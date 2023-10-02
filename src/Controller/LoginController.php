<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\JwtManager;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityManagerInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, \App\Repository\UserRepository $repo, JwtManager $jwtManager): Response
    {
        $user = new User();
        $form = $this->createForm(LoginFormType::class, $user);
        $form->handleRequest($request);

        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            // I check the username
            $userInDatabase = $repo->findOneByUsername($user->getUsername());

            if (!$userInDatabase) {
                $error = "No user found.";
            }

            if (!$error) {
                // I check the password
                $isValid = $userPasswordHasher->isPasswordValid(
                    $userInDatabase,
                    $form->get('plainPassword')->getData()
                );

                if (!$isValid) {
                    $error = "Wrong credential.";
                }
            }

            // Redirect with cookie
            if (!$error) {
                // Construct the JWT
                $jwt = $jwtManager->createJwt([
                    'username' => $user->getUsername(),
                    'id' => $userInDatabase->getId(),
                ]);

                $response = $this->redirectToRoute('app_profile');
                $response->headers->setCookie(Cookie::create('JWT', $jwt));
                return $response;
            }
        }

        return $this->render('login/login.html.twig', [
            'last_username' => $user->getUsername(),
            'error'         => $error,
            'form'          => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
        $response = $this->redirectToRoute('app_home');
        $response->headers->clearCookie('JWT');
        return $response;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, JwtManager $jwtManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Log the user
            $jwt = $jwtManager->createJwt([
                'username' => $user->getUsername(),
                'id' => $user->getId(),
            ]);

            $response = $this->redirectToRoute('app_profile');
            $response->headers->setCookie(Cookie::create('JWT', $jwt));
            return $response;
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
