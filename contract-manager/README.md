# Contract and Authorization Manager Plugin for osTicket

This plugin integrates osTicket with an external API to manage vehicle contracts and authorizations.

## Features
- **VIN/Targa Verification**: Buttons next to VIN and Targa fields to search for contracts and check residual credit.
- **Ticket Authorization**: "Autorizza" and "Nega" buttons on the ticket view for staff.
- **External API Integration**: Configurable endpoints for contract search and cost imputation.
- **Custom Fields**: Automatically creates required fields (VIN, Targa, Autorizzazione, etc.).

## Installation
1. Copy the `contract-manager` directory to your osTicket `include/plugins/` folder.
2. Log in to the osTicket Admin Panel.
3. Go to **Manage** -> **Plugins**.
4. Click **Add New Plugin**.
5. Find "Contract and Authorization Manager" and click **Install**.
6. Click on the plugin name to configure settings.
7. Enable the plugin.

## Configuration
In the plugin settings, provide:
- **Base API URL**: e.g., `https://api.yourcompany.com/v1`
- **API Key**: Your Authorization token.
- **Endpoints**: Specific paths for VIN search, Plate search, and Cost imputation.

## API Examples

### 1. Search by VIN/Plate
**Request:** `GET /search/vin?vin=ABC123456789`
**Response:**
```json
{
  "success": true,
  "data": {
    "contracts": [
      {"id": "CON-100", "status": "Active"},
      {"id": "CON-101", "status": "Expired"}
    ],
    "residual_credit": 500.00
  }
}
```

### 2. Impute Cost
**Request:** `POST /impute/cost`
**Payload:**
```json
{
  "autorizzazione": "123456789012",
  "costo": 150.50,
  "numero_fattura": "INV-2023-001",
  "ticket_id": 1234,
  "data": "2023-10-27 10:00:00"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Cost imputed successfully"
}
```

## Security & Best Practices
- The plugin uses osTicket's `Signal` system to avoid core modifications.
- API calls are logged in osTicket's system logs for auditing.
- Frontend interactions are protected by osTicket's staff session and CSRF mechanisms (via AJAX dispatcher).
