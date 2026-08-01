<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('posts:publish-scheduled')->everyMinute();
Schedule::command('billing:report-ai-overage')->dailyAt('02:00');
Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('model:prune')->daily();
Schedule::command('integrations:sync')->dailyAt('03:00');
Schedule::command('social:publish-scheduled')->everyMinute();
