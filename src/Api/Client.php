<?php
/**
 * API-Client
 * 
 * Kommuniziert mit dem zentralen Server
 * Wird auf Raspberry Pi Clients verwendet
 */

namespace App\Api;

use App\Core\Config;

class Client
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeout = 30;
    private array $lastError = [];

    public function __construct()
    {
        // Stelle sicher, dass dies ein Client ist
        if (Config::isServer()) {
            throw new \RuntimeException('API-Client kann nicht auf dem Server verwendet werden!');
        }

        $this->apiUrl = Config::getApiUrl();
        $this->apiKey = Config::getApiKey();

        if (empty($this->apiUrl)) {
            throw new \RuntimeException('API_URL nicht konfiguriert!');
        }
    }

    /**
     * API-Request senden
     * 
     * @param string $action Die auszuführende Action
     * @param array $data POST-Daten
     * @param string $method HTTP-Methode (GET oder POST)
     * @return array|null Response-Daten oder null bei Fehler
     */
    public function request(string $action, array $data = [], string $method = 'POST'): ?array
    {
        $this->lastError = [];

        // Füge Action zu Daten hinzu
        $data['action'] = $action;

        try {
            if ($method === 'GET') {
                $url = $this->apiUrl . '?' . http_build_query($data);
                $response = $this->sendGetRequest($url);
            } else {
                $response = $this->sendPostRequest($this->apiUrl, $data);
            }

            $result = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Ungültige JSON-Response: ' . json_last_error_msg());
            }

            if (!isset($result['success'])) {
                throw new \RuntimeException('Ungültiges Response-Format');
            }

            if ($result['success'] === false) {
                $this->lastError = [
                    'message' => $result['error'] ?? 'Unbekannter Fehler',
                    'code' => $result['code'] ?? 0
                ];
                return null;
            }

            return $result['data'] ?? [];

        } catch (\Exception $e) {
            $this->lastError = [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            return null;
        }
    }

    /**
     * GET-Request mit cURL
     */
    private function sendGetRequest(string $url): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('cURL Error: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('HTTP Error ' . $httpCode . ': ' . $response);
        }

        return $response;
    }

    /**
     * POST-Request mit cURL
     */
    private function sendPostRequest(string $url, array $data): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('cURL Error: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('HTTP Error ' . $httpCode . ': ' . $response);
        }

        return $response;
    }

    /**
     * Letzten Fehler abrufen
     */
    public function getLastError(): array
    {
        return $this->lastError;
    }

    /**
     * Timeout setzen
     */
    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    // ==========================================
    // CONVENIENCE-METHODEN FÜR COMMON ACTIONS
    // ==========================================

    /**
     * Gemeinsame Transaktionen abrufen
     */
    public function getSharedTransactions(int $limit = 100, int $offset = 0): ?array
    {
        return $this->request('getSharedTransactions', [
            'limit' => $limit,
            'offset' => $offset
        ], 'GET');
    }

    /**
     * Gemeinsame Transaktion hinzufügen
     */
    public function addSharedTransaction(array $transaction): ?array
    {
        return $this->request('addSharedTransaction', $transaction);
    }

    /**
     * Gemeinsame Konten abrufen
     */
    public function getSharedAccounts(): ?array
    {
        return $this->request('getSharedAccounts', [], 'GET');
    }

    /**
     * Gemeinsames Konto erstellen
     */
    public function createSharedAccount(string $name, string $type = 'general'): ?array
    {
        return $this->request('createSharedAccount', [
            'name' => $name,
            'type' => $type
        ]);
    }

    /**
     * Dashboard-Statistiken abrufen
     */
    public function getSharedStats(): ?array
    {
        return $this->request('getSharedStats', [], 'GET');
    }

    /**
     * Transaktion löschen
     */
    public function deleteTransaction(int $id): ?array
    {
        return $this->request('deleteTransaction', ['id' => $id]);
    }

    /**
     * Health-Check (Verbindung testen)
     */
    public function healthCheck(): bool
    {
        $result = $this->request('health', [], 'GET');
        return $result !== null && isset($result['status']) && $result['status'] === 'ok';
    }
}
