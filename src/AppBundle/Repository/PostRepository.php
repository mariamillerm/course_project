<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Post;
use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    /**
     * @param \DateTime $latestExecution
     *
     * @return Post[]
     */
    public function findNewPosts(\DateTime $latestExecution): array
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.creationDate > ?1')
            ->orderBy('p.rating', 'asc')
            ->setMaxResults(5)
            ->getQuery()
            ->setParameter(1, $latestExecution)
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getPostsQuery()
    {
        return $this
            ->createQueryBuilder('p')
            ->orderBy('p.id', 'asc')
            ->getQuery();
    }

    /**
     * @param Post $post
     * @param String $oldName
     *
     * @return bool
     */
    public function isUnique(Post $post, String $oldName = null): bool
    {
        $title = $post->getTitle();
        $post = $this->findOneByTitle($post->getTitle());
        if ($post === null or $title === $oldName) {

            return true;
        }

        return false;
    }
}