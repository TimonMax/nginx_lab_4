<?php
$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
} else {
}

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class ApiClient {
    private ?Client $client;
    private int $retries;
    private float $defaultTimeout;
    private float $defaultConnectTimeout;

    public function __construct(array $options = []) {
        $this->retries = isset($options['retries']) ? (int)$options['retries'] : 2;
        $this->defaultTimeout = isset($options['timeout']) ? (float)$options['timeout'] : 15.0;
        $this->defaultConnectTimeout = isset($options['connect_timeout']) ? (float)$options['connect_timeout'] : 5.0;

        if (!class_exists(Client::class)) {
            $this->client = null;
            return;
        }

        $guzzleOptions = [
            'timeout' => $this->defaultTimeout,
            'connect_timeout' => $this->defaultConnectTimeout,
            'http_errors' => false,
            'allow_redirects' => true,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'lab4-client/1.0 (+https://example.local)'
            ],
        ];

        foreach (['timeout','connect_timeout','http_errors','verify','headers','allow_redirects'] as $k) {
            if (array_key_exists($k, $options)) {
                $guzzleOptions[$k] = $options[$k];
            }
        }

        $this->client = new Client($guzzleOptions);
    }

    public function request(string $url, array $opts = []): array {
        if ($this->client === null) {
            return ['error' => 'Guzzle not available (composer autoload missing). Path used: ' . (__DIR__ . '/../vendor/autoload.php')];
        }

        $attempt = 0;
        $lastError = null;

        $requestOptions = [];
        if (isset($opts['timeout'])) $requestOptions['timeout'] = $opts['timeout'];
        if (isset($opts['connect_timeout'])) $requestOptions['connect_timeout'] = $opts['connect_timeout'];
        if (isset($opts['verify'])) $requestOptions['verify'] = $opts['verify'];

        while ($attempt <= $this->retries) {
            try {
                $res = $this->client->get($url, $requestOptions);
                $body = (string)$res->getBody();
                $decoded = json_decode($body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return ['error' => 'JSON decode error: ' . json_last_error_msg(), 'raw' => $body];
                }
                return $decoded;
            } catch (ConnectException $e) {
                $lastError = $e;
            } catch (RequestException $e) {
                $lastError = $e;
            } catch (\Throwable $e) {
                $lastError = $e;
                break;
            }

            $attempt++;
            sleep(min(4, 1 << $attempt));
        }

        $msg = $lastError ? $lastError->getMessage() : 'unknown error';
        return ['error' => $msg];
    }
}