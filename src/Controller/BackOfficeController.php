<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Form\CategoryFormType;
use App\Controller\BlogController;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BackOfficeController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('back_office/index.html.twig', [
            'controller_name' => 'BackOfficeController',
        ]);
    }

    #[Route('/admin/articles', name: 'app_admin_articles')]
    #[Route('/admin/articles/{id}/remove', name: 'app_admin_articles_remove')]
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repoArticle, Article $artRemove = null, BlogController $artUpdate): Response
    {
        //dd($artRemove);

        // 
        $colonnes = $manager->getclassMetadata(Article::class)->getFieldNames();

        $cellules = $repoArticle->findAll();
        
        //dd($cellules);

        //TRAITEMENT SUPPRESSION ARTICLE EN BDD

        if($artRemove)
        {
            // avant de supprimer l'article dans la BDD, on stock son ID afin de l'intégrer dans le message de validation de suppression (addFlash)
            $id = $artRemove->getId();

            $manager->remove($artRemove);
            $manager->flush();

            $this->addFlash('success', "L'article a bien été supprimer avec succès");

            return $this->redirectToRoute('app_admin_articles');
        }

       

        
        
        /*
            Exo : afficher sous forme de tableau HTML l'ensemble des articles stockés en BDD 
            1. Selectionner en BDD l'ensemble de la table 'article' et transmettre le résultat a la méthode render()
            2. Dans les template 'admin_articles.html.twig', mettre en forme l'affichage des articles dans un tableau HTML
            3. Afficher le nom de la catégorie de chaque article
            4. Afficher le nombre de commentaire de chaque article
            5. Prévoir un lien de modification/Suppression pour chaque article
        */

        return $this->render('back_office/admin_articles.html.twig', [
            'colonnes' => $colonnes, // 
            'cellules' => $cellules
        ]);
    }

   /*
    Exo: Affichage et suppression des commentaires
    1. Création d'une nouvelle route '/admin/commentaires' (name: app_admin_commentaires)
    2. Création d'une nouvelle méthode adminCommentaires()
    3. Création d'un nouveau template 'admin_commentaires.html.twig
    4. Sélectionner les noms/champs colonne de la table 'Comment' et les afficher sur le template
    5. Sélectionner l'ensemble de la table 'Comment' et afficher les données sous forme de tableau
    6. Mettre en place 'dataTable' pour pouvoir filtrer/rechercher des commentaires
    7. Créer une nouvelle route (sur la même méthode) '/admin/comment/{id}/remove' (name: app_admin_comment_remove)
    8. Réaliser le traitement permettant de supprimer un commentaire dans la BDD
   */


    #[Route('/admin/comments', name: 'app_admin_comments')]
    #[Route('/admin/comments/{id}/remove', name: 'app_admin_comments_remove')]
    public function adminComments(EntityManagerInterface $manager, CommentRepository $repoComment, Comment $commentDelete = null)
    {

        $colonnes = $manager->getclassMetadata(Comment::class)->getFieldNames();

        $cellules = $repoComment->findAll();

        if ($commentDelete)
        {
            $id = $commentDelete->getId();

            $manager->remove($commentDelete);
            $manager->flush();

            $this->addFlash('success', "Le commentaire n° $id a bien été supprimer avec succès");

            return $this->redirectToRoute('app_admin_comments');
        }

        //dd($cellules);

        return $this->render('back_office/admin_comments.html.twig', [
            'colonnes' => $colonnes,
            'cellules' => $cellules
        ]);
    }

    #[Route('/admin/comments/{id}/edit', name: 'app_admin_comments_update')]
    public function adminCommentsForm(Comment $comments = null, Request $request, EntityManagerInterface $manager): Response
    {

        $formAdminCom = $this->createForm(CommentType::class, $comments, [
            'commentFormBack' => true 
        ]);

        $formAdminCom->handleRequest($request);

            if($formAdminCom->isSubmitted() && $formAdminCom->isValid())
            {   

                $comments->setDate(new \DateTime());

                $manager->persist($comments);
                $manager->flush();

                $idCom = $comments->getId();

                $this->addFlash('success', "Le commentaire '$idCom' été modifié avec succès !");

                return $this->redirectToRoute('app_admin_comments');         
            
            }        

        return $this->render('back_office/admin_comments_form.html.twig', [
            'formAdminCom' => $formAdminCom->createView(),
            'editmode' => $comments->getId()
            ]);
    }


    #[Route('/admin/articles/add', name: 'app_admin_articles_add')]
    #[Route('/admin/articles/{id}/edit', name: 'app_admin_articles_update')]
    public function adminArticleForm(Article $article = null, Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {
        if($article)
        {
            $photoActuelle = $article->getPhoto();
        }

        if(!$article)
        {
            $article = new Article;
        }
        
        $formAdminArticle = $this->createForm(ArticleType::class, $article);

        $formAdminArticle->handleRequest($request);


        if($formAdminArticle->isSubmitted() && $formAdminArticle->isValid())
        {

            if(!$article->getId())
                $txt = "enregistré";
            else
                $txt = "modifier";

            if($article->getId())           
               $article->setDate(new \DateTime());

            $photo = $formAdminArticle->get('photo')->getData();

            if($photo)
            {
                $nomOriginePhoto = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                
                $nouveauNomFichier = $nomOriginePhoto . "-" . uniqid() . '.' . $photo->guessExtension();                 
                
                try
                {
                    
                    $photo->move(
                        $this->getParameter('photo_directory'),
                        $nouveauNomFichier
                    );
                }
                catch(FileException $e)
                {

                }

                $article->setPhoto($nouveauNomFichier);

            }
            else
            {
                if(isset($photoActuelle))
                    $article->setPhoto($photoActuelle);
                else
                
                    $article->setPhoto(null);
            }

            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success', "L'article a été $txt avec succès !");

            return $this->redirectToRoute('app_admin_articles', [
                'id' => $article->getId()
            ]);           
        
        }

        return $this->render('back_office/admin_articles_form.html.twig', [
            'formAdminArticle' => $formAdminArticle->createView(),
            'editMode' => $article->getId(),
            'photoActuelle' => $article->getPhoto()
        ]);
    }

    /*
        Exo : affichage et suppression catégorie 
        1. Création d'une nouvelle route '/admin/categories' (name: app_admin_categories)
        2. Création d'une nouvelle méthode adminCategories()
        3. Création d'un nouveau template 'admin_categories.html.twig'
        4. Selectionner les noms des champs/colonnes de la table Category, les transmettre au template et les afficher 
        5. Selectionner dans le controller l'ensemble de la table 'category' (findAll) et transmettre au template (render) et les afficher sur le template (Twig), afficher également le nombre d'article liés à chaque catégorie
        6. Prévoir un lien 'modifier' et 'supprimer' pour chaque categorie
        7. Réaliser le traitement permettant de supprimer une catégorie de la BDD
    */

    #[Route('/admin/category', name: 'app_admin_category')]
    #[Route('/admin/category/{id}/remove', name: 'app_admin_category_remove')]
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repoComment, Category $catRemove = null)
    {

        $colonnes = $manager->getclassMetadata(Category::class)->getFieldNames();

        $cellules = $repoComment->findAll();

      

        //dd($cellules);
        if($catRemove)
        {

            $catTitre = $catRemove->getTitre();

            if($catRemove->getArticles()->isEmpty())
            {
                // avant de supprimer l'article dans la BDD, on stock son ID afin de l'intégrer dans le message de validation de suppression (addFlash)
                //$id = $catRemove->getId();

                $manager->remove($catRemove);
                $manager->flush();

                $this->addFlash('success', "La catégorie '$catTitre' a bien été supprimé avec succès.");

                
            }
            else
            {
                $this->addFlash('danger', "Impossible de supprimer la catégory '$catTitre' car il y a encore des articles associés");
            }

            return $this->redirectToRoute('app_admin_category');
        }

        return $this->render('back_office/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'cellules' => $cellules
        ]);
        
    }

    
    #[Route('/admin/category/add', name: 'app_admin_category_add')]
    #[Route('/admin/category/{id}/edit', name: 'app_admin_category_update')]
    public function adminCategoryForm(Category $category = null, Request $request, EntityManagerInterface $manager): Response
    {
 
        if(!$category)
        {
            $category = new Category;
        }
        
        $formAdminCat = $this->createForm(CategoryFormType::class, $category);

        $formAdminCat->handleRequest($request);

            if($formAdminCat->isSubmitted() && $formAdminCat->isValid())
            {         

                $manager->persist($category);
                $manager->flush();

                $titreCat = $category->getTitre();

                $this->addFlash('success', "La catégorie '$titreCat' été ajouté avec succès !");

                return $this->redirectToRoute('app_admin_category');         
            
            }        

        return $this->render('back_office/admin_category_form.html.twig', [
            'formAdminCat' => $formAdminCat->createView(),
            'editmode' => $category->getId()

        ]);
    }

    // #[Route('/admin/user', name: 'app_admin_user')]
    // public function adminUsers(EntityManagerInterface $manager, UserRepository $repoUser)
    // {

    //     $titre = $manager->getclassMetadata(User::class)->getFieldNames();

    //     $values = $repoUser->findAll();

    //     //dd($values);

    //     return $this->render('back_office/admin_user.html.twig', [
    //         'titre' => $titre,
    //         'values' => $values
    //     ]);
    // }
    
    // #[Route('/admin/user/{id}/remove', name: 'app_admin_user_remove')]
    // public function adminUserRemove(User $userRemove , EntityManagerInterface $manager)
    // {

    //     $utilisateur = $userRemove->getId();

    //     if($userRemove)
    //     {

    //     $manager->remove($userRemove);
    //     $manager->flush();

    //     $this->addFlash('success', "L'utilisateur' '$utilisateur' été ajouté avec succès !");

    //     return $this->redirectToRoute('app_admin_user');

    //     }

    //     return $this->render('back_office/admin_user.html.twig');

    //     // return $this->render('back_office/app_admin_user_form.html.twig');
    // }


    // #[Route('/admin/user/{id}/edit', name: 'app_admin_user_update')]
    // public function adminUserUpdate(User $user = null, Request $request, EntityManagerInterface $manager): Response
    // {
    //     $formAdminUser = $this->createForm(RegistrationFormType::class, $user, [
    //         'userBack' => true
    //     ]);

    //     $formAdminUser->handleRequest($request);

    //             if($formAdminUser->isSubmitted() && $formAdminUser->isValid())
    //             {   
    
    //                 $manager->persist($user);
    //                 $manager->flush();
    
    //                 $idUser = $user->getId();
    
    //                 $this->addFlash('success', "L'utilisateur '$idUser' été modifié avec succès !");
    
    //                 return $this->redirectToRoute('app_admin_user');         
                
    //             }            

    //     return $this->render('back_office/admin_user.html.twig', [
    //         'formAdminUser' => $formAdminUser->createView(),
    //         'editmode' => $user->getId(),
    //         'users' => $user
    //         ]);
    // }


    #[Route('/admin/user', name: 'app_admin_user')]
    #[Route('/admin/user/{id}/update', name: 'app_admin_user_update')]
    #[Route('/admin/user/{id}/remove', name: 'app_admin_user_remove')]
    public function adminUser(EntityManagerInterface $manager, UserRepository $repoUser, User $user = null, Request $request): Response
    {
        // dd($user);
        // dd($request->query);

        $titre = $manager->getClassMetaData(User::class)->getFieldNames();

        $users = $repoUser->findAll();

        // Si $user retourne true, cela veut que $user les informations d'1 user stocké en BDD
        if($user)
        {
            // Si l'indice 'op' est définit dans l'URL et qu'il a pour valeur 'update', alors on entre dans la condition et on execute une requete 'update'
            if($request->query->get('op') == 'update')
            {
                // dd('update');
                $formAdminUser = $this->createForm(RegistrationFormType::class, $user, [
                    'userBack' => true
                ]);

                $formAdminUser->handleRequest($request);

                if($formAdminUser->isSubmitted() && $formAdminUser->isValid())
                {
                    $infos = $user->getPrenom() . " " . $user->getNom();

                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash('success', "Le rôle de l'utilisateur $infos a été modifié avec succès.");

                    return $this->redirectToRoute('app_admin_user');
                }
            }
            else // Sinon, aucun paramètres dans l'URL, alors on execute une requete de suppression
            {

                $infos = $user->getPrenom() . " " . $user->getNom();

                $manager->remove($user);
                $manager->flush();

                $this->addFlash('success', "Le rôle de l'utilisateur $infos a été supprimé avec succès.");

                return $this->redirectToRoute('app_admin_user');
            }
        }
       

        return $this->render('back_office/admin_user.html.twig', [
            'titre' => $titre,
            'users' => $users,
            // Si l'indice dans l'URL est 'op=update' alors on execute createView() sur l'objet formUtiUpdate pour générer le formualaire, sinon on stock une chaine de caractère vide
            'formAdminUser' => ($request->query->get('op') == 'update') ? $formAdminUser->createView() : '',
            'user' => $user
        ]);
    } 

     /*
        Exo : Le but est de relier les utilisateurs aux articles, lorsque l'internaute poste un article, il faut une relation entre Article et User
        Créer une nouvelle propriété dans l'entité user 'article' et fait une relation OneToMany, cette propriété peut être null
        Lorsque l'internaute poste un nouvel article, faites en sorte de renseigner la clé étrangère 'user_id' afin que l'article soit relié à l'utilisateur connecté
        Dans la page profil de l'utilisateur, afficher dans une liste tout les articles posté par l'internaute (titre article (lien qui redirige vers l'article), date/heure et un lien pour la modification)
    */
}
