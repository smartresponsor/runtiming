<?php
declare(strict_types=1);



final class FixtureKernelResetInner
{
    private int $count = 0;

    public function reset(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
