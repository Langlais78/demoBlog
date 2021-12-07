<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticlesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // PHP namespace resolver : extension permettant d'importer les classes (ctrl + alt + i)
        // La boucle tourne 10 fois afin de créer 10 articles FICTIFS dans la bdd
        for($i = 1; $i <= 10; $i++)
    {
        // Pour insérer des données dans la table SQL Article, nous commes obligé de passer par sa classe Entity correspondante 'App\Entity\Articles', cette classe est le reflet de la table SQL, elle contient des propriétés indentiques aux hamps/colonnes de la table SQL
        $article = new Article;

        // On execute tout les setteur de l'objet afin de remplir les objets et d'ajouter un titre, contenu, image etc... pour nos 10 articles
        $article->setTitre("Titre de l'article $i")
                ->setContenu("<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam quo mollitia animi maxime sed enim rem molestias possimus provident exercitationem?</p>")
                ->setPhoto("https://picsum.photos/id/221/300/600")
                ->setDate(new \DateTime);

        // Nous faison appel à l'objet $manager ObjetManager
        // Une classe permet entre autre de manipuler les lignes de la BDD (INSET, UPDATE, DELETE)
        // persist() : méthode issu de la classe objetManager permettant de garder en mémoire les 10 objets $articles et de préparer les requetes SQL
        $manager->persist($article);

        // $r = $bdd->prepare("INSET INTO article VALUES ('$articles->getTitre()')")
    }

        // flush() : méthode issu de la classe ObjetManager (ORM doctrine) permettant véritablement d'executer les requetes SQL en BDD
        $manager->flush();
    }
}

