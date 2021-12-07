<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticlesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // On importe la librarie Faker pour les fixtures, cela nous permet de créer des faux articles, categorie, commentaire plus évolueés avec par exemple des faux, faux prenoms, dates aléatoire, etc...
        $faker = \Faker\Factory::create('fr_FR');

        // Création de 3 catégories
        for($cat = 1; $cat <= 3; $cat++)
        {
            $category = new Category;

            $category->setTitre($faker->word)
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 4 à 10 articles par catégorie
            // mt_rand() : fonction prédéfinie PHP qui retourne 1 chiffre aléatoire en fonction des arguments transmit, ici un chiffre entre 4 et 10
            for($art = 1; $art <= mt_rand(4,10); $art++)
            {
                $article = new Article;

                // join() : fonction predefinie de PHP (alias : de implode) mais pour les chaine de caractères
                $contenu = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';

                $article->setTitre($faker->sentence())
                        ->setContenu($contenu)
                        ->setPhoto(null)
                        ->setDate($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category);// on relit les article aux categorie déclarées ci-dessus, le setteur attend en arguments l'objet entité $category pour créer la clé etrangèreet non un int               
                    $manager->persist($article);
                

                // Création de 4 à 10 commentaires par article
                for($cmt = 1; $cmt <= mt_rand(4,10); $cmt++)
                {
                    $comment = new Comment;

                    // Traitement des dates
                    $now = new \DateTime();
                    $interval = $now->diff($article->getDate());// retourne un timestamp (un temps en seconde) entre les dates de création des articles et aujourd'hui

                    $days = $interval->days; // retourne le nombre de jour entre la date de création des articles et aujourd'hui

                    $contenu = '<p>' . join('</p><p>', $faker->paragraphs(2)) . '</p>';
                    
                    $comment->setAuteur($faker->name)
                            ->setCommentaire($contenu)
                            ->setDate($faker->dateTimeBetween("-$days days"))
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
    
}