<?php

namespace AppBundle\Service;

use AppBundle\Entity\ResetToken;

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
}