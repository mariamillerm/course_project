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
     *
     * @return bool
     */
    public function isUnique(Post $post): bool
    {
        $post = $this->findOneByUsername($post->getTitle());
        if ($post !== null) {

            return false;
        }

        return true;
    }
}