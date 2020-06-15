<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Coupon;

class CouponFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $coupons = [
            [
                'coupon_name' => 'coupon-100',
                'coupon_discount' => '15'
            ],
            [
                'coupon_name' => 'coupon-101',
                'coupon_discount' => '15'
            ],
            [
                'coupon_name' => 'coupon-102',
                'coupon_discount' => '15'
            ],
            [
                'coupon_name' => 'coupon-103',
                'coupon_discount' => '15'
            ],
            [
                'coupon_name' => 'coupon-104',
                'coupon_discount' => '15'
            ],
            [
                'coupon_name' => 'coupon-105',
                'coupon_discount' => '15'
            ]
        ];
        foreach ($coupons as $coupon) {
            $couponObj = new Coupon();
            $couponObj->setCouponName($coupon['coupon_name']);
            $couponObj->setCouponDiscount($coupon['coupon_discount']);
            $manager->persist($couponObj);
            $manager->flush();
        }
    }
}
