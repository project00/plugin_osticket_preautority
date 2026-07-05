# Software Architecture Design: PrestaShop-osTicket Integration

## 1. Analisi del Dominio
Il dominio riguarda la gestione del post-vendita per contratti di assistenza tecnica o manutenzione acquistati tramite E-commerce (PrestaShop) e gestiti tramite Help Desk (osTicket).

### Entità Core:
- **Contratto**: Rappresenta l'acquisto di un pacchetto di interventi. Legato a un Prodotto/Ordine PrestaShop e a un VIN (Vehicle Identification Number).
- **Intervento**: Una singola operazione autorizzata a valere su un contratto.
- **Valore Residuo**: Lo stato finanziario del contratto, calcolato dinamicamente.

## 2. Architettura Generale
Si adotta una **Clean Architecture** combinata con **DDD** per separare le preoccupazioni e garantire testabilità e manutenibilità.

- **Domain Layer**: Contiene la logica di business pura (Entità, Value Objects, Domain Services).
- **Application Layer**: Gestisce i casi d'uso (es. "Autorizza Intervento").
- **Infrastructure Layer**: Implementazioni tecniche (Persistence, API Clients, Mailers).
- **Presentation Layer**: Controllers (REST API, Admin UI).

## 3. Bounded Contexts
- **Sales Context (PrestaShop)**: Gestione anagrafiche clienti, vendita contratti, calcolo valore residuo, reporting.
- **Support Context (osTicket)**: Apertura ticket, verifica validità VIN, richiesta autorizzazione interventi.

## 4. Modello Dati
### PrestaShop (Master)
- `ps_customer`: (Esistente) Master identità.
- `ps_product`: (Esistente) Contratto come prodotto virtuale.
- `ps_order`: (Esistente) Transazione di acquisto.
- `ps_contract`:
    - `id_contract` (PK)
    - `id_order` (FK)
    - `id_customer` (FK)
    - `vin` (VARCHAR 17)
    - `initial_value` (DECIMAL)
    - `status` (ACTIVE, CONSUMED)
- `ps_intervention`:
    - `id_intervention` (PK)
    - `id_contract` (FK)
    - `authorized_by` (VARCHAR)
    - `value` (DECIMAL)
    - `invoice_number` (VARCHAR)
    - `invoice_date` (DATE)
    - `description` (TEXT)
    - `status` (AUTHORIZED, DENIED)

## 5. Diagramma delle Relazioni (Concettuale)
`Customer (1) -- (*) Order (1) -- (1) Contract (1) -- (*) Intervention`

## 6. Workflow Applicativo
1. **Acquisto**: Cliente compra Contratto su PrestaShop -> Hook crea record `ps_contract`.
2. **Supporto**: Cliente apre Ticket su osTicket indicando il VIN.
3. **Validazione**: osTicket interroga PrestaShop via API per ottenere contratti attivi per quel VIN/Cliente.
4. **Autorizzazione**: Operatore inserisce dati intervento -> Richiesta API a PrestaShop.
5. **Transazione**: PrestaShop verifica credito -> Sottrae valore -> Crea `ps_intervention` -> Risponde con OK/KO.
6. **Chiusura**: Se residuo == 0, PrestaShop imposta stato `CONSUMED`.

## 7. API REST (OpenAPI Spec - Estratto)
- `GET /api/contracts?vin={vin}&customer_id={id}`: Lista contratti attivi.
- `POST /api/interventions/authorize`: Esegue l'autorizzazione.
    - Payload: `{ contract_id, value, operator, description, ... }`

## 8. Hook
### PrestaShop
- `actionCustomerAccountAdd`: Sincronizza nuovo cliente con osTicket.
- `actionObjectCustomerUpdateAfter`: Propaga modifiche anagrafiche.
- `actionOrderStatusPostUpdate`: Crea contratto quando l'ordine è pagato.

### osTicket
- `ticket.create.client`: Trigger per validazione VIN iniziale.
- `staff.ticket.view`: Iniezione UI per gestione autorizzazioni.

## 9. Sicurezza
- **Autenticazione**: API Key (X-API-Key) + Validazione IP.
- **Integrità**: Transazioni SQL atomiche su PrestaShop per scalare il credito.
- **SSO**: Sincronizzazione sicura degli hash password o integrazione OAuth2.

## 10. Gestione Permessi
- PrestaShop Admin: Accesso completo a reporting ed esportazioni.
- osTicket Staff: Solo visualizzazione contratti e richiesta autorizzazione.

## 11. Roadmap di Sviluppo
1. Core Domain Logic (PrestaShop).
2. API Layer & Security.
3. osTicket UI Integration.
4. Reporting & Export.
5. User Sync.

## 12. Piano di Test
- **Unit Test**: Logica di calcolo valore residuo.
- **Integration Test**: Comunicazione API tra i due sistemi.
- **UAT**: Workflow completo dall'acquisto alla fruizione totale del contratto.
