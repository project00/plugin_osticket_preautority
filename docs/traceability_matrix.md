# Traceability Matrix: PrestaShop-osTicket Integration

| Requirement | File Path | Class | Method | Status | Implementation Detail |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **SSoT (PrestaShop)** | `ps_contract_manager.php` | `PsContractManager` | `createTables` | ✅ | Tables created in PrestaShop DB. |
| **No balance storage in osTicket** | `ost_contract_integration/class.AjaxHandler.php` | `AjaxHandler` | `searchContracts` | ✅ | Fetches live balance from PS API. |
| **REST API Communication** | `ps_contract_manager/src/Controller/Api/ContractApiController.php` | `ContractApiController` | `getContracts`, `authorizeIntervention` | ✅ | Documented endpoints for inter-plugin talk. |
| **DDD / Clean Architecture** | `ps_contract_manager/src/Domain/Contract/Contract.php` | `Contract` | N/A | ✅ | Clear separation of Domain and Infrastructure. |
| **Repository Pattern** | `ps_contract_manager/src/Infrastructure/Persistence/ContractRepository.php` | `ContractRepository` | All | ✅ | Encapsulates all DB access. |
| **User Sync (Create)** | `ps_contract_manager.php` | `PsContractManager` | `hookActionCustomerAccountAdd` | ✅ | Registered PrestaShop hook. |
| **User Sync (Update)** | `ps_contract_manager.php` | `PsContractManager` | `hookActionObjectCustomerUpdateAfter` | ✅ | Registered PrestaShop hook. |
| **User Sync Logic** | `ps_contract_manager/src/Infrastructure/Service/OsTicketSyncService.php` | `OsTicketSyncService` | `syncCustomer` | ✅ | Calls osTicket REST API. |
| **User Sync Handler (osTicket)** | `ost_contract_integration/class.UserSyncHandler.php` | `UserSyncHandler` | `handleSync` | ✅ | Processes and saves/updates users. |
| **Contract Creation** | `ps_contract_manager.php` | `PsContractManager` | `hookActionOrderStatusPostUpdate` | ✅ | Creates contract on paid order. |
| **VIN Field & Validation** | `ost_contract_integration/js/contract.js` | N/A | Anonymous | ✅ | JS validation (17 chars) and API trigger. |
| **Contract Dropdown** | `ost_contract_integration/js/contract.js` | N/A | `fetchContracts` | ✅ | Dynamic population based on PS response. |
| **Remaining Value Formula** | `ps_contract_manager/src/Infrastructure/Persistence/ContractRepository.php` | `ContractRepository` | `findActiveByVinAndCustomer` | ✅ | SQL: `initial_value - SUM(value)`. |
| **Status "FRUITO" transition** | `ps_contract_manager/src/Infrastructure/Persistence/ContractRepository.php` | `ContractRepository` | `authorizeIntervention` | ✅ | Updates status when residual reaches 0. |
| **Atomic Transactions** | `ps_contract_manager/src/Infrastructure/Persistence/ContractRepository.php` | `ContractRepository` | `authorizeIntervention` | ✅ | `BEGIN`, `COMMIT`, `ROLLBACK`. |
| **Race Condition Locking** | `ps_contract_manager/src/Infrastructure/Persistence/ContractRepository.php` | `ContractRepository` | `findActiveByVinAndCustomer` | ✅ | `FOR UPDATE` on contract row. |
| **Reporting UI (Tree View)** | `ps_contract_manager/views/templates/admin/reporting.twig` | N/A | N/A | ✅ | Accordion tree view implementation. |
| **Reporting Logic** | `ps_contract_manager/src/Controller/Admin/AdminContractController.php` | `AdminContractController` | `indexAction` | ✅ | Fetches nested data via Repository. |
| **CSV Export** | `ps_contract_manager/src/Infrastructure/Service/ExportService.php` | `ExportService` | `exportCsv` | ✅ | Native PHP stream CSV generation. |
| **Audit Logging** | `ps_contract_manager/src/Infrastructure/Service/AuditLogger.php` | `AuditLogger` | `log` | ✅ | Records every sensitive operation. |
| **Unit Testing** | `ps_contract_manager/tests/ContractTest.php` | `ContractTest` | All | ✅ | Domain logic validation. |
