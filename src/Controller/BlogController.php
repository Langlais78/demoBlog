<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{

    #[Route('/', name:'home')]
    public function home(): Response
    {
        // render() : méthode de rendu, en fonction de la route dans l'URL, la méthode render() envoi un template, un rendu sur le navigateur
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 20
        ]);
    }

    #[Route('/blog', name: 'blog')]
    public function blog(ArticleRepository $repoArticle): Response
    {
        /*

        Injection de dependances :  c'est un des fondement de symfony, ici notre methode DEPEND de la classe ArticleRepository pour fonctionner correctement 
        Ici Symfony comprend que la methode blog() attend en argument un objet issu de la classe ArticleRepository, automatiquement Symfony envoi une instance de cette classe en argument de cette classe
        $repoArticle est un objet issu de la classe ArticleRepository, nous n'avons plus qu'a piocher dasns l'objet pour atteindre des methode de la classe 

        Symfony est une application qui est capable de réponde à un navigateur lorsque celui-ci appel une adresse (ex: localhost:8800/blog), le controller doit être capable d'envoyer un rendu, un template sur le navigateur

        Icin lorsque l'on transmet la route '/blog' dans l'URL, cela execute la méthode index() dans le controller qui renvoi le template '/blog/index.html.twig' sur le navigateur
        */

        // pour selectionner des données en bdd, nous devons passer par un eclasse repository, ces classe permet uniquement d'executer des requete de selection en BDD. Elles contiennent des methodes mis a disposition par symfony(findAll(), findBy() etc...)

        // Ici nous devons importer au sein de notre controller la classe Articles repository pour pouvoir selectionner dans la table article
        // $repoArticle est un objet issu de la classe ArticleRepository
        // getRepository () est une methode issu de l'objet doctrine permettant ici d'importer la class articleRepository

        // $repoArticle = $doctrine->getRepository(Article::class);

        // dump() / dd() : outil de debug de symfony
        //dd($repoArticle);

        // FIndAll () ; methode issue de la classe ArticleREpository permettant de selectionner l'enssemble de la table SQL et de recuperr un tableau multi contenant l'enssemble des articles stockés en BDD
        $articles = $repoArticle->findAll(); // SELECT * FROM article + FETCH_ALL
        //dd($articles);

        return $this->render('blog/blog.html.twig', [
            'articles' => $articles // On transmet au template les articles selectionnés en BDD afin que twig traite l' affichage
        ]);
    }

    #[Route('/blog/new', name: 'blog_create')]
    #[Route('/blog/{id}/edit', name: 'blog_edit')]
    public function blogCreate(Article $article = null, Request $request, EntityManagerInterface $manager): Response
    {   
        // la classe Request de symfony contient toute les données vehiculées par les superglobales ($_GET, $_POST, etc...)
        //$request->request : la propriété 'request  de l'objet $request contient toutes les données de $_POST   

        // Si les données dans le tableau ARRAY $_post sont superieur a 0, alors on entre dans la condition IF
        // if($request->request->count() > 0)
        // {
        //     //dd($request->request);

        //     // Pour inserer dans la table SQL 'article', nous avons besoin d'un objet de son entité correspondante            
        //     $article = new Article;

        //     $article->setTitre($request->request->get('titre'))
        //             ->setContenu($request->request->get('contenu'))
        //             ->setPhoto($request->request->get('photo'))
        //             ->setDate(new \DateTime());
        //     //dd($article);

        //     //persist() : methode issue de l'interface EntityManagerInterface permettant de preparer la requete d'insertion et de garder en memoire l'objet/ la requete
        //     $manager->persist($article);

        //     // flush() : methode issue de l'interface EntityManagerInterface permettant veritablement d'executer la requete INSERT en BDD(ORM doctrine)
            //$manager->flush();
        //}

        // si la variable article est null, cela veut dire que nous sommes sur la route  '/blog/new', on entre dans le if et on crée un enouvelle instance de l'entité Article 
        // Si la variable $article n'est pas null, cela veut dire que nous sommes sur la route '/blog/{id}/edit', nous n'entrons pas dans le IF car $article contient un article en BDD         
        if(!$article)            
        {
            $article = new Article;
        }
        
        

        // $article->setTitre("Titre a la con")
        // ->setContenu("Contenu a la con");

        $formArticle = $this->createForm(ArticleType::class, $article);
        
        $formArticle->handleRequest($request);

        if($formArticle->isSubmitted() && $formArticle->isValid())
        {

            // Le seul setter que l'on appel de l'entité, c'est celui de la date puisqu'il n'y a pas de champ DATE dans le formulaire
            $article->setDate(new \DateTime());

            //dd($article);

            $manager->persist($article);
            $manager->flush();
        }

        return $this->render('blog/blog_create.html.twig', [
            'formArticle' => $formArticle->createView() // On transmet le formulaire au template afin de pouvoir l'afficher avec twig
            // createView() : retourn eun petit objet qui represente l'affichage du formulaire, on le recupere dans le template blog_create.html.twig 
        ]);
    }

    # Méthode permettant
    # On defeni une route 'parametrée' {id}, ici la route permet d erecevoir l'id d'un article stockée en BDD
    #        /blog/5
    #[Route('/blog/{id}', name: 'blog_show')]
    public function blogShow(Article $article): Response
    {
        /*
            ici, nous envoyons un id dans l'URL et nous imposons en argument un objet issu de l'entité Article donc la table SQL
            Donc Symfony est capable de selectioner en BDD l'article en fonction de l'id passé dans l'URL et de l'envoyer automatiquement en argument de la methode blogShow() dans la variable de reception $article
        */
        //dd($article);
        // On entre la classe ArticleRepository dans la methode BlogShow pour selectionner (SELECT) dans la table SQL 'article'

        //$repoArticle = $doctrine->getRepository(Article::class);
        // dd($repoArticle);

        //  find() : methode issue de la classe ArticleRepository permettant de selectionner un element par son ID qu'on lui fournit en argument
        //$article = $repoArticle->find($id);
        //dd($article);

        // l'id transmit dans la route '/blog/5 est transmit automatiquement en argument de la methode blogShow($id) dans la variable de reception $id       
        //dd($id); // 5
        return $this->render('blog/blog_show.html.twig', [
            'article' => $article // On transmet au template l'article selectionné en BDD afin que twig puisse traiter et afficher les données sur la page
        ]);
    }

    
}
