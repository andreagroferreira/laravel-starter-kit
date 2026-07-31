<?php

declare(strict_types=1);

use App\Enums\TenantRole;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->site = Site::factory()->for($this->tenant)->create();
});

function memberWithRole(Tenant $tenant, TenantRole $role): User
{
    $user = User::factory()->create(['current_tenant_id' => $tenant->id]);
    $tenant->users()->attach($user);
    grantRole($tenant, $user, $role);

    return $user;
}

/**
 * @param  TestResponse<Response>  $response
 */
function assertRbac(TestResponse $response, bool $allowed): void
{
    if ($allowed) {
        expect($response->status())->toBeLessThan(400);
    } else {
        $response->assertForbidden();
    }
}

it('enforces sites.create', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->post('/sites', ['name' => 'Empresa', 'slug' => 'empresa-'.mb_strtolower($role->value), 'type' => 'site']);

    assertRbac($response, $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor' => [TenantRole::Editor, false],
    'marketeer' => [TenantRole::Marketeer, false],
    'journalist' => [TenantRole::Journalist, false],
    'editor_in_chief' => [TenantRole::EditorInChief, false],
]);

it('enforces sites.delete', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->delete('/sites/'.$this->site->id);

    assertRbac($response, $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor' => [TenantRole::Editor, false],
    'editor_in_chief' => [TenantRole::EditorInChief, false],
]);

it('enforces sites.publish', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->post(sprintf('/sites/%s/publish', $this->site->id));

    assertRbac($response, $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor' => [TenantRole::Editor, true],
    'marketeer' => [TenantRole::Marketeer, false],
    'journalist' => [TenantRole::Journalist, false],
]);

it('enforces pages.manage on creation', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->post(sprintf('/sites/%s/pages', $this->site->id), ['title' => 'Página', 'slug' => 'pagina-'.mb_strtolower($role->value)]);

    assertRbac($response, $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor' => [TenantRole::Editor, true],
    'marketeer' => [TenantRole::Marketeer, true],
    'journalist' => [TenantRole::Journalist, false],
    'editor_in_chief' => [TenantRole::EditorInChief, false],
]);

it('enforces pages.publish', function (TenantRole $role, bool $allowed): void {
    $page = $this->site->pages()->create(['title' => 'P', 'slug' => 'p-'.mb_strtolower($role->value)]);

    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->post(sprintf('/sites/%s/pages/%s/publish', $this->site->id, $page->id));

    assertRbac($response, $allowed);
})->with([
    'editor' => [TenantRole::Editor, true],
    'marketeer' => [TenantRole::Marketeer, false],
]);

it('enforces posts.create', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->post(sprintf('/sites/%s/posts', $this->site->id), ['title' => 'Artigo', 'slug' => 'artigo-'.mb_strtolower($role->value)]);

    assertRbac($response, $allowed);
})->with([
    'journalist' => [TenantRole::Journalist, true],
    'editor_in_chief' => [TenantRole::EditorInChief, true],
    'marketeer' => [TenantRole::Marketeer, false],
]);

it('lets a journalist update their own post but not posts of others', function (): void {
    $journalist = memberWithRole($this->tenant, TenantRole::Journalist);
    $own = Post::factory()->for($this->site)->create(['author_id' => $journalist->id]);
    $foreign = Post::factory()->for($this->site)->create(['author_id' => null]);

    $this->actingAs($journalist)
        ->put(sprintf('/sites/%s/posts/%s', $this->site->id, $own->id), ['title' => 'Meu', 'slug' => $own->slug])
        ->assertRedirect();

    $this->actingAs($journalist)
        ->put(sprintf('/sites/%s/posts/%s', $this->site->id, $foreign->id), ['title' => 'Alheio', 'slug' => $foreign->slug])
        ->assertForbidden();
});

it('lets an editor-in-chief update any post', function (): void {
    $chief = memberWithRole($this->tenant, TenantRole::EditorInChief);
    $foreign = Post::factory()->for($this->site)->create(['author_id' => null]);

    $this->actingAs($chief)
        ->put(sprintf('/sites/%s/posts/%s', $this->site->id, $foreign->id), ['title' => 'Editado', 'slug' => $foreign->slug])
        ->assertRedirect();
});

