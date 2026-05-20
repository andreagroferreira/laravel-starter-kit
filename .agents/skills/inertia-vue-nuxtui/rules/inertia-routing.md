# Inertia Routing

## Laravel side

```php
use Inertia\Inertia;

Route::middleware(['web', 'auth:web'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', fn () => Inertia::render('Dashboard/Index'))->name('dashboard');
    Route::get('/customers', fn () => Inertia::render('Customers/Index', [
        'customers' => Customer::paginate(),
    ]))->name('customers');
});
```

## Vue side

```ts
import { Link, router, usePage } from '@inertiajs/vue3';

// Navigate programmatically
router.visit('/admin/customers');

// Visit with params
router.get('/admin/customers', { search: 'foo' });

// Reactive page object
const page = usePage();
const user = computed(() => page.props.auth?.user);
```

## Adding a new page-type

1. Create `resources/js/Pages/<Module>/Index.vue`.
2. Add the route in `routes/web.php`.
3. Add the matching component import in BackofficeLayout's sidebar nav links.
4. Pest feature test: `it('renders X via Inertia', fn () => get('/admin/x')->assertOk()->assertInertia(...))`.
5. KB Obsidian note (if the page introduces new UI patterns).
6. Pest browser scenario for the happy path (3 viewports).
