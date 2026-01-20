<?php
declare(strict_types=1);



require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeSuperchargerConfigInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeSuperchargerConfigValidatorInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeValidationIssue.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeValidationReport.php';

require_once __DIR__ . '/../../src/Service/Runtime/RuntimeSuperchargerConfig.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeSuperchargerConfigValidator.php';

require_once __DIR__ . '/../../src/InfraInterface/Runtime/RuntimeSuperchargerConfigProviderInterface.php';
require_once __DIR__ . '/../../src/Infra/Runtime/RuntimeSuperchargerEnvConfigProvider.php';

use App\Infra\Runtime\RuntimeSuperchargerEnvConfigProvider;
use App\Service\Runtime\RuntimeSuperchargerConfigValidator;

$provider = new RuntimeSuperchargerEnvConfigProvider();
$config = $provider->getConfig();
$validator = new RuntimeSuperchargerConfigValidator();
$report = $validator->validate($config);

echo json_encode([
    'config' => $config->toArray(),
    'validation' => $report->toArray(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

exit($report->isOk() ? 0 : 1);
