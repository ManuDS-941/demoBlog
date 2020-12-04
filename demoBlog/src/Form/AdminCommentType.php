<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author')
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'title'
                ]) // On précise que ce champs vient de l'entity article // Article correspond à la clé étrangère présente dans la table SQL 'comment'
                // Nous devons définir de quelle entité elle provient
            ->add('content')
            ->add('createdAt')
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
