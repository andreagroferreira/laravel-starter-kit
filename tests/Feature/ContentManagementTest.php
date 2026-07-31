<?php

declare(strict_types=1);

use App\Models\Form;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\Redirect;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('lists media assets of the tenant', function (): void {
    MediaAsset::factory()->for($this->tenant)->create(['filename' => 'hero.jpg']);

    $this->actingAs($this->user)
        ->get('/media')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Media/Index')
            ->has('assets.data', 1)
            ->where('assets.data.0.filename', 'hero.jpg')
        );
});

it('uploads a file to the media library', function (): void {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post('/media', [
            'file' => UploadedFile::fake()->image('photo.jpg', 100, 100),
            'alt' => 'A photo',
        ])
        ->assertRedirect();

    $asset = MediaAsset::query()->sole();

    expect($asset->tenant_id)->toBe($this->tenant->id)
        ->and($asset->alt)->toBe('A photo')
        ->and($asset->disk)->toBe('public');

    Storage::disk('public')->assertExists($asset->path);
});

it('validates media uploads', function (): void {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post('/media', ['file' => UploadedFile::fake()->create('evil.php', 10)])
        ->assertSessionHasErrors('file');
});

it('updates alt text and deletes media', function (): void {
    Storage::fake('public');
    $asset = MediaAsset::factory()->for($this->tenant)->create(['disk' => 'public', 'path' => 'media/x.jpg']);
    Storage::disk('public')->put('media/x.jpg', 'data');

    $this->actingAs($this->user)->put('/media/'.$asset->id, ['alt' => 'new alt']);
    expect($asset->refresh()->alt)->toBe('new alt');

    $this->actingAs($this->user)->delete('/media/'.$asset->id);
    expect(MediaAsset::query()->count())->toBe(0);
    Storage::disk('public')->assertMissing('media/x.jpg');
});

it('manages menus', function (): void {
    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/menus', $this->site->id), ['name' => 'footer'])
        ->assertRedirect();

    $menu = Menu::query()->sole();

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/menus/%s', $this->site->id, $menu->id), [
            'items' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Blog', 'url' => '/blog'],
            ],
        ])
        ->assertRedirect();

    expect($menu->refresh()->items)->toHaveCount(2);

    $this->actingAs($this->user)->delete(sprintf('/sites/%s/menus/%s', $this->site->id, $menu->id));
    expect(Menu::query()->count())->toBe(0);
});

it('renders the menus page', function (): void {
    Menu::factory()->for($this->site)->create();

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/menus', $this->site->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Menus/Index')
            ->has('menus', 1)
        );
});

it('manages forms', function (): void {
    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/forms', $this->site->id), [
            'name' => 'contact',
            'fields' => [
                ['name' => 'email', 'type' => 'email', 'required' => true],
                ['name' => 'message', 'type' => 'textarea', 'required' => false],
            ],
        ])
        ->assertRedirect();

    expect(Form::query()->sole()->fields)->toHaveCount(2);

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/forms', $this->site->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Forms/Index')->has('forms', 1));

    $this->actingAs($this->user)->delete(sprintf('/sites/%s/forms/', $this->site->id).Form::query()->sole()->id);
    expect(Form::query()->count())->toBe(0);
});

it('manages redirects', function (): void {
    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/redirects', $this->site->id), [
            'from_path' => '/old',
            'to_path' => '/new',
            'status_code' => '301',
        ])
        ->assertRedirect();

    expect(Redirect::query()->sole()->from_path)->toBe('/old');

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/redirects', $this->site->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Redirects/Index')->has('redirects', 1));

    $this->actingAs($this->user)->delete(sprintf('/sites/%s/redirects/', $this->site->id).Redirect::query()->sole()->id);
    expect(Redirect::query()->count())->toBe(0);
});

it('rejects invalid redirect paths and status codes', function (): void {
    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/redirects', $this->site->id), [
            'from_path' => 'no-slash',
            'to_path' => '/new',
            'status_code' => '404',
        ])
        ->assertSessionHasErrors(['from_path', 'status_code']);
});

it('scopes media to the current tenant', function (): void {
    MediaAsset::factory()->for(Tenant::factory())->create();

    $this->actingAs($this->user)
        ->get('/media')
        ->assertInertia(fn (Assert $page): Assert => $page->has('assets.data', 0));
});
