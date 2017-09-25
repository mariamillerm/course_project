<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="posts",
 *     uniqueConstraints={
 *      @UniqueConstraint(name="search_idx", columns={"title"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 */
class Post
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\File(
     *     uploadErrorMessage="error.upload",
     *     maxSize="10M"
     * )
     */
    private $image;

	/**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="post.title.empty")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $content;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $creationDate;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="posts")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;
    
    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Post")
     * @ORM\JoinTable(name="similarPosts")
     * @Assert\Count(min=0, max=5)
     */
    private $similarPosts;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $rating;

    /**
     * Post constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->similarPosts = new ArrayCollection();
        $this->creationDate = new \DateTime();
        $this->author = $user;
        $this->rating = 0;
    }

    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     *
     * @return Post
     */
    public function setImage($image): Post
    {
        $this->image = $image;

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
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Post
     */
    public function setTitle(string $title): Post
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return Post
     */
    public function setSummary(string $summary): Post
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return Post
     */
    public function setContent(string $content): Post
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @return Post
     */
    public function addRating(): Post
    {
        $this->rating += 1;

        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getSimilarPosts()
    {
        return $this->similarPosts;
    }

    /**
     * @return null|string
     */
    function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @param Post $similarPost
     *
     * @return $this
     */
    public function addSimilarPost(Post $similarPost)
    {
        $this->similarPosts->add($similarPost);

        return $this;
    }

    /**
     * @TODO PHPDoc clear method
     */
    public function clearSimilarPosts(): void
    {
        reset($similarPost);
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return Post
     */
    public function setCategory(Category $category): Post
    {
        $this->category = $category;

        return $this;
    }


}
