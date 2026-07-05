<?php
declare(strict_types=1);

class AjaxHandler {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function searchContracts() {
        $vin = $_GET['vin'] ?? '';
        $customerId = $_GET['customer_id'] ?? 0;

        if (strlen($vin) !== 17) {
            $this->jsonError('VIN non valido (deve essere di 17 caratteri).');
        }

        $url = $this->config->get('ps_api_url') . '/contracts?vin=' . urlencode($vin) . '&customer_id=' . (int)$customerId;
        $res = $this->apiCall($url);

        $this->jsonResponse($res);
    }

    public function authorizeIntervention() {
        $data = [
            'contract_id' => $_POST['contract_id'],
            'amount' => $_POST['amount'],
            'operator' => $GLOBALS['thisstaff']->getName(),
            'description' => $_POST['description'] ?? '',
            'ticket_id' => $_POST['ticket_id'],
            'invoice_number' => $_POST['invoice_number'] ?? '',
            'invoice_date' => $_POST['invoice_date'] ?? null,
        ];

        $url = $this->config->get('ps_api_url') . '/interventions/authorize';
        $res = $this->apiCall($url, 'POST', $data);

        $this->jsonResponse($res);
    }

    private function apiCall($url, $method = 'GET', $data = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->config->get('ps_api_key'),
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            return ['success' => false, 'error' => 'API Error ' . $httpCode];
        }

        return json_decode($response, true);
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }

    private function jsonError($msg) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        die();
    }
}
