<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="reset_tokens")
 * @ORM\Entity()
 */
class ResetToken
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime")
     */
    private $tokenCreateTime;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    /**
     * ResetToken constructor.
     */
    public function __construct()
    {
        $this->tokenCreateTime = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return ResetToken
     */
    public function setToken(string $token): ResetToken
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ResetToken
     */
    public function setUser(User $user): ResetToken
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTokenCreateTime(): \DateTime
    {
        return $this->tokenCreateTime;
    }

    /**
     * @param \DateTime $tokenCreateTime
     *
     * @return ResetToken
     */
    public function setTokenCreateTime(\DateTime $tokenCreateTime): ResetToken
    {
        $this->tokenCreateTime = $tokenCreateTime;

        return $this;
    }
}