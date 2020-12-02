<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin/articles", name="admin_articles")
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repo): Response
    {

        // Exercice : Afficher la liste des articles stockés en BDD sous forme de tabbleau HTML avec comme entête le nom des champs de ma table DSL en article

        // Via manager qui permet de manipuler la BDD (insert, update, delete etc), on execute la methode getClassMetadata() afin de selectionner les méta données(primary key, not null, noms des champs etc..) d'une entité (donc d'une table SQL), afin de selectionner le nom des champ/colonnes de la table grace à la methode getFieldNames()
        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();

        // dump($colonnes);

        // On selectionne l'ensemble des articles de la table SQL 'article' dans la BDD en passant par la classe ArticleRepoistory qui permet de selectionner dans la table SQL 'article' et la méthode 'findAll()' qui permet de selectionner l'ensemble de la table (SELECT * FROM article + FETCHALL)
        $articles = $repo->findAll();

        dump($articles);

        return $this->render('admin/admin_articles.html.twig', [
            'colonnes' => $colonnes,
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/article/new", name="admin_new_article")
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     */
    public function adminForm(Request $request, EntityManagerInterface $manager, Article $article = null): Response
    {

        /*
            1. Importer le formulaire de création des articles (form/ArticleType)
            2. Transmettre le formulaire à la méthode render et l'afficher dans le template admin_create.html.twig 
            3. Faite en sorte de récupérer les données du formulaire et les afficher dans un dump()
            4. Réaliser le traitement PHP permettant d'insérer un nouvel article à la validation du formulaire
            5. Executer une redirection après insertion vers l'afficahge des articles dans le backOffice
            6. Afficher un message de validation
        */

        if(!$article)
        {
            $article = new Article;
            
        }

        $formArticle = $this->createForm(ArticleType::class, $article);

        dump($request);

        $formArticle->handleRequest($request);

        if($formArticle->isSubmitted() && $formArticle->isValid())
        {
            if(!$article->getId())
            {
                $article->setCreatedAt(new \DateTime());
            }
            else
            {
                $this->addflash('success', "L'article a bien été enregistré");
            }
            
            $manager->persist($article);
            $manager->flush(); 

            
            return $this->redirectToRoute('admin_articles');
            
        }



        return $this->render('admin/admin_create.html.twig', [
            'formArticle' => $formArticle->createView()
        ]);

        

    }




}
