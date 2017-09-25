<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class PaginatedEntityRepository extends EntityRepository
{
    public function count(QueryBuilder $query)
    {
        $query->select('count(e)');

        try {
            return $query->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return -1;
        }
    }

    /**
     * @param $query
     * @param int $page
     * @param int $perPage
     *
     * @return array
     */
    public function paginate(QueryBuilder $query, int $page, int $perPage)
    {
        $query
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage);

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $parameters
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery(array $parameters)
    {
        $query = $this->createQueryBuilder('e');

        if (isset($parameters['filterbyfield']) && isset($parameters['pattern'])) {
            if ($parameters['filterbyfield'] !== null && $parameters['pattern'] !== null) {
                $query
                    ->where('e.'.$parameters['filterbyfield'] . ' = :pattern')
                    ->setParameter(':pattern', $parameters['pattern']);
            }
        }
        if (isset($parameters['sortbyfield']) && isset($parameters['order'])) {
            if ($parameters['sortbyfield'] !== null && $parameters['order'] !== null) {
                $query->orderBy('e.'.$parameters['sortbyfield'], $parameters['order']);
            }
        }

        return $query;
    }
}
