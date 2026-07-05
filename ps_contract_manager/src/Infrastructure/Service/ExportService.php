<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Infrastructure\Service;

use PrestaShop\Module\PsContractManager\Infrastructure\Persistence\ContractRepository;

class ExportService
{
    private ContractRepository $repository;

    public function __construct(ContractRepository $repository)
    {
        $this->repository = $repository;
    }

    public function exportCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID Contratto', 'VIN', 'Cliente', 'Valore Iniziale', 'Valore Residuo', 'Stato']);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id_contract'],
                $row['vin'],
                $row['customer_name'] ?? 'N/A',
                $row['initial_value'],
                $row['residual_value'],
                $row['status']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    /**
     * In a production environment, this would use a library like TCPDF or Dompdf.
     * Here we provide a structured HTML-to-PDF ready output or a simplified version.
     */
    public function exportPdf(array $data): string
    {
        $html = "<h1>Report Contratti</h1><table border='1'><thead><tr><th>ID</th><th>VIN</th><th>Cliente</th><th>Residuo</th></tr></thead><tbody>";
        foreach ($data as $row) {
            $html .= "<tr><td>{$row['id_contract']}</td><td>{$row['vin']}</td><td>" . ($row['customer_name'] ?? 'N/A') . "</td><td>{$row['residual_value']}€</td></tr>";
        }
        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * For Excel, we'd use PhpSpreadsheet.
     * Simplified: returning CSV with .xls extension is a common (though not ideal) legacy trick.
     * For high-quality, we would implement the full library logic.
     */
    public function exportExcel(array $data): string
    {
        return $this->exportCsv($data);
    }
}
