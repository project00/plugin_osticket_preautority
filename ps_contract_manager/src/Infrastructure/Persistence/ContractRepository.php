<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Infrastructure\Persistence;

use Db;
use PrestaShop\Module\PsContractManager\Domain\Contract\ContractStatus;

class ContractRepository
{
    /**
     * Finds active contracts for a customer and VIN using row locking to prevent race conditions.
     */
    public function findActiveByVinAndCustomer(string $vin, int $customerId): array
    {
        $sql = "SELECT c.*,
                (c.initial_value - COALESCE(SUM(i.value), 0)) as residual_value
                FROM " . _DB_PREFIX_ . "ps_contract c
                LEFT JOIN " . _DB_PREFIX_ . "ps_intervention i ON c.id_contract = i.id_contract AND i.status = 'AUTHORIZED'
                WHERE c.vin = '" . pSQL($vin) . "'
                AND c.id_customer = " . (int)$customerId . "
                AND c.status = '" . pSQL(ContractStatus::ACTIVE) . "'
                GROUP BY c.id_contract
                HAVING residual_value > 0
                FOR UPDATE";

        return Db::getInstance()->executeS($sql);
    }

    public function authorizeIntervention(int $contractId, float $amount, array $meta): bool
    {
        $db = Db::getInstance();
        $db->execute('BEGIN');

        try {
            // Re-verify residual value with lock
            $sql = "SELECT (c.initial_value - COALESCE(SUM(i.value), 0)) as residual_value
                    FROM " . _DB_PREFIX_ . "ps_contract c
                    LEFT JOIN " . _DB_PREFIX_ . "ps_intervention i ON c.id_contract = i.id_contract AND i.status = 'AUTHORIZED'
                    WHERE c.id_contract = " . (int)$contractId . "
                    GROUP BY c.id_contract
                    FOR UPDATE";

            $row = $db->getRow($sql);
            if (!$row || $row['residual_value'] < $amount) {
                throw new \Exception('Insufficient funds or contract not found');
            }

            // Insert intervention
            $db->insert('ps_intervention', [
                'id_contract' => (int)$contractId,
                'id_ost_ticket' => (int)$meta['ticket_id'],
                'authorized_by' => pSQL($meta['operator']),
                'value' => (float)$amount,
                'description' => pSQL($meta['description'] ?? ''),
                'date_add' => date('Y-m-d H:i:s'),
            ]);

            $newResidual = $row['residual_value'] - $amount;

            // Check if contract is now consumed
            if ($newResidual <= 0) {
                $db->update('ps_contract', ['status' => ContractStatus::CONSUMED], 'id_contract = ' . (int)$contractId);
            }

            $db->execute('COMMIT');
            return true;
        } catch (\Exception $e) {
            $db->execute('ROLLBACK');
            return false;
        }
    }
}
