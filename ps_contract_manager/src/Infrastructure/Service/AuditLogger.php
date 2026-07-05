<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Infrastructure\Service;

use Db;

class AuditLogger
{
    public function log(string $operation, int $userId, array $before = [], array $after = []): void
    {
        Db::getInstance()->insert('ps_contract_audit_log', [
            'operation' => pSQL($operation),
            'id_user' => (int)$userId,
            'data_before' => pSQL(json_encode($before)),
            'data_after' => pSQL(json_encode($after)),
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
}
