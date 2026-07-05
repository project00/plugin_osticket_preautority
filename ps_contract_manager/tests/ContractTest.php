<?php
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PsContractManager\Domain\Contract\Contract;

class ContractTest extends TestCase
{
    public function testResidualValueCalculation()
    {
        $contract = new Contract(1, 1, '12345678901234567', 1000.0);
        $this->assertEquals(1000.0, $contract->calculateResidualValue());
    }

    public function testVinValidation()
    {
        $this->expectException(InvalidArgumentException::class);
        new Contract(1, 1, 'INVALID', 1000.0);
    }
}
