<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    
    #[Route("/login", name:"app_login")]    
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

         // Si getUser() renvoi des données, cela veut dire que l'internaute est authentifié, il n'a rien à faire sur la route '/register', on le redirige vers la route de connexion '/blog'
        if($this->getUser())
        {
            return $this->redirectToRoute ('blog');
        }

        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // renvoi un message d'erreur si jamais nous avons saisie les mauvais indentifiants pour la connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        // Cette methode renvoi dans notre cas, le dernier 'email' saisie par l'internaute dans le formulaire d'authentification
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
