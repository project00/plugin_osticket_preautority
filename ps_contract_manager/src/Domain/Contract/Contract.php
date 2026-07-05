<?php
declare(strict_types=1);

namespace PrestaShop\Module\PsContractManager\Domain\Contract;

class Contract
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_CONSUMED = 'CONSUMED';

    private int $id;
    private int $orderId;
    private int $customerId;
    private string $vin;
    private float $initialValue;
    private string $status;
    private array $interventions = [];

    public function __construct(int $orderId, int $customerId, string $vin, float $initialValue)
    {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->setVin($vin);
        $this->initialValue = $initialValue;
        $this->status = self::STATUS_ACTIVE;
    }

    public function getId(): int { return $this->id; }
    public function getVin(): string { return $this->vin; }
    public function getInitialValue(): float { return $this->initialValue; }
    public function getStatus(): string { return $this->status; }

    public function setVin(string $vin): void
    {
        if (strlen($vin) !== 17) {
            throw new \InvalidArgumentException('VIN must be exactly 17 characters.');
        }
        $this->vin = $vin;
    }

    public function calculateResidualValue(): float
    {
        $totalInterventions = 0.0;
        foreach ($this->interventions as $intervention) {
            if ($intervention->isAuthorized()) {
                $totalInterventions += $intervention->getValue();
            }
        }
        return max(0, $this->initialValue - $totalInterventions);
    }

    public function addIntervention(float $value): void
    {
        if ($this->calculateResidualValue() < $value) {
            throw new \DomainException('Insufficient residual value.');
        }

        // Logical check for status
        if ($this->calculateResidualValue() - $value <= 0) {
            $this->status = self::STATUS_CONSUMED;
        }
    }
}
