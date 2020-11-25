<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    // Chaque méthode du controller es tassocié à une route bien spécifique
    // Lorsque nous envoyons la route '/blog' dans l'URL du navigateur, cela execute automatiquement dans le controller, la méthode associé à celle-ci
    // Chaque méthode renvoi un template sur le navigateur en fonction de la route transmise

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        // On appel la class Repository de la class Article
        // Une classe Repository permet uniquement de selectionner des données en BDD
        // $repo = $this->getDoctrine()->getRepository(Article::class);
        // dump($repo);

        // findAll() est une méthode issue de la classe ArticleRepository et permet de selectionner l'ensemble d'une table SQL (SELECT * FROM)
        $articles = $repo->findAll();
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles // Nous envoyons sur le template les articles selectionnés en BDD
        ]);
    }

    /**
     *  @Route("/",  name = "home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bem-vindos no Blog Symfony',
            'age' => 29
        ]);
    }
    /**
     * @Route("/blog/new", name="blog_create")
     */

    public function create(Request $request, EntityManagerInterface $manager)
    {
        // La classe Request contient tout les données véhiculées par les superglobales ($_post,$_get,$_files etc)

        dump($request); // on observe les données saisi dans le formulaire dans la propriété 'request'

        // Si les données ont bien été saisie dans le formulaire
        if($request->request->count() > 0)
        {
            // Pour pouvoir insérer un article dans la BDD, nous devons passer par l'entité Article et remplir tout les setteur de l'objet
            $article = new Article;
            $article->setTitle($request->request->get('title'))
                    ->setContent($request->request->get('content'))
                    ->setImage($request->request->get('image'))
                    ->setCreatedAt(new \DateTime());

            $manager->persist($article); // On prépare la requete d'insertion
            $manager->flush(); // On execute la requete d'insertion

            // redirectToRoute permet de rediriger 
            //
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }
        
        return $this->render('blog/create.html.twig');
    }

    /*
        Nous utilisons le concept de route paramétrées pour faire en sorte de récupérer le bon ID du bon article
        Nous avons définit le paramètre de type {id} directement dans la route
    */

    /**
     * @Route ("/blog/{id}", name="blog_show")
     */
    public function show(Article $article): Response // 9
    {
        // On appel le repository de la classe Article afin de selectionner dans la table Article
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // LA méthode find() issue de la classe ArticleRepository permet de selectionner un article en BDD en fonction de son ID
        // $article = $repo->find($id); // 9
        dump($article);
        
        return $this->render('blog/show.html.twig', [
            'article' => $article // on envoie sur le template l'article selectionnée dans la BDD
        ]);
        
    }


    /*
        Symfony comprend qu'il y a un article a passé et que dans la route il y a un ID, il va donc chercher le bon article avec le bon identifiant.
        Tout ça grace au ParamConverter de Symfony, en gros il voit que l'on a besoin d'un article et aussi d'un ID, il va donc chercher l'article avec l'identifiant et l'envoyer à la fonction show()
        Nous avons donc des fonctions beaucoup plus courte !!
    */

    
}