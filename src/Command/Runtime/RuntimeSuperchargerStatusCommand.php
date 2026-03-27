<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Command\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigProviderInterface;
use App\Service\Runtime\RuntimeSuperchargerConfigValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'runtime:supercharger:status',
    description: 'Print effective Runtime Supercharger config as JSON.',
)]
final class RuntimeSuperchargerStatusCommand extends Command
{
    private RuntimeSuperchargerConfigProviderInterface $provider;

    public function __construct(RuntimeSuperchargerConfigProviderInterface $provider)
    {
        parent::__construct();
        $this->provider = $provider;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->provider->getConfig();
        $validator = new RuntimeSuperchargerConfigValidator();
        $report = $validator->validate($config);

        $payload = [
            'config' => $config->toArray(),
            'validation' => $report->toArray(),
        ];

        $output->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $report->isOk() ? Command::SUCCESS : Command::FAILURE;
    }
}
