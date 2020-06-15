<?php
/**
 * CheckoutControllerTest Test
 *
 * @file     Books Store
 * @category 99x
 * @package  99x_books_store
 * @author   Dasitha Abeysinghe <dazimax@gmail.com>
 * @access   public
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Coupon;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class CheckoutControllerTest
 */
class CheckoutControllerTest extends TestCase
{
    /**
     * Test Case 1 - 6 Chidlren books Qty with 10% discount from children book total
     * 
     * @return void
     * 
     * @access public 
     */
    public function testCategoryDiscountOne()
    {
        $categoryObj = new Category();
        $childrenBooksCount = 6;
        $fictionBookCount = 0;
        $childrenBooksTotal = 600;
        $totalAmount = 700;
        $cartDiscount = $categoryObj->calculateCategoryDiscounts($childrenBooksCount, $fictionBookCount, $childrenBooksTotal, $totalAmount);
        // Test Case 1 - Expected result
        $this->assertEquals(60, $cartDiscount);
    }

    /**
     * Test Case 2 - 10 Chidlren Books Qty & 10 Fiction Books Qty 
     * with 5% additional discount from total bill with Test Case 1 discount
     * 
     * @return void
     * 
     * @access public 
     */
    public function testCategoryDiscountTwo()
    {
        $categoryObj = new Category();
        $childrenBooksCount = 10;
        $fictionBookCount = 10;
        $childrenBooksTotal = 600;
        $totalAmount = 1000;
        $cartDiscount = $categoryObj->calculateCategoryDiscounts($childrenBooksCount, $fictionBookCount, $childrenBooksTotal, $totalAmount);
        // Test Case 2 - Expected result
        $this->assertEquals(110, $cartDiscount);
    }

    /**
     * Test Case 3 - If you have a coupon code you get a 15% discount for the total bill.
     * 
     * @return void
     * 
     * @access public 
     */
    public function testCouponDiscountOne()
    {
        $couponObj = new Coupon();
        $totalAmount = 1000;
        $discount = $couponObj->calculateCategoryDiscounts($totalAmount);
        // Test Case 3 - Expected result
        $this->assertEquals(150, $discount);
    }
}