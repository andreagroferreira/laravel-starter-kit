<?php

declare(strict_types=1);

use App\Enums\BlockType;

/**
 * packages/blocks is the single source of truth shared by the editor and
 * the public renderer. This test fails the moment PHP and TypeScript
 * disagree on which block types exist.
 */
it('keeps the php enum and the js block manifest in sync', function (): void {
    $manifestPath = base_path('packages/blocks/manifest.json');

    expect(file_exists($manifestPath))->toBeTrue();

    /** @var array{types: list<string>} $manifest */
    $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

    $php = BlockType::values();
    $js = $manifest['types'];

    sort($php);
    sort($js);

    expect($js)->toBe($php);
});

it('has a vue component for every block type', function (): void {
    foreach (BlockType::cases() as $type) {
        $component = str_replace(' ', '', ucwords(str_replace('_', ' ', $type->value))).'Block.vue';

        expect(base_path('packages/blocks/src/components/'.$component))->toBeFile();
    }
});
