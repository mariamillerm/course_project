<?php

namespace AppBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;

class TokenService
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return string
     */
    public function generateToken(): string
    {
        return md5(openssl_random_pseudo_bytes(32));
    }
}