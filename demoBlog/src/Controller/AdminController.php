<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
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
    public function adminFormArticle(Request $request, EntityManagerInterface $manager, Article $article = null): Response
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
            'formArticle' => $formArticle->createView(),
            'editMode' => $article->getId() // Active editMode en fonction de l'id
        ]);
    }

    /**
     * @Route("admin/{id}/delete-article", name="admin_delete_article")
     */
    public function deleteArticle(Article $article, EntityManagerInterface $manager)
    {
        // Nous avons définit une route paramétrée (id) afin de pouvoir supprimé cet article dans la BDD
        // Nous avons injecté en dépendance l'entité article afin que Symfony selectionne automatiquement en BDD l'article à supprimer
        // remove() : méthode de l'interface ENtityManagerInterface qui permet de préparé et garder en mémoire la requete DELETE de suppression
        $manager->remove($article);
        $manager->flush(); // execute la requette de suppression en BDD

        // On affiche un message de validation de suppression
        $this->addFlash('success', "L'article a bien été supprimé");

        // On redirige vers l'afficahge des articles dans le Back office après la suppression
        return $this->redirectToRoute('admin_articles');
    }

    /**
     * @Route("admin/category", name="admin_category")
     */
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repo): Response
    {

        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

        dump($colonnes);

        $categories = $repo->findAll(); // SELECT * FROM category + FETCH ALL

        dump($categories);

        return $this->render('admin/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'categorie' => $categories // toute les categorie récupérer en BDD grace a categorie repository sont envoyer dans ce tableau array
        ]);
    }

    /**
     * @Route("/admin/category/new", name="admin_new_category")
     * @Route("/admin/{id}/edit-category", name="admin_edit_category")
     */
    public function AdminFormCategory(Request $request, EntityManagerInterface $manager, Category $category = null): Response 
    // Request permet récupérer donner poster 
    // EntityManagerInterface $manager permet d'envoyer sur BDD 
    //  Category $category = null : Permet d'avoir les valeurs dans les champs mais il faut mettre nul pour que new Category supprime null et pas les données (nul est une valeur par défault)
    {
        // L'entité Category représente un model de la table SQL Category, donc pour pouvoir insérer dans la table, nous devons renseigner les setter de l'objet avec les données du formulaire

        if(!$category)
        {
            $category = new Category; // On met dans la condition if afin que new category n'écrase pas les données précédente directement mais la valeur nul
        }

        dump($category);

        $formCategory = $this->createForm(CategoryType::class, $category); // on applique la méthode createForm qui va généré le formulaire CategoryType(créer avec le terminale) pour se faire remplir par $category
        // On crée le formulaire d'ajout/modif des catégories et on le relit le formulaire à l'entité $category

        dump($request);

        $formCategory->handleRequest($request); // On récupère les données du formulaire afin de les envoyer dans les setteurs de l'entité $category

        if($formCategory->isSubmitted() && $formCategory->isValid()) // Si les données ont été bien soumise et valide
        {
            if(!$category->getId())
            {
                $message = "La catégorie a bien été enregistrer";
            }
            else
            {
                $message = "La catégorie a bien été modifiée";
            }

            $manager->persist($category); // Prepare et garde en mémoire l'insertion
            $manager->flush(); // Execute la fonction

            // message de validation
            $this->addFlash('success', 'La categorie a bien été ajouté');

            return $this->redirectToRoute('admin_category');
        }

        return $this->render('admin/admin_create_category.html.twig', [
            'formCategory' => $formCategory->createView(),
            'editMode' => $category->getId() // Active editMode en fonction de l'id
        ]);
    }

    /**
     * @Route("/admin/{id}/delete-category", name="admin_delete_category")
     */
    public function adminDeleteCategory(Category $category, EntityManagerInterface $manager) // Pas de response car la function ne retourne pas de template
    // Category $category : a besoin pour savoir quoi supprimer
    // EntityManagerInterface $manager : envoyer la donner supprimer pour le faire ne BDD
    {
        // Si le getter getArticle() est vide, cela veut dire qu'il n'y a plus d'articles associés à la categorie, nous pouvons donc la supprimer
        if($category->getArticles()->isEmpty()) // Si l'article est vide 
        {
            $manager->remove($category);
            $manager->flush();

            $this->addFlash('success', "La catégorie a bien été supprimé");
        }
        else // Sinon dans tout les autres cas, cela veut dire que des articles sont encore associés à la catégorie, nous ne pouvons donc pas la supprimer
        {
            $this->addFlash('danger', "Il n'est pas possible de supprimer la catégorie car des articles y sont toujours associés");
        }

        return $this->redirectToRoute('admin_category');
    }


}
