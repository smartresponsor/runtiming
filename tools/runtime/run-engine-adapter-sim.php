<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';


use App\Service\Runtime\RuntimeEngineAdapter;

final class Decision
{
    public function toArray(): array
    {
        return ['shouldRecycle' => true, 'reason' => 'maxRequest'];
    }
}

$a = new RuntimeEngineAdapter(false);
$action1 = $a->plan(new Decision(), []);
$action2 = $a->plan(null, ['X-Runtime-Supercharger-Recycle' => '1', 'X-Runtime-Supercharger-Reason' => 'maxMemory']);

echo json_encode([
    'decision' => $action1->toArray(),
    'header' => $action2->toArray(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
