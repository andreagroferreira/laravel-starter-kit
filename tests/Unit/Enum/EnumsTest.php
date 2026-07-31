<?php

declare(strict_types=1);

use App\Enums\BlockType;
use App\Enums\ContentStatus;
use App\Enums\SiteType;
use App\Enums\TenantRole;

it('exposes enum values', function (): void {
    expect(TenantRole::values())->toContain('owner', 'editor_in_chief')
        ->and(SiteType::values())->toBe(['site', 'landing', 'news'])
        ->and(ContentStatus::values())->toContain('draft', 'published')
        ->and(BlockType::values())->toContain('hero', 'rich_text');
});
