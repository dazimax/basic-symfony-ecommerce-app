<?php
/**
 * Category Entity
 *
 * @file     Books Store
 * @category 99x
 * @package  99x_books_store
 * @author   Dasitha Abeysinghe <dazimax@gmail.com>
 * @access   public
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Calculate Books Discounts based on Category
     * 
     * @param int $childrenBooksCount
     * @param int $fictionBookCount
     * @param float $childrenBooksTotal
     * @param float $totalAmount
     * 
     * @return float cartDiscount
     * 
     * @access public
     */
    public function calculateCategoryDiscounts($childrenBooksCount = 0, $fictionBookCount = 0, $childrenBooksTotal = 0, $totalAmount = 0)
    {
        $cartDiscount = 0;
        // If you buy 5 or more Children books you get a 10% discount from the Children books total
        if ($childrenBooksCount >= 5) {
            $cartDiscount += floatval($childrenBooksTotal) * 0.10;
        }
        // If you buy 10 books from each category you get 5% additional discount from the total bill
        if ($childrenBooksCount == 10 && $fictionBookCount == 10) {
            $cartDiscount += floatval($totalAmount) * 0.05;
        }
        return $cartDiscount;
    }
}
