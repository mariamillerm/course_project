<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Symfony\Bundle\TwigBundle\TwigEngine;

class EmailSupport
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TwigEngine
     */
    private $templating;
    /**
     * @var string
     */
    private $from;

    /**
     * EmailSupport constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param TwigEngine $templating
     * @param string $from
     */
    public function __construct(\Swift_Mailer $mailer, TwigEngine $templating, string $from)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->from = $from;
    }

    /**
     * @param User $user
     */
    public function sendActivationEmail(User $user)
    {
        $message = (new \Swift_Message('NewsPortal Registration'))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'emails/confirmation.html.twig', [
                        'name' => $user->getUsername(),
                        'token' => $user->getConfirmationCode(),
                    ]
                ),
                'text/html'
            )
        ;

        $this->mailer->send($message);
    }
}