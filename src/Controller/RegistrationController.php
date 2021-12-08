<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // fait appel a l'objet $userPasswordHasher de la classe UserPasswordHasherInterface afin d'encoder le mot de passe en BDD
            // En argument on lui fournit l'objet entité dans lequel nous allons encoder un élément ($user) et on lui fournit le mot de passe saisi dans le formulaire a encoder
            $hash = $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );

            //dd($hash);

            // on transmet au setter du password la clé de hachage à enregistrer en bdd
            $user->setPassword($hash);

            $this->addFlash('success', "Félicitation ! Vous êtes maintenant inscrit sur le site !");

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /*
        Exo : créer un epage profil affichant les données de l'utilisateur authentifié
        1. Créer une nouvelle route '/profil'
        2. Créer une nouvellemethode userProfil()
        3. Cette methode renvoi un template 'registration/profil.html.twig'
        4. Afficher dans ce template les information de l'utilisateur connecter
    */


}
