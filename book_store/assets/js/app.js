/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

$(document).ready(function() {

    // hide message boxes
    var timeOutDelay = 2000;
    $(".alert-success").hide();
    $(".alert-success").removeClass('d-none');
    $(".alert-danger").hide();
    $(".alert-danger").removeClass('d-none');
    $(".cart-coupon-remove-button").hide();
    $(".cart-coupon-remove-button").removeClass('d-none');
    
    // validation email function
    function validateEmail(email) {
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            return true;
        } else {
            return false;
        }
    }

    // check cart total amount
    var token = $('#cart-link').data('token');
    var formData = JSON.stringify({ "token": token });
    $.ajax({
        type: 'POST',
        contentType: "application/json; charset=utf-8",
        url: '/checkcartsummary',
        data: formData,
        success: function (data) {
            if (data.status) {
                var cartTotalAmount = data.cart_summary.total_amount;
                var cartTotalItemQty = data.cart_summary.total_qty;
                var cartTotalDiscount = data.cart_summary.total_discount;
                var cartSubTotalAmount = data.cart_summary.sub_total_amount;
                $(".nav-total-cart-amount").text("("+cartSubTotalAmount+")");
                $(".nav-total-cart-qty").text("("+cartTotalItemQty+")");
            } else {
                $(".alert-danger").text('Some error occured. Please contact support center.');
                $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        },
        error: function (data) {
            console.error('An error occurred.');
            //console.error(data);
            $(".alert-danger").text('Some error occured. Please contact support center.');
            $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
            window.scrollTo({top: 0, behavior: 'smooth'});
        },
    });

    // add to cart
    $('.add-to-cart-button').click(function(e) {
        e.preventDefault();
        
        $(".alert-success").hide();
        $(".alert-danger").hide();

        var productId = $(this).data('id');
        var qty = $(this).parents('.card-body').find('.book-qty').val();
        var token = $(this).data('token');
        var formData = JSON.stringify({ "product_id": productId, "token": token, "qty": qty });
        $.ajax({
            type: 'POST',
            contentType: "application/json; charset=utf-8",
            url: '/addtocart',
            data: formData,
            success: function (data) {
                if (data.status) {
                    var cartTotalAmount = data.cart_summary.total_amount;
                    var cartTotalItemQty = data.cart_summary.total_qty;
                    var cartTotalDiscount = data.cart_summary.total_discount;
                    var cartSubTotalAmount = data.cart_summary.sub_total_amount;
                    $(".nav-total-cart-amount").text("("+cartSubTotalAmount+")");
                    $(".nav-total-cart-qty").text("("+cartTotalItemQty+")");
                    $(".alert-success").text(qty+' Book(s) succesfully added to the Cart!');
                    $(".alert-success").fadeIn().delay(timeOutDelay).fadeOut();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    // reset qty of all books
                    $('.book-qty').val(1);
                } else {
                    $(".alert-danger").text('Some error occured. Please contact support center.');
                    $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                }
            },
            error: function (data) {
                console.error('An error occurred.');
                //console.error(data);
                $(".alert-danger").text('Some error occured. Please contact support center.');
                $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                window.scrollTo({top: 0, behavior: 'smooth'});
            },
        });
    });

    // remove book from cart
    $('.cart-item-remove-button').click(function(e) {
        e.preventDefault();
        
        $(".alert-success").hide();
        $(".alert-danger").hide();

        var productId = $(this).data('id');
        var token = $(this).data('token');
        var formData = JSON.stringify({ "product_id": productId, "token": token });
        $.ajax({
            type: 'POST',
            contentType: "application/json; charset=utf-8",
            url: '/removeItemFromcart',
            data: formData,
            success: function (data) {
                if (data.status) {
                    var cartTotalAmount = data.cart_summary.total_amount;
                    var cartTotalItemQty = data.cart_summary.total_qty;
                    var cartTotalDiscount = data.cart_summary.total_discount;
                    var cartSubTotalAmount = data.cart_summary.sub_total_amount;
                    $(".nav-total-cart-amount").text("("+cartSubTotalAmount+")");
                    $(".nav-total-cart-qty").text("("+cartTotalItemQty+")");
                    $(".alert-success").text('Book succesfully removed from the Cart!');
                    $(".alert-success").fadeIn().delay(timeOutDelay).fadeOut();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    // reset qty of all books
                    $('.book-qty').val(1);
                    // remove book from cart
                    $('#cart-book-'+productId).remove();
                    // update cart total amount
                    $('#cart-total-amount').text(cartTotalAmount);
                    // update cart total discount
                    $('#cart-total-discount').text(cartTotalDiscount);
                    // update cart sub total amount
                    $('#cart-sub-total-amount').text(cartSubTotalAmount);
                    // hide proceed to checkout if qty = 0
                    if (parseInt(cartTotalItemQty) == 0) {
                        $('.cart-summary-action-box').hide();
                    } else {
                        $('.cart-summary-action-box').show();
                    }
                } else {
                    $(".alert-danger").text('Some error occured. Please contact support center.');
                    $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                }
            },
            error: function (data) {
                console.error('An error occurred.');
                //console.error(data);
                $(".alert-danger").text('Some error occured. Please contact support center.');
                $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                window.scrollTo({top: 0, behavior: 'smooth'});
            },
        });
    });

    // apply coupon code
    $('#apply-coupon-button').click(function(e) {
        e.preventDefault();
        
        $("#applied-coupon-success").hide();
        $("#applied-coupon-danger").hide();
        $(".cart-coupon-remove-button").hide();

        var couponCode = $("#coupon").val();
        var token = $(this).data('token');
        var formData = JSON.stringify({ "coupon": couponCode, "token": token });

        if (couponCode.length == 0) {
            $("#applied-coupon-danger").text('Please add valid coupon code.');
            $("#applied-coupon-danger").fadeIn().delay(timeOutDelay).fadeOut();
                        
        } else {
            $.ajax({
                type: 'POST',
                contentType: "application/json; charset=utf-8",
                url: '/applycoupon',
                data: formData,
                success: function (data) {
                    if (data.status) {
                        var cartTotalAmount = data.cart_summary.total_amount;
                        var cartTotalItemQty = data.cart_summary.total_qty;
                        var cartTotalDiscount = data.cart_summary.total_discount;
                        var cartSubTotalAmount = data.cart_summary.sub_total_amount;
                        var couponRate = data.cart_summary.coupon_rate;
                        $(".nav-total-cart-amount").text("("+cartSubTotalAmount+")");
                        $(".nav-total-cart-qty").text("("+cartTotalItemQty+")");
                        $("#applied-coupon-success").text('Your coupon code ('+couponCode+') successfully applied with '+couponRate+' discount rate.');
                        $("#applied-coupon-success").fadeIn();
                        $(".cart-coupon-remove-button").fadeIn();
                        $("#coupon-box").hide();
                        // update cart total amount
                        $('#cart-total-amount').text(cartTotalAmount);
                        // update cart total discount
                        $('#cart-total-discount').text(cartTotalDiscount);
                        // update cart sub total amount
                        $('#cart-sub-total-amount').text(cartSubTotalAmount);
                    } else {
                        $("#applied-coupon-danger").text(data.error);
                        $("#applied-coupon-danger").fadeIn().delay(timeOutDelay).fadeOut();
                        $(".cart-coupon-remove-button").hide();
                    }
                },
                error: function (data) {
                    console.error('An error occurred.');
                    //console.error(data);
                    $("#applied-coupon-danger").text('Some error occured. Please contact support center.');
                    $("#applied-coupon-danger").fadeIn().delay(timeOutDelay).fadeOut();
                    $(".cart-coupon-remove-button").hide();
                },
            });
        }
    });

    // remove applied coupon code
    $('.cart-coupon-remove-button').click(function(e) {
        e.preventDefault();
        
        $("#applied-coupon-success").hide();
        $("#applied-coupon-danger").hide();
        $(".cart-coupon-remove-button").hide();

        var token = $('#cart-link').data('token');
        var formData = JSON.stringify({ "token": token });

        $.ajax({
            type: 'POST',
            contentType: "application/json; charset=utf-8",
            url: '/removecoupon',
            data: formData,
            success: function (data) {
                if (data.status) {
                    var cartTotalAmount = data.cart_summary.total_amount;
                    var cartTotalItemQty = data.cart_summary.total_qty;
                    var cartTotalDiscount = data.cart_summary.total_discount;
                    var cartSubTotalAmount = data.cart_summary.sub_total_amount;
                    $(".nav-total-cart-amount").text("("+cartSubTotalAmount+")");
                    $(".nav-total-cart-qty").text("("+cartTotalItemQty+")");
                    $("#applied-coupon-success").text('');
                    $("#applied-coupon-success").removeClass('d-block');
                    $("#applied-coupon-success").hide();
                    $(".cart-coupon-remove-button").removeClass('d-block');
                    $(".cart-coupon-remove-button").hide();
                    $("#coupon-box").fadeIn();
                    $("#coupon").val('');
                    // update cart total amount
                    $('#cart-total-amount').text(cartTotalAmount);
                    // update cart total discount
                    $('#cart-total-discount').text(cartTotalDiscount);
                    // update cart sub total amount
                    $('#cart-sub-total-amount').text(cartSubTotalAmount);
                } else {
                    $("#applied-coupon-danger").text(data.error);
                    $("#applied-coupon-danger").fadeIn().delay(timeOutDelay).fadeOut();
                    $(".cart-coupon-remove-button").hide();
                }
            },
            error: function (data) {
                console.error('An error occurred.');
                //console.error(data);
                $("#applied-coupon-danger").text('Some error occured. Please contact support center.');
                $("#applied-coupon-danger").fadeIn().delay(timeOutDelay).fadeOut();
                $(".cart-coupon-remove-button").hide();
            },
        });
    });

    // place order button click
    $('#place-order').click(function(e) {
        e.preventDefault();
        
        $("alert-success").hide();
        $(".alert-danger").hide();
        
        // validate form
        var isFormValid = true;
        var isOtherValidationsDetected = false;
        $("#checkout-form input").each(function(index, item) {
            var fieldValue = item.value;
            if (item.id == 'mobile' && fieldValue.length > 10) {
                isFormValid = false;
                isOtherValidationsDetected = true;
                $(".alert-danger").text('Please add a correct Mobile Number. Maximum 10 digits are allowed');
                $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                window.scrollTo({top: 0, behavior: 'smooth'});
                $("#mobile").val('');
                return false;
            }
            if (item.id == 'post-code' && fieldValue.length > 10) {
                isFormValid = false;
                isOtherValidationsDetected = true;
                $(".alert-danger").text('Please add a correct Post Code. Maximum 10 digits are allowed');
                $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                window.scrollTo({top: 0, behavior: 'smooth'});
                $("#post-code").val('');
                return false;
            }
            if (item.id == 'email') {
                var isValidEmail = validateEmail(fieldValue);
                if (!isValidEmail) {
                    isFormValid = false;
                    isOtherValidationsDetected = true;
                    $(".alert-danger").text('Please add a correct Email Address');
                    $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    return false;
                }
            }
            if (item.id != 'coupon') { // skip coupon code
                if (fieldValue.length == 0) {
                    isFormValid = false;
                }
            }
        });

        if (isFormValid) {
            // process checkout
            var formData = $('#checkout-form').serializeArray();
            formData = JSON.stringify({ "form_data": formData });
            $.ajax({
                type: 'POST',
                contentType: "application/json; charset=utf-8",
                url: '/placeOrder',
                data: formData,
                success: function (data) {
                    if (data.status) {
                        $(".alert-success").text('Your order succesfully placed');
                        $(".alert-success").fadeIn().delay(timeOutDelay).fadeOut();
                        window.scrollTo({top: 0, behavior: 'smooth'});
                        setTimeout(function(){
                            window.location = '/complete';
                        }, 1000);
                    } else {
                        $(".alert-danger").text(data.error);
                        $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    }
                },
                error: function (data) {
                    console.error('An error occurred.');
                    //console.error(data);
                    $(".alert-danger").text('Some error occured. Please contact support center.');
                    $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                },
            });
        } else {
            // invalid form data
            if (!isOtherValidationsDetected) {
                $(".alert-danger").text('Please fill out all the User Details and Delivery / Billing Details');
                $(".alert-danger").fadeIn().delay(timeOutDelay).fadeOut();
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        }
        
    });

});