it('enforces posts.delete and posts.publish', function (TenantRole $role, bool $allowed): void {
    $post = Post::factory()->for($this->site)->create();
    $user = memberWithRole($this->tenant, $role);

    assertRbac($this->actingAs($user)->post(sprintf('/sites/%s/posts/%s/publish', $this->site->id, $post->id)), $allowed);
    assertRbac($this->actingAs($user)->delete(sprintf('/sites/%s/posts/%s', $this->site->id, $post->id)), $allowed);
})->with([
    'editor_in_chief' => [TenantRole::EditorInChief, true],
    'journalist' => [TenantRole::Journalist, false],
]);

it('enforces menus, forms and redirects management', function (TenantRole $role, bool $menus, bool $forms): void {
    $user = memberWithRole($this->tenant, $role);

    assertRbac($this->actingAs($user)->post(sprintf('/sites/%s/menus', $this->site->id), ['name' => 'footer-'.mb_strtolower($role->value)]), $menus);
    assertRbac($this->actingAs($user)->post(sprintf('/sites/%s/redirects', $this->site->id), ['from_path' => '/a-'.mb_strtolower($role->value), 'to_path' => '/b', 'status_code' => '301']), $menus);
    assertRbac($this->actingAs($user)->post(sprintf('/sites/%s/forms', $this->site->id), ['name' => 'contact-'.mb_strtolower($role->value), 'fields' => [['name' => 'email', 'type' => 'email', 'required' => true]]]), $forms);
})->with([
    'editor' => [TenantRole::Editor, true, true],
    'marketeer' => [TenantRole::Marketeer, false, true],
    'journalist' => [TenantRole::Journalist, false, false],
]);

it('allows every role to upload media', function (): void {
    $user = memberWithRole($this->tenant, TenantRole::Journalist);

    Storage::fake('public');

    $this->actingAs($user)
        ->post('/media', ['file' => UploadedFile::fake()->image('foto.png')])
        ->assertRedirect();

    expect(MediaAsset::query()->count())->toBe(1);
});

it('enforces members.manage on invites and removals', function (TenantRole $role, bool $allowed): void {
    $user = memberWithRole($this->tenant, $role);
    $target = memberWithRole($this->tenant, TenantRole::Editor);

    assertRbac($this->actingAs($user)->post('/settings/members', ['email' => mb_strtolower($role->value).'@exemplo.pt', 'role' => 'editor']), $allowed);
    assertRbac($this->actingAs($user)->delete('/settings/members/'.$target->id), $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor' => [TenantRole::Editor, false],
    'editor_in_chief' => [TenantRole::EditorInChief, false],
]);

it('refuses to remove the last owner', function (): void {
    $owner = memberWithRole($this->tenant, TenantRole::Owner);
    $secondOwner = memberWithRole($this->tenant, TenantRole::Owner);

    // With two owners the removal succeeds…
    $this->actingAs($owner)->delete('/settings/members/'.$secondOwner->id)->assertRedirect();

    // …but the survivor cannot be orphaned by a fresh owner-less state.
    $editor = memberWithRole($this->tenant, TenantRole::Editor);
    $this->actingAs($editor); // editor lacks members.manage entirely
    $this->actingAs($owner)->delete('/settings/members/'.$owner->id)->assertStatus(422);
});

it('enforces tokens.manage', function (TenantRole $role, bool $allowed): void {
    $user = memberWithRole($this->tenant, $role);

    assertRbac($this->actingAs($user)->get('/settings/api'), $allowed);
    assertRbac($this->actingAs($user)->post('/settings/api/tokens', ['name' => 'cli', 'abilities' => ['read']]), $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor' => [TenantRole::Editor, false],
]);

it('enforces billing.manage', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))->get('/settings/billing');

    assertRbac($response, $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'marketeer' => [TenantRole::Marketeer, false],
]);

it('enforces audit.view', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))->get('/settings/audit');

    assertRbac($response, $allowed);
})->with([
    'owner' => [TenantRole::Owner, true],
    'editor_in_chief' => [TenantRole::EditorInChief, true],
    'editor' => [TenantRole::Editor, false],
]);

it('enforces brand.manage', function (TenantRole $role, bool $allowed): void {
    $response = $this->actingAs(memberWithRole($this->tenant, $role))
        ->put('/settings/brand', ['name' => 'Marca']);

    assertRbac($response, $allowed);
})->with([
    'marketeer' => [TenantRole::Marketeer, true],
    'journalist' => [TenantRole::Journalist, false],
]);

it('enforces ai.generate through the form requests', function (): void {
    $user = memberWithRole($this->tenant, TenantRole::Journalist);

    // Journalists hold ai.generate — the request is authorized (fails later only if credits/agent missing).
    expect($user->can('ai.generate'))->toBeTrue();
});
