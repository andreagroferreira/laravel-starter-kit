<?php

declare(strict_types=1);

use App\Models\AiUsage;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Form;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\PageBlock;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Site;
use App\Models\SiteVersion;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('relates categories to site, parent, children and posts', function (): void {
    $site = Site::factory()->create();
    $parent = Category::factory()->for($site)->create();
    $child = Category::factory()->for($site)->for($parent, 'parent')->create();
    $post = Post::factory()->for($site)->create();
    $post->categories()->attach($parent);

    expect($child->parent->id)->toBe($parent->id)
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->posts)->toHaveCount(1)
        ->and($parent->site->id)->toBe($site->id);
});

it('relates forms, redirects, menus, blocks and versions to their parents', function (): void {
    $site = Site::factory()->create();

    $form = Form::factory()->for($site)->create();
    $redirect = Redirect::factory()->for($site)->create();
    $menu = Menu::factory()->for($site)->create();
    $version = SiteVersion::factory()->for($site)->create();
    $block = PageBlock::factory()->create();

    expect($form->site->id)->toBe($site->id)
        ->and($redirect->site->id)->toBe($site->id)
        ->and($menu->site->id)->toBe($site->id)
        ->and($version->site->id)->toBe($site->id)
        ->and($block->page)->not->toBeNull();
});

it('resolves media asset url and uploader', function (): void {
    Storage::fake('r2');

    $user = User::factory()->create();
    $asset = MediaAsset::factory()->create(['uploaded_by' => $user->id, 'disk' => 'r2', 'path' => 'media/x.jpg']);

    expect($asset->uploader->id)->toBe($user->id)
        ->and($asset->url())->toContain('media/x.jpg');
});

it('relates usage and audit logs to users', function (): void {
    $user = User::factory()->create();

    $usage = AiUsage::factory()->create(['user_id' => $user->id]);
    $log = AuditLog::factory()->create(['user_id' => $user->id]);

    expect($usage->user->id)->toBe($user->id)
        ->and($log->user->id)->toBe($user->id);
});

it('exposes tenant relations', function (): void {
    $tenant = Tenant::factory()->create();
    AiUsage::factory()->for($tenant)->create();
    AuditLog::factory()->for($tenant)->create();
    Site::factory()->for($tenant)->create();

    expect($tenant->aiUsages)->toHaveCount(1)
        ->and($tenant->auditLogs)->toHaveCount(1)
        ->and($tenant->sites)->toHaveCount(1);
});

it('exposes version author and post author', function (): void {
    $user = User::factory()->create();
    $version = SiteVersion::factory()->create(['created_by' => $user->id]);
    $post = Post::factory()->create(['author_id' => $user->id]);

    expect($version->author->id)->toBe($user->id)
        ->and($post->author->id)->toBe($user->id)
        ->and($post->site)->not->toBeNull();
});

it('exposes remaining site relations', function (): void {
    $site = Site::factory()->create();
    Form::factory()->for($site)->create();
    Redirect::factory()->for($site)->create();
    Menu::factory()->for($site)->create();
    SiteVersion::factory()->for($site)->published()->create();
    Post::factory()->for($site)->create();
    Category::factory()->for($site)->create();

    expect($site->forms)->toHaveCount(1)
        ->and($site->redirects)->toHaveCount(1)
        ->and($site->menus)->toHaveCount(1)
        ->and($site->versions)->toHaveCount(1)
        ->and($site->publishedVersion)->not->toBeNull()
        ->and($site->posts)->toHaveCount(1)
        ->and($site->categories)->toHaveCount(1);
});
