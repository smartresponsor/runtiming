<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';




use App\Infra\Runtime\RuntimeSuperchargerEnvConfigProvider;
use App\Service\Runtime\RuntimeSuperchargerConfigValidator;

$provider = new RuntimeSuperchargerEnvConfigProvider();
$config = $provider->getConfig();
$validator = new RuntimeSuperchargerConfigValidator();
$report = $validator->validate($config);

echo json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

exit($report->isOk() ? 0 : 1);
