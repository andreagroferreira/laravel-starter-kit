<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\SiteType;
use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'wizardingcode'],
            ['name' => 'WizardingCode', 'plan' => 'pro', 'ai_credits_monthly' => 2_000],
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'demo@wizardingcode.pt'],
            [
                'name' => 'Demo WizardingCode',
                'password' => 'password',
                'email_verified_at' => now(),
                'current_tenant_id' => $tenant->id,
            ],
        );

        $tenant->users()->syncWithoutDetaching([$user->id => ['joined_at' => now()]]);

        resolve(TenantProvisioner::class)->provision($tenant);
        setPermissionsTeamId($tenant->id);

        $user->assignRole(TenantRole::Owner);

        $tenant->brandProfile()->firstOrCreate(
            ['name' => 'WizardingCode'],
            [
                'tone_of_voice' => 'direto, técnico mas acessível, sem jargão corporativo',
                'glossary' => ['CMS' => 'sistema de gestão de conteúdos'],
                'examples' => ['Construímos software que resolve problemas reais.'],
            ],
        );

        $site = $tenant->sites()->firstOrCreate(
            ['slug' => 'wizardingcode-site'],
            ['name' => 'WizardingCode', 'type' => SiteType::Site],
        );

        $home = $site->pages()->firstOrCreate(
            ['slug' => '/'],
            ['title' => 'Home', 'status' => ContentStatus::Published, 'published_at' => now()],
        );

        if ($home->blocks()->count() === 0) {
            $home->blocks()->createMany([
                ['type' => 'hero', 'content' => ['heading' => 'Software à medida que escala', 'subheading' => 'Laravel, Nuxt e AI aplicados ao teu negócio'], 'sort_order' => 0],
                ['type' => 'features', 'content' => ['items' => [['title' => 'Web Apps'], ['title' => 'E-commerce'], ['title' => 'AI']]], 'sort_order' => 1],
                ['type' => 'cta', 'content' => ['label' => 'Fala connosco', 'url' => '/contactos'], 'sort_order' => 2],
            ]);
        }

        $site->menus()->firstOrCreate(
            ['name' => 'main'],
            ['items' => [['label' => 'Home', 'url' => '/'], ['label' => 'Blog', 'url' => '/blog']]],
        );

        $site->posts()->firstOrCreate(
            ['slug' => 'hello-wizardingcode'],
            [
                'author_id' => $user->id,
                'title' => 'Olá, WizardingCode',
                'status' => ContentStatus::Published,
                'excerpt' => 'O primeiro post do nosso novo site.',
                'body' => '<p>Construído com o nosso próprio CMS.</p>',
                'published_at' => now(),
            ],
        );
    }
}
