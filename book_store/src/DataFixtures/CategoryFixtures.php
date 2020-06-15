<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categories = ['Children', 'Fiction'];
        foreach ($categories as $category) {
            $categoryObj = new Category();
            $categoryObj->setName($category);
            $manager->persist($categoryObj);
            $manager->flush();
        }
    }
}
