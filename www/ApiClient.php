<?php
require_once __DIR__ . '/../vendor/autoload.php';
use GuzzleHttp\Client;

class ApiClient {
    private Client $client;

    public function __construct(array $options = []) {
        $this->client = new Client($options + ['timeout' => 5.0]);
    }

    public function request(string $url): array {
        try {
            $res = $this->client->get($url);
            $body = (string) $res->getBody();
            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'JSON decode error: ' . json_last_error_msg()];
            }
            return $decoded;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}