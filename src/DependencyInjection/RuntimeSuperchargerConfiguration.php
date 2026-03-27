<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\DependencyInjection;

use App\Contract\RuntimeSuperchargerContract;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class RuntimeSuperchargerConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder(RuntimeSuperchargerContract::CONFIG_ROOT);
        $root = $tree->getRootNode();

        $root
            ->children()
                ->arrayNode('telemetry')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dir')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('endpoint')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('metrics')->defaultTrue()->end()
                        ->booleanNode('status')->defaultTrue()->end()
                        ->arrayNode('security')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->enumNode('mode')
                                    ->values(['allowlist_or_token', 'allowlist_only', 'require_token'])
                                    ->defaultValue('allowlist_or_token')
                                ->end()
                                ->arrayNode('allow_cidr')
                                    ->scalarPrototype()->end()
                                    ->defaultValue(['127.0.0.1/8', '::1/128'])
                                ->end()
                                ->scalarNode('token')->defaultNull()->end()
                                ->scalarNode('header')->defaultValue('X-Runtime-Token')->end()
                                ->booleanNode('proxy_strict')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('worker')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('lifecycle')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->beforeNormalization()->ifString()->then(static fn (string $value): bool => filter_var($value, FILTER_VALIDATE_BOOL))->end()->defaultTrue()->end()
                                ->integerNode('max_request')->beforeNormalization()->ifString()->then(static fn (string $value): int => (int) $value)->end()->min(0)->defaultValue(1000)->end()
                                ->integerNode('max_memory_mb')->beforeNormalization()->ifString()->then(static fn (string $value): int => (int) $value)->end()->min(0)->defaultValue(512)->end()
                                ->integerNode('max_uptime_second')->beforeNormalization()->ifString()->then(static fn (string $value): int => (int) $value)->end()->min(0)->defaultValue(3600)->end()
                                ->integerNode('drain_second')->beforeNormalization()->ifString()->then(static fn (string $value): int => (int) $value)->end()->min(0)->defaultValue(10)->end()
                            ->end()
                        ->end()
                        ->arrayNode('reset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->beforeNormalization()->ifString()->then(static fn (string $value): bool => filter_var($value, FILTER_VALIDATE_BOOL))->end()->defaultTrue()->end()
                                ->booleanNode('kernel')->beforeNormalization()->ifString()->then(static fn (string $value): bool => filter_var($value, FILTER_VALIDATE_BOOL))->end()->defaultTrue()->end()
                                ->booleanNode('doctrine')->beforeNormalization()->ifString()->then(static fn (string $value): bool => filter_var($value, FILTER_VALIDATE_BOOL))->end()->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $tree;
    }
}
