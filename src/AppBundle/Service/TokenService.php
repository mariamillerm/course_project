<?php

namespace AppBundle\Service;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\ResetToken;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;

class TokenService
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param ResetToken $token
     *
     * @return bool
     */
    public function isResetTokenDisabled(ResetToken $token)
    {
        $date = $token->getTokenCreateTime();
        $now = new \DateTime();
        $interval = $now->diff($date);

        $isDisabled = $interval->h >= 24;

        if ($isDisabled) {
            $this->removeResetToken($token);
        }

        return $isDisabled;
    }

    /**
     * @param User $user
     */
    public function setResetTokenToUser(User $user)
    {
        $this->deleteOldResetToken($user);
        $userToken = $this->createResetToken($user);
        $this->insertResetTokenInDB($userToken);

        $this
            ->container
            ->get('app.email_support')
            ->sendRecoveryEmail($user, $userToken);
    }

    /**
     * @param ResetToken $token
     */
    private function insertResetTokenInDB(ResetToken $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($token);
        $em->flush();
    }

    /**
     * @param User $user
     */
    private function deleteOldResetToken(User $user)
    {
        $token = $this->findResetTokenByUser($user);

        if ($token !== null) {
            $this->removeResetToken($token);
        }
    }

    /**
     * @param ResetToken $token
     */
    public function removeResetToken(ResetToken $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->remove($token);
        $em->flush();
    }

    /**
     * @param int $id
     */
    public function deleteConfirmationToken(int $id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $token = $em
            ->getRepository(ConfirmationToken::class)
            ->findOneBy(
                ['user' => $id]
            );

        if ($token !== null) {
            $this->removeConfirmationToken($token);
        }
    }

    /**
     * @param ConfirmationToken $token
     */
    public function removeConfirmationToken(ConfirmationToken $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->remove($token);
        $em->flush();
    }

    /**
     * @param string $token
     *
     * @return ResetToken|object
     */
    public function findResetToken(string $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $token = $em
            ->getRepository(ResetToken::class)
            ->findOneBy([
                'token' => $token,
            ]);

        return $token;
    }

    /**
     * @param string $token
     *
     * @return ConfirmationToken|object
     */
    public function findConfirmationToken(string $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $token = $em
            ->getRepository(ConfirmationToken::class)
            ->findOneBy([
                'token' => $token,
            ]);

        return $token;
    }

    /**
     * @param User $user
     *
     * @return ResetToken|object
     */
    private function findResetTokenByUser(User $user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $token = $em
            ->getRepository(ResetToken::class)
            ->findOneBy([
                'user' => $user,
            ]);

        return $token;
    }

    /**
     * @param User $user
     *
     * @return ResetToken
     */
    private function createResetToken(User $user)
    {
        $token = new ResetToken();
        $token->setUser($user);
        $token->setToken(md5(openssl_random_pseudo_bytes(32)));

        return $token;
    }

    /**
     * @param User $user
     *
     * @return ConfirmationToken
     */
    public function createConfirmationToken(User $user)
    {
        $userToken = new ConfirmationToken();
        $userToken->setUser($user);
        $userToken->setToken(md5(openssl_random_pseudo_bytes(32)));

        return $userToken;
    }
}