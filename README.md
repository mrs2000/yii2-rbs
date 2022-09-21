# RBS
Component for payment through the payment gateway bank "Sber"

[Sber Manual](https://securepayments.sberbank.ru/wiki/doku.php/start)

[![Latest Stable Version](https://img.shields.io/packagist/v/mrssoft/rbs.svg)](https://packagist.org/packages/mrssoft/rbs)
![PHP](https://img.shields.io/packagist/php-v/mrssoft/rbs.svg)
![Github](https://img.shields.io/github/license/mrs2000/yii2-rbs.svg)
![Total Downloads](https://img.shields.io/packagist/dt/mrssoft/rbs.svg)

### Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
Either run
```
php composer.phar require --prefer-dist mrssoft/rbs "*"
```
or add
```
"mrssoft/rbs": "*"
```
to the require section of your `composer.json` file.

### Usage
Register order
```php
    $rbs = new \mrssoft\rbs\Rbs(['userName' => '', 'password' => '']);
    $rbs->credit = true; //if credit
    $rbs->productType = \mrssoft\rbs\Rbs::TYPE_CREDIT_INSTALLMENT;
    
    $rbsOrder = new RbsOrder();
    $rbsOrder->orderNumber = 'NM-12874';
    $rbsOrder->email = 'test@mail.com';
    $rbsOrder->description = 'Test';
    $rbsOrder->returnUrl = 'https:/mysite.com/payment/success';
    $rbsOrder->failUrl = 'https:/mysite.com/payment/fail';
    $rbsOrder->credit = 1; //for credit payment

    
    $rbsOrder->addCartItem(123, 'Product name', 450.80, 2);
    $rbsOrder->addCartItem('a321', 'Product name II', 145, 2.5);
    ...
    
    $response = $rbs->register($rbsOrder);
    if ($response) {
        //$response['orderId'] - order number on the payment gateway
        //$response['formUrl'] - redirect url
    }
```
Get order status
```php
    $rbs = new \mrssoft\rbs\Rbs(['userName' => '', 'password' => '']);
    $response = $rbsOrder->getOrderStatus('00256ad8-xxxx-4302-xxxx-846d6c0fd6bd');
    //$response['OrderStatus'] - order state code
```
Get order info
```php
    $rbs = new \mrssoft\rbs\Rbs(['userName' => '', 'password' => '']);
    $info = $rbsOrder->getOrderInfo('00256ad8-xxxx-4302-xxxx-846d6c0fd6bd');
```
### Usage as Yii component
```php
    
    // Application config
    ...
    'components' => [
        'rbs' = > [
            'class' => \mrssoft\rbs\Rbs::class,
            'auth' => [ // multiple accounts
                'first' => [
                    'server' => 'https://3dsec.sberbank.ru/sbercredit/',
                    'userName' => 'username1',
                    'password' => '*****',
                ],
                'second' => [
                    'userName' => 'username2',
                    'password' => '*****',
                ]
            ]
        ]
    ]
    ...

    // Selecting account "second"
    $response = Yii::$app->rbs->register($rbsOrder, 'second');
```