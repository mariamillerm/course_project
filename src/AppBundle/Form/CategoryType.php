<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
		    ->add('name', TextType::class)
            ->add('save', SubmitType::class, [
             'attr'      => ['class' => 'button-link save'],
             'label'     => 'Save'
            ])
            ->add('delete', SubmitType::class, [
            'attr'      => ['class' => 'button-link delete'],
            'label'     => 'Delete'
             ]);

	}

}