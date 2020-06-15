# Books Store App

This App will show a basic e-commerce data flow.

## Implemented Features based on the requirements

1.You should store books of two categories (ex: Children, Fiction) and list them for users to buy on the home page of the application. Users should be able to list the books by category.

2.Users can add the books to the shopping cart and remove them when needed from the cart. The total price should be shown on the application all the time and should change based on the books in the cart.

3.On checkout, users should see a full invoice with details of the purchase.

4.When calculating the price, the following conditions should be satisfied;
4.1 If you buy 5 or more Children books you get a 10% discount from the Children books total
4.2 If you buy 10 books from each category you get 5% additional discount from the total bill
4.3 If you have a coupon code (which you can enter and redeem from the invoice page itself) you get a 15% discount for the total bill. In this case, all other discounts will be invalidated.

## Assumptions

1. Application backend already built for adding and view products, category etc. for admin users

2. Assume only two categories exists (Children and Fiction)

3. Payment method not required

4. Customer account validation not required

### Prerequisites

Please follow below link to set up the development environment according to the Symfony 5.1 requirements
```
https://symfony.com/doc/current/setup.html#technical-requirements
```
Tested development environment
```
OS : Linux Mint
PHP 7.1.33
Server version: Apache/2.4.18 (Ubuntu)
MySQL 5.7.26
Composer 1.10.1
yarn 1.22.4
```

### Installing

To clone the website source code from GitHub repo
```
git clone https://github.com/dazimax/basic-symfony-ecommerce-app.git
```

Required to install the Symfony framework vendor modules
```
composer install
yarn install
```

Required to update the MySQL database configuration in .env file
```
username : root
password : ***
database : store

DATABASE_URL=mysql://root:***@127.0.0.1:3306/store?serverVersion=5.7
```

Required to run below commands to create the database and migration sample data
```
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

Required to run below commands to server start
```
php bin/console server:start
```

Demo Video URL
```
https://www.loom.com/share/1ba912aee49b44bd8339a78fc3a206ba
```

## Running the tests

Sample Coupon Codes 
```
coupon-100
coupon-101
coupon-102
coupon-103
coupon-104
coupon-105
```

To run the Unit Test (Execute the command in the terminal) 
```
php bin/phpunit tests/Controller/CheckoutControllerTest.php
```

## Versioning

App version v1.0.0

## Author

* **Dasitha Abeysinghe** - [dazimax@gmail.com](dazimax@gmail.com)

## License

This project is licensed under the GNU Public License - see the [LICENSE](http://opensource.org/licenses/gpl-license.php) file for details
