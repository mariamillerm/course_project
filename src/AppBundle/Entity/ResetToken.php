<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="reset_tokens")
 * @ORM\Entity()
 */
class ResetToken
{
    // @TODO UniqueConstraint
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $creationTime;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    /**
     * ResetToken constructor.
     *
     * @param User   $user
     * @param string $hash
     */
    public function __construct(User $user, string $hash)
    {
        $this->creationTime = new \DateTime();
        $this->user = $user;
        $this->hash = $hash;
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

    /**
     * @return \DateTime
     */
    public function getCreationTime(): \DateTime
    {
        return $this->creationTime;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        $now = new \DateTime();
        $interval = $now->diff($this->creationTime);

        return $interval->h >= 24;
    }
}
