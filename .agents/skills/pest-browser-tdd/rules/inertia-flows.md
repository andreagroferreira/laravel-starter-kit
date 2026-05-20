# Testing Inertia Flows

Inertia uses a JSON envelope on regular requests and full HTML on initial visit. Tests need both:

## Feature tests (no browser)

```php
get('/admin')->assertInertia(fn (Assert $page) => $page->component('Dashboard/Index')->where('auth.user.email', 'admin@wc.io'));
```

## Browser tests

```php
visit('/admin/login')
    ->fill('input[name="email"]', 'admin@wc.io')
    ->fill('input[name="password"]', 'StrongPass!')
    ->click('button[type="submit"]')
    ->wait(500) // Inertia transition
    ->assertPathIs('/admin');
```

## Patterns to test

- Form submission with Inertia useForm (validation errors, loading state).
- Page navigation (router.visit) — assertPathIs.
- Partial reloads (router.reload with only: [...]).
- Persistent layout transitions (sidebar persists across pages).
