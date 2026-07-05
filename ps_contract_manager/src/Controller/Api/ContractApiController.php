<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Controller\Api;

use PrestaShopBundle\Controller\Api\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use PrestaShop\Module\PsContractManager\Infrastructure\Persistence\ContractRepository;

class ContractApiController extends ApiController
{
    private $repository;

    public function __construct(ContractRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getContracts(Request $request): JsonResponse
    {
        $vin = $request->query->get('vin');
        $customerId = $request->query->get('customer_id');

        if (!$vin || !$customerId) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        $contracts = $this->repository->findByVinAndCustomer($vin, (int)$customerId);

        // Enrichment with residual value would happen here
        return new JsonResponse($contracts);
    }

    public function authorizeIntervention(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Transactional logic:
        // 1. Check residual value
        // 2. Insert intervention
        // 3. Update contract status if needed

        $success = $this->repository->saveIntervention($data);

        return new JsonResponse(['success' => $success]);
    }
}
