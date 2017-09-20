<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends AbstractType{

	/**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
	public function buildform(FormBuilderInterface $builder, array $options)
	{

		$builder
		    ->add('category', TextType::class)
            ->add('parent', EntityType::class, array()
                  'class' => 'AppBundle:Category',
                  'choice_label' => function ($category) {
                  
                  return $category->getParent());

            ->add('save', SubmitType::class, array(
             'attr'      => array('class' => 'button-link save'),
             'label'     => 'Save'
            ))
            ->add('delete', SubmitType::class, array(
            'attr'      => array('class' => 'button-link delete'),
            'label'     => 'Delete'
             ));

	}

}