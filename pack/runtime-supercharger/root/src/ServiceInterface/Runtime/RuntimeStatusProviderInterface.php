<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeStatusProviderInterface
{
    public function snapshot(): RuntimeStatusSnapshot;
}
