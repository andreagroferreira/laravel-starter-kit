# 3-Viewport Mandate

Every E2E test runs at THREE viewports:

- Mobile: 375 × 800 (iPhone 13)
- Tablet: 820 × 1180 (iPad)
- Desktop: 1440 × 900 (Desktop Chrome)

## Pattern

```php
use function Pest\Browser\visit;

it('renders X on mobile', function (): void {
    visit('/path')->resize(375, 800)->assertSee('X');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables');

it('renders X on tablet', function (): void {
    visit('/path')->resize(820, 1180)->assertSee('X');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables');

it('renders X on desktop', function (): void {
    visit('/path')->resize(1440, 900)->assertSee('X');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables');
```

## Why 3 viewports

Per spec §6.6.C: WC supports mobile, tablet (iPad), and desktop. No page may break before 768px.
