<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="posts")
 * @ORM\Entity()
 */
class Post
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="error.upload.notBlank")
     * @Assert\File(uploadErrorMessage="error.upload", maxSize="10M")
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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Empty title")
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
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", onDelete="CASCADE")
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
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Post")
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
     */
    public function __construct()
    {
        $this->similarPosts = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return Post
     */
    public function setImage(string $image): Post
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
     * @param int $id
     *
     * @return Post
     */
    public function setId(int $id): Post
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
    public function getSummary(): string
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
    public function getContent(): string
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
     * @param User $author
     *
     * @return Post
     */
    public function setAuthor(User $author): Post
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return Post
     */
    public function setCreationDate(\DateTime $creationDate): Post
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     *
     * @return Post
     */
    public function setRating(int $rating): Post
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @param Post $similarPost
     *
     * @return $this
     */
    public function addSimilarPost(Post $similarPost)
    {
        $this->similarPosts[] = $similarPost;

        return $this;
    }

    /**
     * @param Post $similarPost
     */
    public function removeSimilarPost(Post $similarPost)
    {
        $this->similarPosts->removeElement($similarPost);
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getTitle();
    }

}