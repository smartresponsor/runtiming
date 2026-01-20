<?php
declare(strict_types=1);



namespace App\Tool\Runtime\Fixture;

interface FixtureResetRegistryInterface
{
    public function resetAll(): FixtureResetReport;
}
