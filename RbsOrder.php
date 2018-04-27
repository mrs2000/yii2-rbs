<?php

namespace mrssoft\rbs;

use yii\base\BaseObject;

class RbsOrder extends BaseObject
{
    /**
     * @var string - buyer's e-mail
     */
    public $email;

    /**
     * @var string - buyer's phone number
     */
    public $phone;

    /**
     * @var string - order number
     */
    public $orderNumber;

    /**
     * @var string|array
     */
    public $returnUrl;

    /**
     * @var string|array
     */
    public $failUrl;

    /**
     * @var string - order description
     */
    public $description;

    private $items;

    private $amount;

    /**
     * Add item to cart
     * @param mixed $itemCode - product code
     * @param string $name - product name
     * @param float $price
     * @param float $qty
     * @param string $unit
     */
    public function addCartItem($itemCode, string $name, float $price, float $qty = 1, ?string $unit = 'шт'): void
    {
        $price = round($price * 100);
        $amount = $price * $qty;

        $this->items[] = [
            'positionId' => count($this->items) + 1,
            'name' => mb_substr($name, 0, 100),
            'quantity' => [
                'value' => $qty,
                'measure' => $unit
            ],
            'itemPrice' => $price,
            'itemAmount' => $amount,
            'itemCode' => $itemCode,
        ];

        $this->amount += $amount;
    }

    public function _generate()
    {
        $result = [
            'orderNumber' => $this->orderNumber,
            'amount' => $this->amount,
            'returnUrl' =>$this->returnUrl,
            'failUrl' => $this->failUrl,
            'description' => $this->description
        ];

        $customerDetails = [];
        if ($this->email) {
            $customerDetails['email'] = $this->email;
            $result['jsonParams']['email'] = $this->email;
        }
        if ($this->phone) {
            $customerDetails['phone'] = $this->phone;
        }

        $result['orderBundle'] = [
            'customerDetails' => $customerDetails,
            'cartItems' => [
                'items' => $this->items
            ]
        ];

        return $result;
    }
}