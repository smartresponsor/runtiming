<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Runtime\DependencyInjection;

use App\Runtime\RuntimeSuperchargerContract;
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
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->integerNode('max_request')->min(0)->defaultValue(1000)->end()
                                ->integerNode('max_memory_mb')->min(0)->defaultValue(512)->end()
                                ->integerNode('max_uptime_second')->min(0)->defaultValue(3600)->end()
                                ->integerNode('drain_second')->min(0)->defaultValue(10)->end()
                            ->end()
                        ->end()
                        ->arrayNode('reset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->booleanNode('kernel')->defaultTrue()->end()
                                ->booleanNode('doctrine')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $tree;
    }
}
