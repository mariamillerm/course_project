<?php

namespace AppBundle\Service;

use Symfony\Component\Form\Form;

class FormErrors
{
    public function getFormErrorMessage(Form $form)
    {
        $errors = $form->getErrors();
        $error = $errors->current();

        $message = null;

        if ($error !== false) {
            $message = $error->getMessage();
        }

        return $message;
    }
}