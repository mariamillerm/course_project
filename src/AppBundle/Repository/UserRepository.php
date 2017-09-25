<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param string $username
     *
     * @return User|null
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUser(string $username): ?User
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.email = ?1')
            ->orWhere('u.username = ?1')
            ->setMaxResults(1)
            ->getQuery()
            ->setParameter(1, $username)
            ->getSingleResult();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isUnique(User $user): bool
    {
        $userByUsername = $this->findOneByUsername($user->getUsername());
        $userByEmail = $this->findOneByEmail($user->getEmail());
        if ($userByUsername !== null or $userByEmail !== null) {

            return false;
        }

        return true;
    }

    /**
     * @return User[]
     */
    public function findSubscribers(): array
    {
        return $this->findBy([
            'isSubscribed' => true,
        ]);
    }
}