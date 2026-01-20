<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigValidatorInterface;
use App\ServiceInterface\Runtime\RuntimeValidationIssue;
use App\ServiceInterface\Runtime\RuntimeValidationReport;

final class RuntimeSuperchargerConfigValidator implements RuntimeSuperchargerConfigValidatorInterface
{
    public function validate(RuntimeSuperchargerConfigInterface $config): RuntimeValidationReport
    {
        $r = new RuntimeValidationReport();

        if ($config->getMaxRequest() <= 0) {
            $r->add(new RuntimeValidationIssue('maxRequest', 'maxRequest must be positive', ['value' => $config->getMaxRequest()]));
        }

        if ($config->getMaxUptimeSec() <= 0) {
            $r->add(new RuntimeValidationIssue('maxUptimeSec', 'maxUptimeSec must be positive', ['value' => $config->getMaxUptimeSec()]));
        }

        if ($config->getMaxMemoryMb() <= 0) {
            $r->add(new RuntimeValidationIssue('maxMemoryMb', 'maxMemoryMb must be positive', ['value' => $config->getMaxMemoryMb()]));
        }

        if ($config->getSoftMemoryMb() < 0) {
            $r->add(new RuntimeValidationIssue('softMemoryMb', 'softMemoryMb must be >= 0', ['value' => $config->getSoftMemoryMb()]));
        }

        if ($config->getSoftMemoryMb() > $config->getMaxMemoryMb()) {
            $r->add(new RuntimeValidationIssue('softMemoryMb.gt.maxMemoryMb', 'softMemoryMb must be <= maxMemoryMb', [
                'softMemoryMb' => $config->getSoftMemoryMb(),
                'maxMemoryMb' => $config->getMaxMemoryMb(),
            ]));
        }

        $path = trim($config->getFeedPath());
        if ($path === '') {
            $r->add(new RuntimeValidationIssue('feedPath', 'feedPath must be non-empty'));
        } elseif (!str_ends_with($path, '.ndjson')) {
            $r->add(new RuntimeValidationIssue('feedPath.ext', 'feedPath must end with .ndjson', ['value' => $config->getFeedPath()]));
        }

        if ($config->getFeedMaxBytes() < 1024) {
            $r->add(new RuntimeValidationIssue('feedMaxBytes', 'feedMaxBytes must be >= 1024', ['value' => $config->getFeedMaxBytes()]));
        }

        if ($config->getFeedMaxKeep() < 0 || $config->getFeedMaxKeep() > 200) {
            $r->add(new RuntimeValidationIssue('feedMaxKeep', 'feedMaxKeep must be in range 0..200', ['value' => $config->getFeedMaxKeep()]));
        }

        return $r;
    }
}
