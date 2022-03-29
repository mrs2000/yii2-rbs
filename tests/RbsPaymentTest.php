<?php

namespace mrssoft\rbs\tests;

use mrssoft\rbs\Rbs;
use mrssoft\rbs\RbsOrder;

class RbsPaymentTest extends \PHPUnit\Framework\TestCase
{
    private array $params;

    private string $paymentId;

    public function setUp(): void
    {
        $this->params = json_decode(file_get_contents(__DIR__ . '\params-payment.json'), true);
        $this->paymentId = '8b64ca2a-d217-7582-8b64-ca2a0000076c';
    }

    public function testRegisterPaymentCard()
    {
        $rbs = new Rbs($this->params);

        $rbsOrder = new RbsOrder();
        $rbsOrder->orderNumber = 'NM-12874-' . time();
        $rbsOrder->email = 'test@mail.com';
        $rbsOrder->description = 'Test';
        $rbsOrder->returnUrl = 'https:/mysite.com/payment/success';
        $rbsOrder->failUrl = 'https:/mysite.com/payment/fail';

        $rbsOrder->addCartItem(123, 'Product name', 450.05, 2);
        $rbsOrder->addCartItem('a321', 'SELECT FROM ORDER WHERE AND Product LIKE name II', 145, 2.5);

        $response = $rbs->register($rbsOrder);
        $this->paymentId = $response['orderId'];

        $this->assertNotNull($response);
        $this->assertArrayHasKey('orderId', $response);
        $this->assertArrayHasKey('formUrl', $response);
    }

    public function testGetPaymentStatus()
    {
        if ($this->paymentId) {
            $rbs = new Rbs($this->params);
            $response = $rbs->getOrderStatus($this->paymentId);
            $this->assertNotNull($response);
            $this->assertArrayHasKey('OrderStatus', $response);
        }
    }

    public function testGetPaymentInfo()
    {
        if ($this->paymentId) {
            $rbs = new Rbs($this->params);
            $info = $rbs->getOrderInfo($this->paymentId);
            $this->assertNotNull($info);
            $this->assertNotEmpty($info);
        }
    }

    public function testPaymentAccount()
    {
        if ($this->paymentId) {
            $rbs = new Rbs($this->params);
            $response = $rbs->getOrderStatus($this->paymentId, 'first');
            $this->assertNotNull($response);
        }
    }

}