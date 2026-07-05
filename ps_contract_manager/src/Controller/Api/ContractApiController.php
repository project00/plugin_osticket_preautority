<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use PrestaShop\Module\PsContractManager\Infrastructure\Persistence\ContractRepository;

class ContractApiController extends AbstractController
{
    private ContractRepository $repository;

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

        $contracts = $this->repository->findActiveByVinAndCustomer($vin, (int)$customerId);
        return new JsonResponse($contracts);
    }

    public function authorizeIntervention(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['contract_id'], $data['amount'])) {
            return new JsonResponse(['error' => 'Missing data'], 400);
        }

        $success = $this->repository->authorizeIntervention(
            (int)$data['contract_id'],
            (float)$data['amount'],
            $data
        );

        return new JsonResponse(['success' => $success]);
    }
}
