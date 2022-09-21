<?php

namespace mrssoft\rbs;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidValueException;

/**
 * Component for payment through the payment gateway "Sberbank"
 * @see https://securepayments.sberbank.ru/wiki/doku.php/start
 *
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 */
class Rbs extends Component
{
    public const TYPE_CREDIT_DEFAULT = 'CREDIT';
    public const TYPE_CREDIT_INSTALLMENT = 'INSTALLMENT';

    public string $userName = '';

    public string|array $password = '';

    public bool $credit = false;
    public string $productType = self::TYPE_CREDIT_DEFAULT;

    public string $server = 'https://securepayments.sberbank.ru/payment/rest/';

    /**
     * @var array
     */
    public array $auth = [];

    /**
     * Register a new order
     * @param \mrssoft\rbs\RbsOrder $rbsOrder
     * @param null|string $account
     * @return array|null
     * @throws \yii\base\Exception
     */
    public function register(RbsOrder $rbsOrder, ?string $account = null): ?array
    {
        $data = $rbsOrder->_generate();

        if ($this->credit) {
            $data['orderBundle']['installments'] = [
                'productType' => $this->productType,
                'productID' => '10'
            ];
        }

        return $this->request('register.do', $data, $account);
    }

    /**
     * Get order status
     * @param string $orderId - order number in the payment system
     * @param null|string $account
     * @return array|null
     * @throws \yii\base\Exception
     */
    public function getOrderStatus(string $orderId, ?string $account = null): ?array
    {
        return $this->request('getOrderStatus.do', ['orderId' => $orderId], $account);
    }

    /**
     * Obtain information about the order in the form of an array [key => value]
     * @param string $orderId - order number in the payment system
     * @param null|string $account
     * @return array
     * @throws \yii\base\Exception
     */
    public function getOrderInfo(string $orderId, ?string $account = null): array
    {
        $response = $this->request('getOrderStatusExtended.do', ['orderId' => $orderId], $account);

        $info = [];
        if ($response && empty($response['errorCode'])) {
            if (isset($response['orderStatus'])) {

                $status = 'Нет данных';
                switch ($response['orderStatus']) {
                    case 0:
                        $status = 'Заказ зарегистрирован, но не оплачен';
                        break;
                    case 1:
                        $status = 'Предавторизованная сумма захолдирована';
                        break;
                    case 2:
                        $status = 'Проведена полная авторизация суммы заказа';
                        break;
                    case 3:
                        $status = 'Авторизация отменена';
                        break;
                    case 4:
                        $status = 'По транзакции была проведена операция возврата';
                        break;
                    case 5:
                        $status = 'Инициирована авторизация через ACS банка-эмитента';
                        break;
                    case 6:
                        $status = 'Авторизация отклонена';
                        break;
                }
                $info['Статус'] = $status;

                if (empty($response['actionCodeDescription']) === false) {
                    $info['Последнее действие'] = $response['actionCodeDescription'];
                }

                if (isset($response['cardAuthInfo'])) {
                    $info['Держатель карты'] = $response['cardAuthInfo']['cardholderName'] ?? '-';
                    $info['Номер карты'] = $response['cardAuthInfo']['pan'] ?? '-';
                    $expiration = $response['cardAuthInfo']['expiration'] ?? null;
                    if ($expiration) {
                        $expiration = substr($expiration, 0, 4) . ' / ' . substr($expiration, 4);
                        $info['Номер карты'] .= ' ' . $expiration;
                    }
                }

                if (isset($response['paymentAmountInfo'])) {
                    $info['Сумма предавторизации'] = $response['paymentAmountInfo']['approvedAmount'] / 100;
                    $info['Сумма подтверждения'] = $response['paymentAmountInfo']['depositedAmount'] / 100;
                    $info['Сумма возврата'] = $response['paymentAmountInfo']['refundedAmount'] / 100;
                }
            } else {
                $info['Статус'] = 'Заказ не найден';
            }
        }

        return $info;
    }

    /**
     * Sending a request to the payment gateway server
     * @param string $command
     * @param array $data
     * @param null|string $account
     * @return array|null
     * @throws \yii\base\Exception
     */
    private function request(string $command, array $data, ?string $account): ?array
    {
        $this->arrayToJson($data);
        $this->fillAuth($data, $account);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->server . $command);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        if ($response === false) {
            Yii::error('Error RBS. Connection error with payment gateway.');
        } else {
            $response = json_decode($response, true);
            if (!empty($response['errorCode'])) {
                Yii::error('Error RBS [' . $response['errorCode'] . '] ' . $response['errorMessage']);
            } else {
                return $response;
            }
        }

        return null;
    }

    private function arrayToJson(array &$data): void
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    private function fillAuth(array &$data, ?string $account): void
    {
        if ($account) {
            if (isset($this->auth[$account]) === false) {
                throw new InvalidValueException("Account [$account] not found.");
            }
            $data['userName'] = $this->auth[$account]['userName'];
            $data['password'] = $this->auth[$account]['password'];
            if (!empty($this->auth[$account]['server'])) {
                $this->server = $this->auth[$account]['server'];
            }
        } else {
            $data['userName'] = $this->userName;
            $data['password'] = $this->password;
        }
        if (empty($data['userName'])) {
            throw new InvalidValueException('User name is empty.');
        }
        if (empty($data['password'])) {
            throw new InvalidValueException('Password is empty.');
        }
    }
}
