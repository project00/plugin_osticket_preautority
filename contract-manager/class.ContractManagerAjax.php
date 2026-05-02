<?php

class ContractManagerAjax {
    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    private function getClient() {
        include_once('class.ContractManagerAPI.php');
        return new ContractManagerAPI($this->plugin->getConfig());
    }

    public function testConnection() {
        $client = $this->getClient();
        $res = $client->testConnection();
        $this->jsonResponse($res);
    }

    public function verifyVin() {
        $vin = $_GET['vin'] ?? '';
        $client = $this->getClient();
        $res = $client->searchByVin($vin);
        $this->jsonResponse($res);
    }

    public function verifyPlate() {
        $plate = $_GET['plate'] ?? '';
        $client = $this->getClient();
        $res = $client->searchByPlate($plate);
        $this->jsonResponse($res);
    }

    public function saveVerification() {
        $ticketId = $_POST['ticket_id'] ?? null;
        $credit = $_POST['credit'] ?? null;
        $contracts = $_POST['contracts'] ?? '';

        if ($ticketId) {
            $this->updateTicketFields($ticketId, [
                'credito_residuo' => $credit,
                'id_contratto' => $contracts
            ], 'Verification Update');
            $this->jsonResponse(['success' => true]);
        }
        $this->jsonError('Missing Ticket ID');
    }

    public function authorize() {
        $ticketId = $_POST['ticket_id'] ?? null;
        $cost = $_POST['cost'] ?? 0;
        $invoiceNumber = $_POST['invoice_number'] ?? '';

        $authCode = substr(str_shuffle(str_repeat('0123456789', 12)), 0, 12);
        $client = $this->getClient();
        $res = $client->imputeCost($authCode, $cost, $invoiceNumber, $ticketId);

        if ($res['success']) {
            $this->updateTicketFields($ticketId, [
                'autorizzazione' => $authCode,
                'costo' => $cost,
                'numero_fattura' => $invoiceNumber
            ], 'Authorized');
        }
        $this->jsonResponse($res);
    }

    public function deny() {
        $ticketId = $_POST['ticket_id'] ?? null;
        $this->updateTicketFields($ticketId, ['autorizzazione' => 'NEGATA'], 'Denied');
        $this->jsonResponse(['success' => true]);
    }

    private function updateTicketFields($ticketId, $fields, $status) {
        $ticket = Ticket::lookup($ticketId);
        if (!$ticket) return;

        foreach ($ticket->getDynamicForms() as $form) {
            $updated = false;
            foreach ($fields as $name => $val) {
                if ($f = $form->getField($name)) {
                    $form->setAnswer($name, $val);
                    $updated = true;
                }
            }
            if ($updated) $form->save();
        }

        $logData = json_encode($fields, JSON_PRETTY_PRINT);
        $ticket->logActivity('Contract Manager: ' . $status, $logData);
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function jsonError($msg) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }
}
?>
