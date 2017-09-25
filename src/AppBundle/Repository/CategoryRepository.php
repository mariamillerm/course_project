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

    /**
     * @return Category[]
     */
    public function categoriesUnderRoot(): array
    {
        $rootCategory = $this->find(2);
        $categories = $this->findByParent($rootCategory);

        return $categories;
    }

    /**
     * @param Category $category
     *
     * @return Category[]
     */
    public function categoriesUnderCurrentCategory(Category $category): array
    {
        $categories = $this->findByParent($category);

        return $categories;
    }
}
