<?php
/**
 * CheckoutController Controller
 *
 * @file     Books Store
 * @category 99x
 * @package  99x_books_store
 * @author   Dasitha Abeysinghe <dazimax@gmail.com>
 * @access   public
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\CouponRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\OrderItemRepository;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Category;
use App\Entity\Coupon;
use App\Entity\OrderItem;
use Psr\Log\LoggerInterface;

/**
 * Class CheckoutController
 */
class CheckoutController extends AbstractController
{
    /**
     * @var SessionInterface $cartSession
     */
    private $cartSession;

    /**
     * __construct
     * 
     * @param SessionInterface $session
     * 
     * @return void
     * 
     * @access public
     */
    public function __construct(SessionInterface $session)
    {
        $this->cartSession = $session;
    }

    /**
     * Load checkout page
     * 
     * @Route("/checkout", name="checkout")
     */
    public function index()
    {
        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
        ]);
    }

    /**
     * Calculate Cart Total Discount
     * 
     * @param array $cartItems
     * @param CategoryRepository $categoryRepository
     * 
     * @return float $cartDiscount
     * 
     * @access public
     */
    public function calculateCartDiscount($cartItems = [], CategoryRepository $categoryRepository)
    {
        $totalAmount = 0;
        $cartDiscount = 0;
        $childrenBooksCount = 0;
        $childrenBooksTotal = 0;
        $fictionBookCount = 0;
        $childrenBookCategory = $categoryRepository->getCategoryDetailsByName('Children');
        $childrenCategoryId = $childrenBookCategory->getId();
        $fictionBookCategory = $categoryRepository->getCategoryDetailsByName('Fiction');
        $fictionCategoryId = $fictionBookCategory->getId();

        // calculate total cart amount
        if (is_array($cartItems) || is_object($cartItems)) {
            foreach ($cartItems as $cartItem) {
                $totalAmount += floatval($cartItem['line_total']);
                if (intval($cartItem['product_category_id']) == $childrenCategoryId) {
                    $childrenBooksCount += intval($cartItem['qty']);
                    $childrenBooksTotal += floatval($cartItem['line_total']);
                }
                if (intval($cartItem['product_category_id']) == $fictionCategoryId) {
                    $fictionBookCount += intval($cartItem['qty']);
                }
            }
        }

        // Calculate books disconts based on categories
        $categoryObj = new Category();
        $cartDiscount = $categoryObj->calculateCategoryDiscounts($childrenBooksCount, $fictionBookCount, $childrenBooksTotal, $totalAmount);

        return $cartDiscount;
    }

    /**
     * Calculate Cart Total Summary
     * 
     * @param array $cartItems
     * @param CategoryRepository $categoryRepository
     * 
     * @return array $cartSummary
     * 
     * @access public
     */
    public function calculateCartSummary($cartItems = [], CategoryRepository $categoryRepository)
    {
        $totalAmount = 0;
        $subTotalAmount = 0;
        if (is_array($cartItems) || is_object($cartItems)) {
            foreach ($cartItems as $cartItem) {
                $totalAmount += floatval($cartItem['line_total']);
            }
        } else {
            $totalAmount = 0;
        }
        
        // calculate cart discount
        $cartDiscount = $this->calculateCartDiscount($cartItems, $categoryRepository);
        // calculate sub total amount
        $subTotalAmount = floatval($totalAmount) - floatval($cartDiscount);

        $cartSummary = $this->cartSession->get('cart_summary');
        if (empty($cartSummary)) {
            $cartSummary = [];
        }
        // add coupon data if already applied
        if (!empty($cartSummary) && $cartSummary['is_coupon_applied']) {
            $subTotalAmount = floatval($totalAmount) - floatval($cartSummary['total_discount']);     
            $cartDiscount = $cartSummary['total_discount'];
            $isCouponApplied = true;  
            $coupon = $cartSummary['coupon'];
            $couponRate = $cartSummary['coupon_rate'];
            $couponMessage = $cartSummary['coupon_message'];
        } else {
            $isCouponApplied = false;
            $coupon = '';
            $couponRate = '';
            $couponMessage = '';
        }
        $cartSummary = [
            'total_amount' => number_format($totalAmount, 2),
            'total_discount' => number_format($cartDiscount, 2),
            'total_qty' => count($cartItems),
            'sub_total_amount' => number_format($subTotalAmount, 2),
            'is_coupon_applied' => $isCouponApplied,
            'coupon' => $coupon,
            'coupon_rate' => $couponRate,
            'coupon_message' => $couponMessage
        ];
        
        $this->cartSession->set('cart_summary', $cartSummary);
        return $cartSummary;
    }

    /**
     * Add product item to cart
     * 
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * 
     * @return JsonResponse $response
     * 
     * @access public
     * 
     * @Route("/addtocart", name="checkout_add_to_cart", methods={"post"})
     */
    public function addToCart(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): JsonResponse
    {
        $response = new JsonResponse();
        $postData = json_decode($request->getContent());
        $productId = $postData->product_id;
        $qty = $postData->qty;
        $token = $postData->token;
        $isValidToken = $this->isCsrfTokenValid($productId, $token);

        if ($isValidToken) {

            // Get cart details
            $product = $productRepository->find($productId);
            $bookPrice = $product->getBookPrice();
            // Store cart items
            $cartItems = $this->cartSession->get('cart_items');
            if (empty($cartItems)) {
                $cartItems = [];
            }
            // update qty and line total if already book added to the cart
            $isAlreadyBookAdded = false;
            if (!empty($cartItems)) {
                foreach ($cartItems as $inx => $cartItem) {
                    if ($cartItem['product_id'] == $productId && $cartItem['product_price'] == floatval($bookPrice)) {
                        $alreadyAddedBookQty = $cartItem['qty'];
                        $totalLineQty = $qty + $alreadyAddedBookQty;
                        $lineTotal = floatval($bookPrice) * $totalLineQty;
                        $cartBook = [
                            'product_id' => $productId,
                            'product_name' => $product->getBookName(),
                            'product_category_id' => $product->getCategoryId(),
                            'product_image' => $product->getBookImage(),
                            'product_price' => number_format($bookPrice, 2),
                            'qty' => $totalLineQty,
                            'line_total' => number_format($lineTotal, 2)
                        ];
                        $cartItems[$inx] = $cartBook;
                        $isAlreadyBookAdded = true;
                    }
                }
            }

            if (!$isAlreadyBookAdded) {
                // if already not added add the book to cart
                $lineTotal = floatval($bookPrice) * $qty;
                $cartBook = [
                    'product_id' => $productId,
                    'product_name' => $product->getBookName(),
                    'product_category_id' => $product->getCategoryId(),
                    'product_image' => $product->getBookImage(),
                    'product_price' => number_format($bookPrice, 2),
                    'qty' => $qty,
                    'line_total' => number_format($lineTotal, 2)
                ];
                array_push($cartItems, $cartBook);
            }
            $this->cartSession->set('cart_items', $cartItems);

            // Calculate total cart summary
            $cartSummary = $this->calculateCartSummary($cartItems, $categoryRepository);
            
            $response->setData([
                'status' => true,
                'cart_summary' => $cartSummary
            ]);
            return $response;

        } else {
            // invalid request
            $response->setData([
                'status' => false,
                'error' => 'Invalid request. Please contact support center.'
            ]);
            return $response;
        }
    }

    /**
     * Update navigator bar cart summary
     * 
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * 
     * @return JsonResponse $response
     * 
     * @access public
     * 
     * @Route("/checkcartsummary", name="checkout_menu_cart_summary", methods={"post"})
     */
    public function updateMenuCartSummary(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $response = new JsonResponse();
        $postData = json_decode($request->getContent());
        $token = $postData->token;
        $isValidToken = $this->isCsrfTokenValid('cart', $token);

        if ($isValidToken) {
            // Get Cart Summary
            $cartItems = $this->cartSession->get('cart_items');
            if (empty($cartItems)) {
                $cartItems = [];
            }
            $cartSummary = $this->calculateCartSummary($cartItems, $categoryRepository);
                
            $response->setData([
                'status' => true,
                'cart_summary' => $cartSummary
            ]);
            return $response;
        
        } else {

            // invalid request
            $response->setData([
                'status' => false,
                'error' => 'Invalid request. Please contact support center.'
            ]);
            return $response;
        }
    }

    /**
     * Remove product item from cart
     * 
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * 
     * @return JsonResponse $response
     * 
     * @access public
     * 
     * @Route("/removeItemFromcart", name="checkout_remove_from_cart", methods={"post"})
     */
    public function removeItemFromCart(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $response = new JsonResponse();
        $postData = json_decode($request->getContent());
        $productId = $postData->product_id;
        $token = $postData->token;
        $isValidToken = $this->isCsrfTokenValid($productId, $token);

        if ($isValidToken) {

            // Get cart details
            $cartItems = $this->cartSession->get('cart_items');
            if (empty($cartItems)) {
                $cartItems = [];
            }
            // Remove book if already added to the cart
            $isAlreadyBookAdded = false;
            if (!empty($cartItems)) {
                foreach ($cartItems as $inx => $cartItem) {
                    if ($cartItem['product_id'] == $productId) {
                        unset($cartItems[$inx]);
                        $isAlreadyBookAdded = true;
                    }
                }
            }
            // rebase array key indexes
            if ($isAlreadyBookAdded) {
                $cartItems = array_values($cartItems);
            }
            $this->cartSession->set('cart_items', $cartItems);

            // Remove coupon from cart
            $cartSummary['is_coupon_applied'] = false;
            $this->cartSession->set('cart_summary', $cartSummary);

            // Calculate total amount
            $cartSummary = $this->calculateCartSummary($cartItems, $categoryRepository);
            
            $response->setData([
                'status' => true,
                'cart_summary' => $cartSummary
            ]);
            return $response;

        } else {
            // invalid request
            $response->setData([
                'status' => false,
                'error' => 'Invalid request. Please contact support center.'
            ]);
            return $response;
        }
    }

    /**
     * Apply coupon code to cart
     * 
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @param CouponRepository $couponRepository
     * 
     * @return JsonResponse $response
     * 
     * @access public
     * 
     * @Route("/applycoupon", name="apply_coupon_code", methods={"post"})
     */
    public function applyCouponCode(Request $request, CategoryRepository $categoryRepository, CouponRepository $couponRepository): JsonResponse
    {
        $response = new JsonResponse();
        $postData = json_decode($request->getContent());
        $couponCode = $postData->coupon;
        $token = $postData->token;
        $isValidToken = $this->isCsrfTokenValid('coupon', $token);

        if ($isValidToken) {

            // Verify coupon is valid or not
            $isCouponValid = false;
            $coupon = $couponRepository->getCoupon($couponCode);
            if ($coupon != null) {
                $isCouponValid = true;
            }

            if ($isCouponValid) {
                $couponDiscount = $coupon->getCouponDiscount();

                // Get cart details
                $cartItems = $this->cartSession->get('cart_items');
                if (empty($cartItems)) {
                    $cartItems = [];
                }
                
                // Calculate total amount
                $cartSummary = $this->calculateCartSummary($cartItems, $categoryRepository);
                
                // apply coupon discount
                // In this case, all other discounts will be invalidated.
                $discount = 0;
                $totalAmount = $cartSummary['total_amount'];
                $couponObj = new Coupon();
                $discount = $couponObj->calculateCategoryDiscounts($totalAmount, $couponDiscount);                
                $subTotalAmount = floatval($totalAmount) - floatval($discount);

                $cartSummary['total_discount'] = number_format($discount, 2);
                $cartSummary['sub_total_amount'] = number_format($subTotalAmount, 2);
                $cartSummary['is_coupon_applied'] = true;
                $cartSummary['coupon'] = $couponCode;
                $cartSummary['coupon_rate'] = $couponDiscount.'%';
                $cartSummary['coupon_message'] = 'Your coupon code ('.$couponCode.') successfully applied with '.$couponDiscount.'% discount rate.';

                // set updated data to session
                $this->cartSession->set('cart_summary', $cartSummary);

                $response->setData([
                    'status' => true,
                    'cart_summary' => $cartSummary
                ]);
                return $response;

            } else {
                $response->setData([
                    'status' => false,
                    'error' => 'Invalid coupon code ('.$couponCode.') applied. Please review the coupon code.'
                ]);
                return $response;
            }

        } else {
            // invalid request
            $response->setData([
                'status' => false,
                'error' => 'Invalid request. Please contact support center.'
            ]);
            return $response;
        }
    }

    /**
     * Remove applied coupon code from cart
     * 
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @param CouponRepository $couponRepository
     * 
     * @return JsonResponse $response
     * 
     * @access public
     * 
     * @Route("/removecoupon", name="remove_coupon_code", methods={"post"})
     */
    public function removeCouponCode(Request $request, CategoryRepository $categoryRepository, CouponRepository $couponRepository): JsonResponse
    {
        $response = new JsonResponse();
        $postData = json_decode($request->getContent());
        $token = $postData->token;
        $isValidToken = $this->isCsrfTokenValid('cart', $token);

        if ($isValidToken) {

            // Get cart details
            $cartItems = $this->cartSession->get('cart_items');
            if (empty($cartItems)) {
                $cartItems = [];
            }
            // Remove coupon from cart
            $cartSummary['is_coupon_applied'] = false;
            $this->cartSession->set('cart_summary', $cartSummary);
            // Calculate total amount
            $cartSummary = $this->calculateCartSummary($cartItems, $categoryRepository);
            $response->setData([
                'status' => true,
                'cart_summary' => $cartSummary
            ]);
            return $response;

        } else {
            // invalid request
            $response->setData([
                'status' => false,
                'error' => 'Invalid request. Please contact support center.'
            ]);
            return $response;
        }
    }

    /**
     * View Added to cart books
     * 
     * @return Response
     * 
     * @access public
     * 
     * @Route("/cart", name="view_cart", methods={"get"})
     */
    public function viewCart(): Response
    {
        $cartData = $this->cartSession->all();
        if (!isset($cartData['cart_items'])) {
            return $this->redirectToRoute('homepage');
        }
        return $this->render('checkout/cart.html.twig', [
            'cartItems' => $cartData,
        ]);
    }

    /**
     * process Checkout
     * 
     * @return Response
     * 
     * @access public
     * 
     * @Route("/checkout", name="checkout", methods={"get"})
     */
    public function checkout(): Response
    {
        $cartData = $this->cartSession->all();
        if (!isset($cartData['cart_items'])) {
            return $this->redirectToRoute('homepage');
        } else {
            return $this->render('checkout/index.html.twig', [
                'cartItems' => $cartData,
            ]);
        }
    }

    /**
     * Loop form data and pick the value by name
     * 
     * @param String $name
     * @param array $formData
     * 
     * @return String $value
     * 
     * @access public
     */
    public function getFormValueByName($name = null, $formData = []): String
    {
        $value = '';
        if (!empty($formData)) {
            $formData = $formData->form_data;
            foreach ($formData as $data) {
                if ($data->name == $name) {
                    $value = $data->value;
                }
            }
        }
        return $value;
    }

    /**
     * process Checkout
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param OrderRepository $orderRepository
     * @param OrderItemRepository $orderItemRepository
     * @param UserPasswordEncoderInterface $encoder
     * @param LoggerInterface $logger
     * 
     * @return JsonResponse $response
     * 
     * @access public
     * 
     * @Route("/placeOrder", name="checkout_place_order", methods={"post"})
     */
    public function placeOrder(Request $request, UserRepository $userRepository, OrderRepository $orderRepository, OrderItemRepository $orderItemRepository, UserPasswordEncoderInterface $encoder, LoggerInterface $logger): Response
    {
        $response = new JsonResponse();
        $postData = json_decode($request->getContent());
        $token = $this->getFormValueByName('token', $postData);
        $isValidToken = $this->isCsrfTokenValid('place_order', $token);
        
        $cartData = $this->cartSession->all();
        if ($isValidToken && isset($cartData['cart_items'])) {
            
            try {

                $entityManager = $this->getDoctrine()->getManager();
                
                // User details
                $userObj = new User();
                $userPassword = $this->getFormValueByName('password', $postData);
                $encodedPassword = $encoder->encodePassword($userObj, $userPassword);
                $userRole[] = 'customer';
                $firstName = $this->getFormValueByName('first_name', $postData);
                $lastName = $this->getFormValueByName('last_name', $postData);
                $mobileNo = $this->getFormValueByName('mobile_no', $postData);
                $addressLine1 = $this->getFormValueByName('address_line_1', $postData);
                $addressLine2 = $this->getFormValueByName('address_line_2', $postData);
                $country = $this->getFormValueByName('country', $postData);
                $state = $this->getFormValueByName('state', $postData);
                $postCode = $this->getFormValueByName('post_code', $postData);
                $postCode = intval($postCode);

                // Check user already added
                $email = $this->getFormValueByName('email', $postData);
                $userRecord = $userRepository->getUserDetailsByEmail($email);
                if (!is_null($userRecord)) {
                    $userObj = $userRecord;
                    // update details
                    $userObj->setMobileNo($mobileNo); 
                    $userObj->setAddressLine1($addressLine1); 
                    $userObj->setAddressLine2($addressLine2); 
                    $userObj->setCountry($country); 
                    $userObj->setState($state); 
                    $userObj->setPostCode($postCode); 
                } else {
                    // add new user record
                    $userObj->setEmail($email); 
                    $userObj->setRoles($userRole);
                    $userObj->setPassword($encodedPassword); 
                    $userObj->setFirstName($firstName); 
                    $userObj->setLastName($lastName); 
                    $userObj->setMobileNo($mobileNo); 
                    $userObj->setAddressLine1($addressLine1); 
                    $userObj->setAddressLine2($addressLine2); 
                    $userObj->setCountry($country); 
                    $userObj->setState($state); 
                    $userObj->setPostCode($postCode); 
                }
                $entityManager->persist($userObj);
                $entityManager->flush();

                $userId = $userObj->getId();

                // Add order details
                $createdAt = new \DateTime();
                $totalOrderAmount = $cartData['cart_summary']['sub_total_amount'];
                $coupon = $cartData['cart_summary']['coupon'];
                $totalDiscount = $cartData['cart_summary']['total_discount'];

                $orderObj = new Order();
                $orderObj->setUserId($userId);
                $orderObj->setTotalOrderAmount($totalOrderAmount);
                $orderObj->setCoupon($coupon);
                $orderObj->setDiscount($totalDiscount);
                $orderObj->setCreatedAt($createdAt);

                $entityManager->persist($orderObj);
                $entityManager->flush();

                $orderId = $orderObj->getId();
                
                // Add order items
                $cartItems = $cartData['cart_items'];
                foreach($cartItems as $cartItem) {
                    
                    $orderItemObj = new OrderItem();
                    $orderItemObj->setOrderId($orderId);
                    $orderItemObj->setProductId($cartItem['product_id']);
                    $orderItemObj->setProductPrice($cartItem['product_price']);
                    $orderItemObj->setProductQty($cartItem['qty']);
                    $orderItemObj->setCreatedAt($createdAt);
                    
                    $entityManager->persist($orderItemObj);
                    $entityManager->flush();
                }

                // set order Id
                $this->cartSession->set('order_id', $orderId);

                $response->setData([
                    'status' => true,
                    'order_id' => $orderId
                ]);
                return $response;
                
            } catch (Exception $e) {
                $error = $e->getTraceAsString();
                $logger->error('Error on CheckoutController :: placeOrder');
                $logger->error($error);
                $response->setData([
                    'status' => false,
                    'error' => 'Error with place an order. Please contact support center.'
                ]);
                return $response;
            }
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * View the order complete page
     * 
     * @return Response
     * 
     * @access public
     * 
     * @Route("/complete", name="ordercomplete")
     */
    public function orderComplete(): Response
    {
        $cartData = $this->cartSession->all();
        if (!isset($cartData['cart_items'])) {
            return $this->redirectToRoute('homepage');
        } else {
            $cartData = $this->cartSession->all();
            $orderId = $cartData['order_id']; 
            // clear the cart data
            $this->cartSession->remove('order_id');
            $this->cartSession->remove('cart_items');
            $this->cartSession->remove('cart_summary');

            return $this->render('checkout/complete.html.twig', [
                'orderid' => $orderId
            ]);
        }
    }
}
