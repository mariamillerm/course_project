<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="posts")
 * @ORM\Entity()
 */
class Post
{
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="error.upload.notBlank")
     * @Assert\File(uploadErrorMessage="error.upload", maxSize="10M")
     */
    private $image;

	/**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Empty title")
     */
    private $title;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    private $summary;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $creationDate;
    
    /**
     * @ORM\ManyToMany(targetEntity="Post")
     * @ORM\JoinTable(name="similarPosts")
     * @Assert\Count(min=0, max=5)
     */
    private $similarPosts;

    /**
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


    public function getId()
    {
        return $this->id;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getSummary()
    {
        return $this->summary;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getCreationDate()
    {
        return $this->creationDate;
    }
    public function getCategory()
    {
        return $this->category;
    }
    public function getAuthor()
    {
        return $this->author;
    }
    public function getSimilarPosts()
    {
        return $this->similarPosts;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }
    public function addSimilarPost(Post $similarPost)
    {
        $this->similarPosts[] = $similarPost;

        return $this;
    }
    public function removeSimilarPost(Post $similarPost)
    {
        $this->similarPosts->removeElement($similarPost);
    }

    function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param mixed $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
}