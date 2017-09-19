<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UserEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'user' => 'ROLE_USER',
                    'manager' => 'ROLE_MANAGER',
                    'admin' => 'ROLE_ADMIN',
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function getName()
    {
        return 'app_bundle_user_edit';
    }
}
