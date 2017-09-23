<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    /**
     * @param Category $category
     * @param String $oldName
     *
     * @return bool
     */
    public function isUnique(Category $category, String $oldName = null): bool
    {
        $name = $category->getName();
        $category = $this->findOneByName($category->getName());
        if ($category === null or $name === $oldName) {

            return true;
        }

        return false;
    }
}
