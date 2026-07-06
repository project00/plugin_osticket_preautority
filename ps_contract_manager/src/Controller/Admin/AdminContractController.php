<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PrestaShop\Module\PsContractManager\Infrastructure\Persistence\ContractRepository;
use PrestaShop\Module\PsContractManager\Infrastructure\Service\ExportService;

class AdminContractController extends FrameworkBundleAdminController
{
    private ContractRepository $repository;
    private ExportService $exportService;

    public function __construct(ContractRepository $repository, ExportService $exportService)
    {
        $this->repository = $repository;
        $this->exportService = $exportService;
    }

    public function indexAction(Request $request): Response
    {
        $contracts = $this->repository->findAllWithInterventions();

        return $this->render('@Modules/ps_contract_manager/views/templates/admin/reporting.twig', [
            'contracts' => $contracts,
        ]);
    }

    public function exportAction(string $type): Response
    {
        $data = $this->repository->findAllWithInterventions();

        switch ($type) {
            case 'csv':
                $content = $this->exportService->exportCsv($data);
                $contentType = 'text/csv';
                break;
            case 'pdf':
                $content = $this->exportService->exportPdf($data);
                $contentType = 'application/pdf';
                break;
            default:
                throw new \InvalidArgumentException('Unsupported export type');
        }

        return new Response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="contracts_export.' . $type . '"',
        ]);
    }
}
