<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerConfigValidatorInterface
{
    public function validate(RuntimeSuperchargerConfigInterface $config): RuntimeValidationReport;
}
