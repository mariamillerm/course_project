<?php

namespace AppBundle\Form;

use AppBundle\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'post.title',
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'post.summary',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'post.content',
            ])
            ->add('image', FileType::class, [
                'label' => 'post.image',
                'data_class' => null,
            ])
//            ->add('similarPosts', CollectionType::class, [
//                'entry_type'   => EntityType::class,
//                'entry_options'  => [
//                    'choices' => 'AppBundle\Entity\Post'
//                ],
//                'label' => 'Choose similar posts',
//                'allow_add' => true,
//                'allow_delete' => true,
//            ])
            ->add('category', EntityType::class, [
                'class' => 'AppBundle\Entity\Category',
                'choice_label' => 'name',
            ])
            ->add('creationDate', DateTimeType::class, [
                'label' => 'post.creation_date',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'app_bundle_post_type';
    }
}
