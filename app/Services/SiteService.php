<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContentStatus;
use App\Enums\DeploymentStatus;
use App\Jobs\DeploySite;
use App\Models\Deployment;
use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Site;
use App\Models\SiteVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SiteService
{
    /**
     * Create a site with a starter homepage and a default menu.
     *
     * @param  array{name: string, slug: string, type: string, domain?: string|null, settings?: array<string, mixed>|null}  $data
     */
    public function createSite(array $data): Site
    {
        return DB::transaction(function () use ($data): Site {
            /** @var Site $site */
            $site = Site::query()->create($data);

            $site->pages()->create([
                'title' => 'Home',
                'slug' => '/',
                'status' => ContentStatus::Draft,
                'sort_order' => 0,
            ]);

            $site->menus()->create([
                'name' => 'main',
                'items' => [['label' => 'Home', 'url' => '/']],
            ]);

            return $site;
        });
    }

    /**
     * Build the render schema from published content.
     *
     * @return array<string, mixed>
     */
    public function buildSchema(Site $site): array
    {
        return [
            'site' => [
                'name' => $site->name,
                'slug' => $site->slug,
                'type' => $site->type->value,
                'domain' => $site->domain,
                'settings' => $site->settings,
            ],
            'menus' => $site->menus()->get(['name', 'items'])
                ->mapWithKeys(fn (Menu $menu): array => [$menu->name => $menu->items])
                ->all(),
            // The renderer needs the field definitions to draw form blocks
            // and to post leads back to the public endpoint.
            'forms' => $site->forms()->get(['id', 'name', 'fields'])
                ->map(fn (Form $form): array => [
                    'id' => $form->id,
                    'name' => $form->name,
                    'fields' => $form->fields,
                ])->all(),
            'pages' => $site->pages()->published()
                ->with('blocks')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Page $page): array => [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'seo' => $page->seo,
                    'blocks' => $page->blocks->map(fn (PageBlock $block): array => [
                        'type' => $block->type,
                        'content' => $block->content,
                    ])->all(),
                ])->all(),
        ];
    }

    /**
     * Publish the current published content as a new immutable site version.
     */
    public function publish(Site $site, ?User $author = null, string $origin = 'human'): SiteVersion
    {
        return DB::transaction(function () use ($site, $author, $origin): SiteVersion {
            /** @var SiteVersion $version */
            $version = $site->versions()->create([
                'created_by' => $author?->getKey(),
                'schema' => $this->buildSchema($site),
                'renderer_version' => $site->renderer_version,
                'origin' => $origin,
                'published_at' => now(),
            ]);

            $site->forceFill(['status' => 'published'])->save();

            /** @var Deployment $deployment */
            $deployment = $site->deployments()->create([
                'tenant_id' => $site->tenant_id,
                'site_version_id' => $version->id,
                'type' => 'content',
                'status' => DeploymentStatus::Queued,
                'triggered_by' => $author?->getKey(),
            ]);

            dispatch(new DeploySite($deployment->id, $site->tenant_id))->afterCommit();

            return $version;
        });
    }
}
