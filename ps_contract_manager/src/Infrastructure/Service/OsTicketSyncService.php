<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Infrastructure\Service;

use Customer;
use Configuration;
use PrestaShop\Module\PsContractManager\Infrastructure\Service\AuditLogger;

class OsTicketSyncService
{
    private string $apiUrl;
    private string $apiKey;
    private AuditLogger $logger;

    public function __construct()
    {
        $this->apiUrl = (string)Configuration::get('OSTICKET_API_URL');
        $this->apiKey = (string)Configuration::get('OSTICKET_API_KEY');
        $this->logger = new AuditLogger();
    }

    public function syncCustomer(Customer $customer): bool
    {
        $data = [
            'email' => $customer->email,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'active' => $customer->active,
            'ps_id' => $customer->id,
            'passwd_hash' => $customer->passwd,
        ];

        return $this->callApi('/users/sync', $data);
    }

    private function callApi(string $endpoint, array $data): bool
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            $this->logger.log('USER_SYNC_SKIPPED', (int)($data['ps_id'] ?? 0), [], ['reason' => 'API config missing']);
            return false;
        }

        $url = rtrim($this->apiUrl, '/') . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $error) {
            $this->logger->log('USER_SYNC_FAILURE', (int)($data['ps_id'] ?? 0), [], [
                'error' => $error,
                'http_code' => $httpCode,
                'email' => $data['email']
            ]);
            return false;
        }

        return true;
    }
}
