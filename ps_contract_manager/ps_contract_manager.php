<?php
/**
 * @author Jules
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    die();
}

require_once _PS_MODULE_DIR_ . 'ps_contract_manager/src/Infrastructure/Service/OsTicketSyncService.php';

use PrestaShop\Module\PsContractManager\Infrastructure\Service\OsTicketSyncService;

class PsContractManager extends Module
{
    public function __construct()
    {
        $this->name = 'ps_contract_manager';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Jules';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Contract Manager');
        $this->description = $this->l('Manage customer contracts and interventions.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionCustomerAccountAdd')
            && $this->registerHook('actionObjectCustomerUpdateAfter')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->createTables();
    }

    private function createTables()
    {
        $sqls = [
                        "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ps_contract_audit_log` (\n                `id_audit` INT AUTO_INCREMENT PRIMARY KEY,\n                `operation` VARCHAR(255),\n                `id_user` INT,\n                `data_before` TEXT,\n                `data_after` TEXT,\n                `date_add` DATETIME\n            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",\n            "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ps_contract` (
                `id_contract` INT AUTO_INCREMENT PRIMARY KEY,
                `id_order` INT NOT NULL,
                `id_customer` INT NOT NULL,
                `vin` VARCHAR(17) NOT NULL,
                `initial_value` DECIMAL(20, 6) NOT NULL,
                `status` VARCHAR(20) DEFAULT 'ACTIVE',
                `date_add` DATETIME NOT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ps_intervention` (
                `id_intervention` INT AUTO_INCREMENT PRIMARY KEY,
                `id_contract` INT NOT NULL,
                `id_ost_ticket` INT,
                `authorized_by` VARCHAR(255),
                `value` DECIMAL(20, 6) NOT NULL,
                `numero_fattura` VARCHAR(255),
                `invoice_date` DATE,
                `description` TEXT,
                `status` VARCHAR(20) DEFAULT 'AUTHORIZED',
                `date_add` DATETIME NOT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;"
        ];

        foreach ($sqls as $sql) {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        }
        return true;
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $sync = new OsTicketSyncService();
        $sync->syncCustomer($params['new_customer']);
    }

    public function hookActionObjectCustomerUpdateAfter($params)
    {
        if (isset($params['object']) && $params['object'] instanceof Customer) {
            $sync = new OsTicketSyncService();
            $sync->syncCustomer($params['object']);
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        // To be implemented
    }

    public function getContent()
    {
        return "Contract Manager Admin UI";
    }
}
