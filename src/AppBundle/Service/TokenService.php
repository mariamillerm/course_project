<?php

namespace AppBundle\Service;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\ResetToken;
use AppBundle\Entity\User;

class TokenService
{
    /**
     * @param ResetToken $token
     *
     * @return bool
     */
    public function isResetTokenActive(ResetToken $token)
    {
        $date = $token->getTokenCreateTime();
        $now = new \DateTime();
        $interval = $now->diff($date);

        return $interval->h < 24;
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