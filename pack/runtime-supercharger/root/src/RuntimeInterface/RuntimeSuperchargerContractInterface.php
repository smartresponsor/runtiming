<?php
declare(strict_types=1);



namespace App\RuntimeInterface;

interface RuntimeSuperchargerContractInterface
{
    public const CONFIG_ROOT = 'runtime_supercharger';

    public const ENV_TELEMETRY_DIR = 'RUNTIME_TELEMETRY_DIR';
    public const ENV_WORKER_ID = 'RUNTIME_WORKER_ID';
    public const ENV_ENGINE = 'RUNTIME_ENGINE';

    public const PARAM_TELEMETRY_DIR = 'runtime_supercharger_telemetry_dir';

    public const ENDPOINT_METRICS_PATH = '/metrics';
    public const ENDPOINT_METRICS_AGGREGATE_PATH = '/runtime/metrics/aggregate';
    public const ENDPOINT_STATUS_PATH = '/status';
    public const ENDPOINT_STATUS_HOST_PATH = '/runtime/status/host';
}
