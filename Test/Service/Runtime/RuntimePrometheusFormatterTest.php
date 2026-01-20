<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Test\Service\Runtime;

use App\Service\Runtime\RuntimePrometheusFormatter;
use PHPUnit\Framework\TestCase;

final class RuntimePrometheusFormatterTest extends TestCase
{
    public function testFormatKeySortAndEscapeLabels(): void
    {
        $f = new RuntimePrometheusFormatter();

        $key = $f->formatKey('runtime-supercharger.metric', [
            'b' => "2",
            'a' => "1\n\"x\"",
        ]);

        self::assertSame('runtime_supercharger_metric{a="1\\n\\\"x\\\"",b="2"}', $key);
    }

    public function testSafeNameAndLabelName(): void
    {
        $f = new RuntimePrometheusFormatter();

        self::assertSame('_1abc', $f->safeName('1abc'));
        self::assertSame('abc_def', $f->safeName('abc-def'));
        self::assertSame('_', $f->safeName(''));

        self::assertSame('_1x', $f->safeLabelName('1x'));
        self::assertSame('a_b', $f->safeLabelName('a-b'));
    }
}
