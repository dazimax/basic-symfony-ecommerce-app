<?php
/**
 * Coupon Entity
 *
 * @file     Books Store
 * @category 99x
 * @package  99x_books_store
 * @author   Dasitha Abeysinghe <dazimax@gmail.com>
 * @access   public
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CouponRepository::class)
 */
class Coupon
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $coupon_name;

    /**
     * @ORM\Column(type="float")
     */
    private $coupon_discount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCouponName(): ?string
    {
        return $this->coupon_name;
    }

    public function setCouponName(string $coupon_name): self
    {
        $this->coupon_name = $coupon_name;

        return $this;
    }

    public function getCouponDiscount(): ?float
    {
        return $this->coupon_discount;
    }

    public function setCouponDiscount(float $coupon_discount): self
    {
        $this->coupon_discount = $coupon_discount;

        return $this;
    }

    /**
     * Calculate Coupon Discount
     * If you have a coupon code you get a 15% discount for the total bill.
     * 
     * @param float $totalAmount
     * @param float $couponDiscount
     * 
     * @return float discount
     * 
     * @access public
     */
    public function calculateCategoryDiscounts($totalAmount = 0, $couponDiscount = 15)
    {
        // Calculate coupon discount
        $discount = 0;
        $discount = (floatval($totalAmount) * floatval($couponDiscount) ) / 100;
        return $discount;
    }
}
