<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(
 *     name="categories",
 *     uniqueConstraints={
 *      @UniqueConstraint(name="search_idx", columns={"name"})
 * })
 * @ORM\Entity()
 */
class Category
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
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $name;

    /**
     * One Category has Many Posts.
     *
     * @var Post
     *
     * @OneToMany(targetEntity="AppBundle\Entity\Post", mappedBy="category")
     */
    private $posts;

    /**
     * @param string $name
     *
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        return $this->posts->toArray();
    }

    /**
     * @return string
     */
     public function __toString(){

        return $this->name;
    }
}


