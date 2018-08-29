# Visualsoft PHP SOAP Bindings

The purpose of this library is to aid communication with Visualsoft's SOAP API.

This library currently supports version 3 of the Visualsoft WSDL.

## Installation

To install with Composer:

```
composer require mikkelson/visualsoft-php-soap-api
```

After the package installation completes, use the autoloader provided by Composer.

```php
require __DIR__ . '/vendor/autoload.php';
```

## Usage & Setup

Load the package namespace.

```php
use Mikkelson\Visualsoft;
```

Before making useful calls to Visualsoft, you must first set the client.

```php
    
    $credentials = [
        'client_id' => 'YOUR VISUALSOFT CLIENT ID',
        'username' => 'YOUR VISUALSOFT API USERNAME',
        'password' => 'YOUR VISUALSOFT API PASSWORD',
        'domain' => 'YOUR VISUALSOFT DOMAIN NAME'
    ];

    $vs = new VisualSoft();
    $vs->setClient($credentials);

```

## Hello World

This call returns the string `Hello World` if successful, useful to test your API connectivity.

```php
$vs->helloWorld();
```

## Get Orders By Date

Return a list of all orders from a specified date.

```php
$date = new DateTime();
$vs->getOrdersByDate($date);
```

## Get Order By ID

Returns order data for a specified order using the order id.

```php
$order_id = 1;
$vs->getOrderById($order_id);
```

## Get Order By Reference

Returns order data for a specified order using the order reference.

```php
$order_ref = 'SO1000';
$vs->getOrderByRef($order_ref);
```

## Update Order Status

Updates the status of an order.

```php
$order_id = 1;
$status = 'Order Dispatched'
$tracking = 123456; //Order tracking number
$comments = 'Order dispatched'; // Order comment

//the 5th parameter is optional. Defaults to true. When true, VisualSoft will email the customer informing of the update to the order.
$email_customer = false; 

$vs->updateOrderStatus($order_id, $status, $tracking, $comments, $email_customer);
```

## Get New Orders

Returns all orders that have not yet been marked as downloaded. 

Pass in true/false to have the new orders automatically marked as `Downloaded`. Defaults to false.

```php
$order_ref = 'SO1000';
$vs->getNewOrders(true);
```
