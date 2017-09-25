<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;

class PostRepository extends PaginatedEntityRepository
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
     * @param array $parameters
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery(array $parameters)
    {
        $parameters['sortbyfield'] = $parameters['sortbyfield'] ?? 'creationDate';
        $parameters['order'] = $parameters['order'] ?? 'desc';
        $query = parent::createQuery($parameters);

        return $query;
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
     * @param Category $category
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCategoryPostsQuery(Category $category)
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.category = ?1')
            ->orderBy('p.id', 'asc')
            ->setParameter(1, $category)
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