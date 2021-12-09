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
         // Si getUser() renvoi des données, cela veut dire que l'internaute est authentifié, il n'a rien à faire sur la route '/register', on le redirige vers la route de connexion '/blog'
        if($this->getUser())
        {
            return $this->redirectToRoute ('blog');
        }

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'userRegistration' => true // on precise dans quelle condition on entre, dans la classe registrationFormType pour afficher un formulaire en particulier, la classe contient plusieur formulaire
        ]);

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

        #[Route('/profil', name: 'app_profil')]
        public function userProfil(): Response
        {
             // Si getUser() est null, et ne renvoi aucune données, cela veut dire que l'internaute n'est pas authentifié, il n'a rien à faire sur la route '/profil', on le redirige vers la route de connexion '/login'
            if(!$this->getUser())
            {
                return $this->redirectToRoute('app_login');
            }
           // retourne un objet App/Entity/User contenant le sinformations de l'utilisateur authentifié sur le site, ces données sont stockées dans le fichier session de l'utilisateur
            $user= $this->getUser();

            //dd($user);

            return $this->render('registration/profil.html.twig', [
                'user' => $user
            ]);

        }

        # methode permettant de modifier les informations de l'utilisateur en BDD (sauf mdp)
        #[Route('/profil/{id}/edit', name: 'app_profil_edit')]
            public function userProfilEdit(User $user, Request $request, EntityManagerInterface $manager): Response
            {

                $formUpdate = $this->createForm(RegistrationFormType::class, $user, [
                    'userUpdate' => true
                ]);

                $formUpdate->handleRequest($request);
                

                if($formUpdate->isSubmitted() && $formUpdate->isValid())
                {
                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash('success', 'Vous avez modifié vos informations, merci de vous authentifié de nouveau');

                    // Une fois que l'utilisateur a modifie ses info de profil, on le redirige vers la route de deconnexion , on le deconnecte pour qu'il puisse apres mettre a jour la session en s'authenifiant de nouveau
                    return $this->redirectToRoute('app_logout');
                }

                //dd($user);
                return $this->render('registration/profil_edit.html.twig', [
                    'formUpdate' => $formUpdate->createView()
                ]);
            }

}
