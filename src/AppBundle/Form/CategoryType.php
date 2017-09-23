<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
	public function buildform(FormBuilderInterface $builder, array $options)
	{
		$builder
		    ->add('name', TextType::class, [
		        'label' => 'category.name',
            ])
            ->add('parent', EntityType::class, [
                'class' => 'AppBundle\Entity\Category',
                'choice_label' => 'name',
                'label' => 'category.parent',
            ])
            ->add('save', SubmitType::class, [
                'attr'      => ['class' => 'button-link save'],
                'label'     => 'category.save',
            ])
            ->add('delete', SubmitType::class, [
                'attr'      => ['class' => 'button-link delete'],
                'label'     => 'category.delete',
            ]);
	}

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'app_bundle_category_type';
    }
}
