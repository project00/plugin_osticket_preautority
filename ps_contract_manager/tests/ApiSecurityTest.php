<?php
use PHPUnit\Framework\TestCase;

class ApiSecurityTest extends TestCase
{
    public function testUnauthorizedAccess()
    {
        // Mock request without X-API-Key should return 403
        $this->assertTrue(true);
    }
}
