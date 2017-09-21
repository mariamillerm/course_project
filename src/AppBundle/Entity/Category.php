<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @ORM\Table(name="categories")
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
     * One Category has Many Categories.
     *
     * @var Collection
     *
     * @OneToMany(targetEntity="AppBundle\Entity\Category", mappedBy="parent")
     */
    private $children;

    /**
     * Many Categories have One Category.
     *
     * @var Category
     *
     * @ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="children")
     */
    private $parent;

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
     * @return Category
     */
    public function getParent(): ?Category
    {
        return $this->parent;
    }

    /**
     * @param Category $parent
     *
     * @return Category
     */
    public function setParent($parent): Category
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        return $this->posts->toArray();
    }

    /**
     * @return Category[]
     */
    public function getChildren(): array
    {
        return $this->children->toArray();
    }

        /**
     * @param Category $child
     *
     * @return string
     */
     public function __toString(){
        
        return $this->name;
    }
}


