<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /*
            Les fixtures permettent de créer des données fictives, des fausses données en BDD

            Nous créons ici une boucle afin de créer 10 articles en BDD
            Pour pouvoir insérer des articles en BDD, nous devons passer par l'entité Articles qui reflète la table SQL
        */

        for($i = 1; $i <= 10; $i++)
        {
            // Pour chaque tour de boucle, on créer un objet Article vide
            $article = new Article;

            // On renseigne tout les setters de l'entité Article
            $article->setTitle("Titre de l'article n°$i")
                    ->setContent("<p>Contenu de l'article n°$i</p>")
                    ->setImage("https://picsum.photos/200/300")
                    ->setCreatedAt(new \DateTime());

            // ObjectManager permet de manipuler les lignes dans la BDD (INSERT,UPDATE,DELETE)
            // persist() : permet de préaprer les requetes d'insertions
            $manager->persist($article); // prepare la requette d'insertion
        }

        // flush() : permet de libérer l'insertion en BDD
        $manager->flush(); // Execute l'insertion en BDD
    }
}
