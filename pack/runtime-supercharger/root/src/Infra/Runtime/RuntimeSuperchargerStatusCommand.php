<?php
declare(strict_types=1);



namespace App\Infra\Runtime;

use App\InfraInterface\Runtime\RuntimeSuperchargerConfigProviderInterface;
use App\Service\Runtime\RuntimeSuperchargerConfigValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RuntimeSuperchargerStatusCommand extends Command
{
    protected static $defaultName = 'runtime:supercharger:status';

    private RuntimeSuperchargerConfigProviderInterface $provider;

    public function __construct(RuntimeSuperchargerConfigProviderInterface $provider)
    {
        parent::__construct();
        $this->provider = $provider;
    }

    protected function configure(): void
    {
        $this->setDescription('Print effective Runtime Supercharger config as JSON.');
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
