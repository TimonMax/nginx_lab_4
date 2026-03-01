<?php
if (!class_exists(\GuzzleHttp\Client::class ?? '')) {
    // возможные пути (чисто на всякий случай)
    $autoloadCandidates = [
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/../../vendor/autoload.php'
    ];
    foreach ($autoloadCandidates as $a) {
        if (is_file($a)) {
            require_once $a;
            break;
        }
    }
}
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class ApiClient {
    private ?Client $client;
    private int $retries;

    public function __construct(array $options = []) {
        $this->retries = isset($options['retries']) ? (int)$options['retries'] : 2;

        unset($options['retries']);

        $guzzleDefaults = [
            'timeout' => $options['timeout'] ?? 15.0,
            'connect_timeout' => $options['connect_timeout'] ?? 5.0,
            'http_errors' => false,
        ];

        $guzzleOptions = $options + $guzzleDefaults;

        if (!class_exists(Client::class)) {
            $this->client = null;
            return;
        }
        $this->client = new Client($guzzleOptions);
    }

    public function request(string $url): array {
        if ($this->client === null) {
            return ['error' => 'Guzzle not available (composer autoload missing)'];
        }
        $attempt = 0;
        $lastError = null;

        while ($attempt <= $this->retries) {
            try {
                $res = $this->client->get($url);
                $body = (string)$res->getBody();
                $decoded = json_decode($body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return ['error' => 'JSON decode error: ' . json_last_error_msg()];
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
            $wait = (int) (2 ** $attempt);
            if ($wait < 1) $wait = 1;
            sleep($wait);
        }

        $msg = $lastError ? $lastError->getMessage() : 'unknown error';
        return ['error' => $msg];
    }
}