<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryFormType;
use App\Controller\BlogController;
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

   


    #[Route('/admin/comments', name: 'app_admin_comments')]
    public function adminComments(EntityManagerInterface $manager, CommentRepository $repoComment)
    {

        $colonnes = $manager->getclassMetadata(Comment::class)->getFieldNames();

        $cellules = $repoComment->findAll();

        //dd($cellules);

        return $this->render('back_office/admin_comments.html.twig', [
            'colonnes' => $colonnes,
            'cellules' => $cellules
        ]);
    }

    #[Route('/admin/user', name: 'app_admin_user')]
    public function adminUsers(EntityManagerInterface $manager, UserRepository $repoUser)
    {

        $titre = $manager->getclassMetadata(User::class)->getFieldNames();

        $values = $repoUser->findAll();

        //dd($values);

        return $this->render('back_office/admin_user.html.twig', [
            'titre' => $titre,
            'values' => $values
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
            // avant de supprimer l'article dans la BDD, on stock son ID afin de l'intégrer dans le message de validation de suppression (addFlash)
            $id = $catRemove->getId();

            $manager->remove($catRemove);
            $manager->flush();

            $this->addFlash('success', "L'article a bien été supprimer avec succès");

            return $this->redirectToRoute('app_admin_category');
        }

        return $this->render('back_office/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'cellules' => $cellules
        ]);
        
    }

    #[Route('/admin/category/add', name: 'app_admin_category_add')]
    public function adminCategoryAdd()
    {
        return $this->render('back_office/admin_category_add.html.twig');
    }

    #[Route('/admin/category/add', name: 'app_admin_category_add')]
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

            $category->getId();          

            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', "La categories a été ajouté avec succès !");

            return $this->redirectToRoute('app_admin_category');         
        
        }

        

        return $this->render('back_office/admin_category_form.html.twig', [
            'formAdminCat' => $formAdminCat->createView(),
            'editmode' => $category->getId()

        ]);
    }
}