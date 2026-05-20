<?php

declare(strict_types=1);

use Orchestra\Testbench\TestCase;

pest()
    ->extend(TestCase::class)
    ->in('Feature');
