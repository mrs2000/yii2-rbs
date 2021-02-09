<?php

namespace mrssoft\rbs;

use yii\base\BaseObject;

/**
 * Payment order
 *
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 * @see https://securepayments.sberbank.ru/wiki/doku.php/start
 */
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

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var float
     */
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
        $amount = round($price * $qty);

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
            'returnUrl' => $this->returnUrl,
            'failUrl' => $this->failUrl,
            'description' => $this->description,
            'currency' => '643',
            'language' => 'ru',
        ];

        if ($this->email) {
            $result['orderBundle']['customerDetails']['email'] = $this->email;
            $result['jsonParams']['email'] = $this->email;
        }

        if ($this->phone) {
            $result['orderBundle']['customerDetails']['phone'] = $this->phone;
            $result['jsonParams']['phone'] = $this->phone;
        }

        $result['orderBundle']['cartItems'] = [
            'items' => $this->items
        ];

        return $result;
    }
}