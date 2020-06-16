# Books Store App

This App will show basic e-commerce feature with Symfony Framework.

### Prerequisites

Please follow below link to set up the development environment according to the Symfony requirements

<a href="https://symfony.com/doc/current/setup.html#technical-requirements">https://symfony.com/doc/current/setup.html#technical-requirements</a>

Tested development environment
```
OS : Linux Mint
PHP 7.3.66
Server version: Apache/2.4.18 (Ubuntu)
MySQL 5.7.26
Composer 1.10.1
yarn 1.22.1
```

### Installing

To clone the website source code from GitHub repo
```
git clone https://github.com/dazimax/basic-symfony-ecommerce-app.git
```

Required to install the vendor modules
```
composer install
yarn install
```

Required to update MySQL database configurations in .env file
```
username : root
password : ***
database : store

DATABASE_URL=mysql://root:***@127.0.0.1:3306/store?serverVersion=5.7
```

Required to run below commands to setup the Database and migrate sample data
```
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

Required to run for start the server
```
php bin/console server:start
```

Demo Video URL

<a href="https://www.loom.com/share/1ba912aee49b44bd8339a78fc3a206ba">https://www.loom.com/share/1ba912aee49b44bd8339a78fc3a206ba</a>

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

To run the Unit Test Cases (Execute the command in the terminal) 
```
php bin/phpunit tests/Controller/CheckoutControllerTest.php
```

## Versioning

App version v1.0.0

## Author

* **Dasitha Abeysinghe** - [dazimax@gmail.com](dazimax@gmail.com)

