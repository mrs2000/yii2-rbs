<?php

namespace mrssoft\rbs\api;

use Yii;
use yii\base\Component;

/**
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 */
class SberBaseApi extends Component
{
    /**
     * @var string
     */
    public $server = 'https://dev.api.sberbank.ru/ru/prod/';

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var string
     */
    public $clientSecret;

    protected function generateRquid(): string
    {
        return md5(uniqid(time(), true));
    }

    protected function formatDate(string $date): string
    {
        return date('Y-m-d\TH:i:s\Z', strtotime($date));
    }

    protected function request(string $command, string $body, array $headers): ?array
    {
        $headers[] = 'accept: application/json';
        $headers[] = 'x-ibm-client-id: ' . $this->clientId;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->server . $command,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        var_dump($response);
        die();

        return $code == 200 && $response ? json_decode($response, true) : null;
    }

    public function token(string $scope): ?string
    {
        $cacheKey = 'sber-auth-tokens';
        $tokens = Yii::$app->cache->get($cacheKey);
        if ($tokens === false || array_key_exists($scope, $tokens) === false || $tokens[$scope]['expires_in'] < time()) {
            $response = $this->requestToken($scope);
            $tokens[$scope] = [
                'access_token' => $response['access_token'],
                'expires_in' => time() + $response['expires_in'],
            ];
            Yii::$app->cache->set($cacheKey, $tokens);
        }
        return $tokens[$scope]['access_token'] ?? null;
    }

    public function requestToken(string $scope): ?array
    {
        $post = http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => $scope
        ]);
        return $this->request('tokens/v2/oauth', $post, [
            'Authorization: Basic ' . base64_encode("$this->clientId:$this->clientSecret"),
            'content-type: application/x-www-form-urlencoded',
            'rquid: ' . $this->generateRquid(),
        ]);
    }
}