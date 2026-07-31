<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\TenantRole;

/**
 * Single source of truth for the permission × role matrix.
 *
 * Roles are team-scoped (one set per tenant); permissions are global rows
 * synced onto each tenant's roles by the TenantProvisioner.
 */
final class RoleMatrix
{
    /**
     * @var array<string, list<TenantRole>>
     */
    private const array MATRIX = [
        'sites.view' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer, TenantRole::Journalist, TenantRole::EditorInChief],
        'sites.create' => [TenantRole::Owner],
        'sites.delete' => [TenantRole::Owner],
        'sites.publish' => [TenantRole::Owner, TenantRole::Editor],
        'pages.manage' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer],
        'pages.publish' => [TenantRole::Owner, TenantRole::Editor],
        'posts.create' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Journalist, TenantRole::EditorInChief],
        'posts.update-own' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Journalist, TenantRole::EditorInChief],
        'posts.update-any' => [TenantRole::Owner, TenantRole::Editor, TenantRole::EditorInChief],
        'posts.delete' => [TenantRole::Owner, TenantRole::Editor, TenantRole::EditorInChief],
        'posts.publish' => [TenantRole::Owner, TenantRole::Editor, TenantRole::EditorInChief],
        'menus.manage' => [TenantRole::Owner, TenantRole::Editor],
        'redirects.manage' => [TenantRole::Owner, TenantRole::Editor],
        'forms.manage' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer],
        'leads.view' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer],
        'leads.export' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer],
        'media.manage' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer, TenantRole::Journalist, TenantRole::EditorInChief],
        'ai.generate' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer, TenantRole::Journalist, TenantRole::EditorInChief],
        'brand.manage' => [TenantRole::Owner, TenantRole::Editor, TenantRole::Marketeer],
        'members.manage' => [TenantRole::Owner],
        'billing.manage' => [TenantRole::Owner],
        'tokens.manage' => [TenantRole::Owner],
        'audit.view' => [TenantRole::Owner, TenantRole::EditorInChief],
    ];

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return array_keys(self::MATRIX);
    }

    /**
     * @return list<string>
     */
    public static function permissionsFor(TenantRole $role): array
    {
        return array_keys(array_filter(
            self::MATRIX,
            fn (array $roles): bool => in_array($role, $roles, true),
        ));
    }
}
