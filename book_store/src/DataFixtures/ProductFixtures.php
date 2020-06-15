<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Repository\CategoryRepository;

class ProductFixtures extends Fixture
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function load(ObjectManager $manager)
    {
        $childrenBookCategory = $this->categoryRepository->getCategoryDetailsByName('Children');
        $childrenCategoryId = $childrenBookCategory->getId();
        $fictionBookCategory = $this->categoryRepository->getCategoryDetailsByName('Fiction');
        $fictionCategoryId = $fictionBookCategory->getId();

        $products = [
            [
                'book_name' => "The Handmaid's Tale: The Graphic Novel",
                'book_price' => '30',
                'category_id' => $fictionCategoryId,
                'book_image' => 'TheHandmaid.jpeg',
                'book_author' => 'Margaret Atwood'
            ],
            [
                'book_name' => 'The Great Gatsby',
                'book_price' => '20',
                'category_id' => $fictionCategoryId,
                'book_image' => 'TheGreatGatsby.jpeg',
                'book_author' => 'F. Scott Fitzgerald'
            ],
            [
                'book_name' => 'All the Light We Cannot See',
                'book_price' => '30',
                'category_id' => $fictionCategoryId,
                'book_image' => 'AlltheLightWeCannotSee.jpeg',
                'book_author' => 'Anthony Doerr'
            ],
            [
                'book_name' => 'Matilda',
                'book_price' => '25',
                'category_id' => $childrenCategoryId,
                'book_image' => 'Matilda.jpeg',
                'book_author' => 'Roald Dahl'
            ],
            [
                'book_name' => 'The Tale of Peter Rabbit',
                'book_price' => '15',
                'category_id' => $childrenCategoryId,
                'book_image' => 'TheTaleofPeterRabbit.jpg',
                'book_author' => 'Beatrix Potter'
            ],
            [
                'book_name' => 'The Lion, The Witch and The Wardrobe',
                'book_price' => '30',
                'category_id' => $childrenCategoryId,
                'book_image' => 'TheLionWardrobe.jpeg',
                'book_author' => 'C. S. Lewis'
            ]
        ];
        foreach ($products as $product) {
            $productObj = new Product();
            $productObj->setBookName($product['book_name']);
            $productObj->setBookPrice($product['book_price']);
            $productObj->setCategoryId($product['category_id']);
            $productObj->setBookImage($product['book_image']);
            $productObj->setBookAuthor($product['book_author']);
            $manager->persist($productObj);
            $manager->flush();
        }
    }
}
