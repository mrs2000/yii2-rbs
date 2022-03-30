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
    /** Buyer's e-mail */
    public string|null $email = null;

    /** Buyer's phone number */
    public string|null $phone = null;

    /** Order number */
    public string $orderNumber = '';

    /** Order date */
    public string $orderDate = '';

    public string $returnUrl = '';

    public string $failUrl = '';

    /** Order description */
    public string $description = '';

    public array $items = [];

    public float $amount = 0;

    /**
     * Add item to cart
     * @param mixed $itemCode - product code
     * @param string $name - product name
     * @param float $price
     * @param float $qty
     * @param string $unit
     */
    public function addCartItem(mixed $itemCode, string $name, float $price, float $qty = 1, string $unit = 'шт'): void
    {
        $price = round($price * 100);
        $amount = round($price * $qty);

        $this->items[] = [
            'positionId' => count($this->items) + 1,
            'name' => $this->prepareItemName($name),
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

    private function prepareItemName(string $name): string
    {
        $name = str_ireplace([' LIKE ', 'SELECT '], ' ', $name);
        return mb_substr($name, 0, 100);
    }

    public function _generate(): array
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