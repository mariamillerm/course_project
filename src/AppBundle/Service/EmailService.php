<?php

namespace AppBundle\Service;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\Post;
use AppBundle\Entity\ResetToken;
use AppBundle\Entity\User;
use Symfony\Bundle\TwigBundle\TwigEngine;

class EmailService
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
     * EmailService constructor.
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
     * @param ConfirmationToken $token
     */
    public function sendActivationEmail(User $user, ConfirmationToken $token)
    {
        $message = (new \Swift_Message('NewsPortal: Account Confirmation'))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'emails/confirmation.html.twig', [
                        'name' => $user->getUsername(),
                        'hash' => $token->getHash(),
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param User $user
     * @param ResetToken $token
     */
    public function sendRecoveryEmail(User $user, ResetToken $token)
    {
        $message = (new \Swift_Message('NewsPortal: Reset Password'))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'emails/reset_password.html.twig', [
                        'name' => $user->getUsername(),
                        'hash' => $token->getHash(),
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param User   $user
     * @param Post[] $posts
     */
    public function sendNewsEmail(User $user, array $posts)
    {
        $message = (new \Swift_Message('Palmary News'))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'emails/news.html.twig', [
                        'posts' => $posts,
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}