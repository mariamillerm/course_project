<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserService constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User $user
     */
    public function encodePassword(User $user)
    {
        $password = $this
            ->passwordEncoder
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            );
        $user->setPassword($password);
    }
//
//    /**
//     * @param User $user
//     * @param ResetToken $token
//     */
//    public function resetUserPassword(User $user, ResetToken $token)
//    {
//        $password = $this
//            ->container
//            ->get('security.password_encoder')
//            ->encodePassword(
//                $user,
//                $user->getPassword()
//            );
//
//        $user->setPassword($password);
//        $this
//            ->container
//            ->get('app.token_service')
//            ->removeResetToken($token);
//    }
//
//    /**
//     * @param User $user
//     */
//    public function createUser(User $user)
//    {
//        $this->prepareUser($user);
//
//        $token = $this
//            ->container
//            ->get('app.token_service')
//            ->createConfirmationToken($user);
//
//        $this->insertDataInDB($user, $token);
//
//        $this
//            ->container
//            ->get('app.email_support')
//            ->sendActivationEmail($user, $token);
//    }
}