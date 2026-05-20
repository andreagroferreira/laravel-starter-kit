<?php

declare(strict_types=1);

namespace WizardingCode\ArkaBridge\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class SyncCommand extends Command
{
    protected $signature = 'arka:sync';

    protected $description = 'Sync multi-runtime agents folders from .agents/ source of truth.';

    public function handle(): int
    {
        $script = base_path('bin/arka-sync-agents');

        if (! is_executable($script)) {
            $this->error('bin/arka-sync-agents not found or not executable.');

            return Command::FAILURE;
        }

        $process = new Process([$script]);
        $process->setWorkingDirectory(base_path());
        $process->setTty(false);
        $process->run(function (string $type, string $buffer): void {
            $this->getOutput()->write($buffer);
        });

        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
