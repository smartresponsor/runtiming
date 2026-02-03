<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Runtime;

use App\RuntimeInterface\RuntimeSuperchargerConfigProviderInterface;
use App\Service\Runtime\RuntimeSuperchargerConfigValidator;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RuntimeSuperchargerValidateCommand extends Command
{
    protected static $defaultName = 'runtime:supercharger:validate';

    private RuntimeSuperchargerConfigProviderInterface $provider;

    public function __construct(RuntimeSuperchargerConfigProviderInterface $provider)
    {
        parent::__construct();
        $this->provider = $provider;
    }

    protected function configure(): void
    {
        $this->setDescription('Validate Runtime Supercharger config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->provider->getConfig();
        $validator = new RuntimeSuperchargerConfigValidator();
        $report = $validator->validate($config);

        $payload = json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new RuntimeException(sprintf('Unable to encode validation report: %s', json_last_error_msg()));
        }

        $output->writeln($payload);
        return $report->isOk() ? Command::SUCCESS : Command::FAILURE;
    }
}
