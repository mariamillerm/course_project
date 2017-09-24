<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
        $postTitle = $options['postTitle'];

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
                'required' => false,
            ])
            ->add('similarPosts', EntityType::class, [
                'multiple' => true,
                'class' => 'AppBundle\Entity\Post',
                'query_builder' => function (EntityRepository $er) use ($postTitle) {
                    return $er->createQueryBuilder('p')
                        ->where('p.title != ?1')
                        ->orderBy('p.title', 'ASC')
                        ->setParameter(1, $postTitle);
                },
                'label' => 'post.similarPosts',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('category', EntityType::class, [
                'class' => 'AppBundle\Entity\Category',
                'choice_label' => 'name',
                'label' => 'post.category',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Post',
            'postTitle' => null,
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
