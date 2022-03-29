<?php

namespace mrssoft\rbs\tests;

use mrssoft\rbs\Rbs;
use mrssoft\rbs\RbsOrder;

class RbsCreditTest extends \PHPUnit\Framework\TestCase
{
    private array $params;

    public function setUp(): void
    {
        $this->params = json_decode(file_get_contents(__DIR__ . '\params-credit.json'), true);
    }

    public function testRegisterCredit()
    {
        $rbs = new Rbs($this->params);
        $rbs->credit = true;

        $rbsOrder = new RbsOrder();
        $rbsOrder->orderNumber = 'NM-12874-' . time();
        $rbsOrder->email = 'test@mail.com';
        $rbsOrder->phone = '89833145368'; //required
        $rbsOrder->description = 'Test credit';
        $rbsOrder->returnUrl = 'https:/mysite.com/payment/success';
        $rbsOrder->failUrl = 'https:/mysite.com/payment/fail';

        $rbsOrder->addCartItem(123, 'Product name', 450.05, 2);
        $rbsOrder->addCartItem('a321', 'Product name II', 145, 2.5);

        $response = $rbs->register($rbsOrder);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('orderId', $response);
        $this->assertArrayHasKey('formUrl', $response);
    }

}