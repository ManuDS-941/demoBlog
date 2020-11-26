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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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

        // findAll() est une méthode issue de la classe ArticleRepository et permet de selectionner l'ensemble d'une table SQL (SELECT * FROM article)
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
     * @Route("/blog/{id}/edit", name="blog_edit")
     */

    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        //Nous avon définit 2 routes différentes, une pour l'insertion et une pour la modification
        // Lorsque l'on envoie la route '/blog/new' dans l'URL, on définit un article $article NULL, sinon symfony tente de récupéerer une article en BDD et nous avons une erreur
        // Lorsque l'on envoie la route '/blog/{id}/edit', Symfony selectionne en BDD l'article en fonction de l'id transmit dans l'URL et écrase NULL par l'article recupéré en BDD dans l'objet $article


        // La classe Request contient tout les données véhiculées par les superglobales ($_post,$_get,$_files etc)

        // dump($request); // on observe les données saisi dans le formulaire dans la propriété 'request'

        // Si les données ont bien été saisie dans le formulaire
        // if($request->request->count() > 0)
        // {
        //     // Pour pouvoir insérer un article dans la BDD, nous devons passer par l'entité Article et remplir tout les setteur de l'objet
        //     $article = new Article;
        //     $article->setTitle($request->request->get('title'))
        //             ->setContent($request->request->get('content'))
        //             ->setImage($request->request->get('image'))
        //             ->setCreatedAt(new \DateTime());

        //     $manager->persist($article); // On prépare la requete d'insertion
        //     $manager->flush(); // On execute la requete d'insertion

        //     // redirectToRoute permet de rediriger 
        //     //
        //     return $this->redirectToRoute('blog_show', [
        //         'id' => $article->getId()
        //     ]);
        // }
        
        //On entre dans la condition IF seulement dans le cas de la création d'un nouvel article, c'est a dire pour la route '/blog/new', $article est NULL, on créer un nouvel objet Article
        // Dans le cas d'une modification, $article n'est pas NULL, il contient l'article selectionné en BDD à modifier, on entre pas dans la condition IF

        if(!$article)
        {
            $article = new Article;
        }

        //On observe quand remplissant l'objet Article via les setterus, les getteurs renvoient les données de l'article directement dans les champs du formulaire
        // $article->setTitle("Titre à la con")
        //         ->setContent("Contenu à la con");

        // createFormBuilder() : méthode issue de la classe BlogController permettant de créer un formulaire HTML qui sera lié à notre objet Article, c'est à dire que les champs du formulaire vont remplir l'objet Article

        // $form = $this->createFormBuilder($article)
        //             ->add('title') // permet de créer des champ du formulaire

        //             ->add('content')

        //             ->add('image')

        //             ->getForm(); // permet de valider le formulaire

        // On importe la classe ArticleType qui permet de générer le formulaire d'ajout / modification des articles
        // On précise que le formulaire a pour but de remplir les setteurs de l'objet $article
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request); // handleRequest permet de verifier si tout les champs ont bien été remplit et la méthode va bindé l'objet Article, c'est à dire que si un titre de l'article a été saisi, il sera envoyé directement dans le bon setterde l'objet Article

        dump($request); // On observe les données saisi dans le formulaire dans la proprété 'request'

        // Si le formulaire a bien été soumit et que tout les données sont valides, alors on entre dans la condition IF
        if($form->isSubmitted() && $form->isValid())
        {
            // Si l'article n'a pas d'ID, cela veut dire que nous sommes dans le cas d'un insertion, alors on entre dans la condition IF
            if(!$article->getId())
            {
                $article->setCreatedAt((new \DateTime())); // On remplit la setter de la date puisque nous n'avons pas de champ date dans le formulaire
            }
            $manager->persist($article); // on prépare l'insertion
            $manager->flush(); // on execute l'insertion en BDD

            // Une fois l'insertion exécutée, on redirige ver s le détail de l'article qui vient d'être inséré
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId() // on transmet dans la route l'id de l'article qui vient d'être inséré grace au getter de l'objet Article
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() // Si l'id d'article est différent de NULL, alors 'editMode' renvoi TRUE et que c'est une modification
        ]);
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