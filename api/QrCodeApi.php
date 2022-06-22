<?php

namespace mrssoft\rbs\api;

use mrssoft\rbs\RbsOrder;

/**
 * Component for payment through the payment gateway "Sberbank"
 * @see https://developer.sberbank.ru/api/5df4ada7e4b05210f32c030d
 *
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 */
class QrCodeApi extends SberBaseApi
{
    public function registerOrder(RbsOrder $rbsOrder): ?array
    {
        $token = $this->token('https://api.sberbank.ru/order.create');
        if ($token === null) {
            return null;
        }

        $rquid = $this->generateRquid();

        $order_params = [];
        foreach ($rbsOrder->items as $item) {
            $order_params[] = [
                'position_name' => $item['name'],
                'position_count' => $item['quantity']['value'],
                'position_sum' => $item['itemPrice'],
                'position_description' => (string)$item['itemCode'],
            ];
        }

        $params = [
            'rq_uid' => $rquid,
            'rq_tm' => $this->formatDate(date('c')),
            //"rq_tm" => "2005-08-15T15:52:01Z",
            'member_id' => '000001', // (string)$rbsOrder->clientId,
            'order_number' => $rbsOrder->orderNumber,
            'order_create_date' => $this->formatDate($rbsOrder->orderDate),
            //'order_create_date' => '2005-08-15T15:52:01Z',
            'id_qr' => '1000100051',
            'order_sum' => $rbsOrder->amount,
            'currency' => '810',
            'description' => $rbsOrder->description,
            'order_params_type' => $order_params,
        ];

        return $this->request('order/v1/creation', json_encode($params), [
            'Authorization: Bearer ' . $token,
            'content-type: application/json',
            'x-Introspect-RqUID: ' . $rquid,
        ]);
    }
}