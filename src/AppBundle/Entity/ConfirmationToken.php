<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(
 *     name="confirmation_tokens",
 *     uniqueConstraints={
 *      @UniqueConstraint(name="search_idx", columns={"hash"})
 * })
 * @ORM\Entity()
 */
class ConfirmationToken
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
    private $hash;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    /**
     * ConfirmationToken constructor.
     *
     * @param User $user
     * @param string $hash
     */
    public function __construct(User $user, string $hash)
    {
        $this->hash = $hash;
        $this->user = $user;
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
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
