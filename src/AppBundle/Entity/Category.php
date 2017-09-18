<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @ORM\Table(name="categories")
 * @ORM\Entity()
 */
class Category
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
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $name;

    /**
     * @var Category
     *
     * One Category has Many Categories.
     * @OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * @var Category
     *
     * Many Categories have One Category.
     * @ManyToOne(targetEntity="Category", inversedBy="children")
     */
    private $parent;

    /**
     * @var Article
     *
     * One Category has Many Articles.
     * @OneToMany(targetEntity="Article", mappedBy="category")
     */
    private $articles;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->children = new ArrayCollection();
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
    public function getName(): string
    {
        return $this->name;
    }

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
     * @return Category[]
     */
    public function getChildren(): array
    {
        return [$this->children];
    }

    /**
     * @param Category $children
     *
     * @return Category
     */
    public function setChildren(Category $children): Category
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return Category
     */
    public function getParent(): Category
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
     * @return Article[]
     */
    public function getArticles(): array
    {
        return [$this->articles];
    }

    /**
     * @param Article $articles
     *
     * @return Category
     */
    public function setArticles(Article $articles)
    {
        $this->articles = $articles;

        return $this;
    }


}