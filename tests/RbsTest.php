<?php

namespace mrssoft\rbs\tests;

use mrssoft\rbs\Rbs;
use mrssoft\rbs\RbsOrder;

class RbsTest extends \PHPUnit\Framework\TestCase
{
    private $params;

    private $paymentOrderId;

    public function setUp()
    {
        $this->params = json_decode(file_get_contents(__DIR__ . '\params.json'), true);
        $this->paymentOrderId = '8b64ca2a-d217-7582-8b64-ca2a0000076c';
    }

    public function testRegister()
    {
        $rbs = new Rbs($this->params);

        $rbsOrder = new RbsOrder();
        $rbsOrder->orderNumber = 'NM-12874-' . time();
        $rbsOrder->email = 'test@mail.com';
        $rbsOrder->description = 'Test';
        $rbsOrder->returnUrl = 'https:/mysite.com/payment/success';
        $rbsOrder->failUrl = 'https:/mysite.com/payment/fail';

        $rbsOrder->addCartItem(123, 'Product name', 450.05, 2);
        $rbsOrder->addCartItem('a321', 'Product name II', 145, 2.5);

        $response = $rbs->register($rbsOrder);
        $this->paymentOrderId = $response['orderId'];

        $this->assertNotNull($response);
        $this->assertArrayHasKey('orderId', $response);
        $this->assertArrayHasKey('formUrl', $response);
    }

    public function testGetStatus()
    {
        if ($this->paymentOrderId) {
            $rbs = new Rbs($this->params);
            $response = $rbs->getOrderStatus($this->paymentOrderId);
            $this->assertNotNull($response);
            $this->assertArrayHasKey('OrderStatus', $response);
        }
    }

    public function testGetInfo()
    {
        if ($this->paymentOrderId) {
            $rbs = new Rbs($this->params);
            $info = $rbs->getOrderInfo($this->paymentOrderId);
            $this->assertNotNull($info);
            $this->assertNotEmpty($info);
        }
    }

    public function testAccount()
    {
        if ($this->paymentOrderId) {
            $rbs = new Rbs($this->params);
            $response = $rbs->getOrderStatus($this->paymentOrderId, 'first');
            $this->assertNotNull($response);
        }
    }
}