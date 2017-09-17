<?php

namespace AppBundle\Service;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\ResetToken;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;

class UserService
{
    /**
     * @var Container
     */
    private $container;

    /**
     * UserService constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param User $user
     */
    private function prepareUser(User $user)
    {
        $password = $this
            ->container
            ->get('security.password_encoder')
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            );
        $user->setPassword($password);
        $user->setRole('ROLE_USER');
    }

    /**
     * @return array
     */
    public function getAllUsers()
    {
        $em = $this->container->get('doctrine')->getManager();
        $users = $em
            ->getRepository(User::class)
            ->findAll();

        return $users;
    }

    /**
     * @param User $user
     *
     * @return User|object
     */
    public function findUserByEmail(User $user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $user->getEmail(),
            ]);

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User|object
     */
    public function findUser(User $user)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $em
            ->getRepository(User::class)
            ->find($user);

        return $user;
    }

    /**
     * @param User $user
     * @param string $role
     */
    public function changeUserRole(User $user, string $role)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user->setRole($role);
        $em->flush();
    }

    /**
     * @param User $user
     * @param ConfirmationToken $token
     */
    private function insertDataInDB(User $user, ConfirmationToken $token)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($user);
        $em->persist($token);
        $em->flush();
    }

    /**
     * @param User $user
     * @param ResetToken $token
     */
    public function resetUserPassword(User $user, ResetToken $token)
    {
        $password = $this
            ->container
            ->get('security.password_encoder')
            ->encodePassword(
                $user,
                $user->getPassword()
            );

        $user->setPassword($password);
        $this
            ->container
            ->get('app.token_service')
            ->removeResetToken($token);
    }

    /**
     * @param ConfirmationToken $token
     */
    public function activateUser(ConfirmationToken $token)
    {
        $user = $token->getUser();
        $user->setIsActive(true);

        $this
            ->container
            ->get('app.token_service')
            ->removeConfirmationToken($token);
    }

    /**
     * @param User $user
     */
    public function createUser(User $user)
    {
        $this->prepareUser($user);

        $token = $this
            ->container
            ->get('app.token_service')
            ->createConfirmationToken($user);

        $this->insertDataInDB($user, $token);

        $this
            ->container
            ->get('app.email_support')
            ->sendActivationEmail($user, $token);
    }
}