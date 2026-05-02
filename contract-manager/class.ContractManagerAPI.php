<?php

class ContractManagerAPI {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    private function call($endpoint, $method = 'GET', $data = null) {
        $baseUrl = $this->config->get('api_url');
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Respect the passed $method unless it's a generic GET that should follow config
        $actualMethod = strtoupper($method);
        if ($actualMethod === 'GET' && $this->config->get('api_method')) {
            $actualMethod = strtoupper($this->config->get('api_method'));
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $actualMethod);

        $headers = ['Content-Type: application/json'];
        if ($authHeader = $this->config->get('api_key')) {
            $headers[] = 'Authorization: ' . $authHeader;
        }

        if ($customHeaders = $this->config->get('custom_headers')) {
            foreach (explode("\n", $customHeaders) as $line) {
                if ($line = trim($line)) $headers[] = $line;
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "Connection Error: $error"];
        }

        $decoded = json_decode($response, true);
        return [
            'success' => $httpCode < 400,
            'status' => $httpCode,
            'data' => $decoded,
            'raw' => $response
        ];
    }

    public function searchByVin($vin) {
        return $this->call($this->config->get('search_vin_endpoint') . '?vin=' . urlencode($vin));
    }

    public function searchByPlate($plate) {
        return $this->call($this->config->get('search_plate_endpoint') . '?plate=' . urlencode($plate));
    }

    public function imputeCost($authCode, $cost, $invoiceNumber, $ticketId) {
        $data = [
            'autorizzazione' => $authCode,
            'costo' => $cost,
            'numero_fattura' => $invoiceNumber,
            'ticket_id' => $ticketId,
            'data' => date('Y-m-d H:i:s')
        ];
        return $this->call($this->config->get('impute_cost_endpoint'), 'POST', $data);
    }

    public function testConnection() {
        // Just call the base URL or a simple endpoint
        return $this->call('');
    }
}
?>
