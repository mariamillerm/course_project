<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;

class EmailSupport
{
    private $mailer;

    /**
     * EmailSupport constructor.
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendActivationEmail(User $user)
    {
        $message = (new \Swift_Message('NewsPortal Registration'))
            ->setFrom('maria.melnik.a@google.com')
            ->setTo($user->getEmail())
            ->setBody('Registration success!')
        ;
//        var_dump($message);
//        die();

        $this->mailer->send($message);
    }
}