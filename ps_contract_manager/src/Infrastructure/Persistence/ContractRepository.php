<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Infrastructure\Persistence;

use Db;
use PrestaShop\Module\PsContractManager\Domain\Contract\Contract;

class ContractRepository
{
    public function findByVinAndCustomer(string $vin, int $customerId): array
    {
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "ps_contract
                WHERE vin = '" . pSQL($vin) . "'
                AND id_customer = " . (int)$customerId . "
                AND status = 'ACTIVE'";

        return Db::getInstance()->executeS($sql);
    }

    public function saveIntervention(array $data): bool
    {
        return Db::getInstance()->insert('ps_intervention', [
            'id_contract' => (int)$data['contract_id'],
            'authorized_by' => pSQL($data['operator']),
            'value' => (float)$data['value'],
            'invoice_number' => pSQL($data['invoice_number'] ?? ''),
            'description' => pSQL($data['description'] ?? ''),
            'date_add' => date('Y-m-d H:i:s'),
        ]);
    }
}
