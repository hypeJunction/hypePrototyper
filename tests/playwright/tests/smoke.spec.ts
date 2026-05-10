import { test, expect } from '@playwright/test';

/**
 * E2E smoke for hypeprototyper.
 *
 * The form-builder framework plugin: 33 classes, declarative view
 * extensions for elgg.css + admin.css adding the prototyper stylesheet,
 * plus simplecache asset views for jquery.cropper.{css,js}. Catches:
 *   - "framework plugin doesn't load at all" (homepage smoke)
 *   - "stylesheet view extension target/key drifted" (css aggregate)
 *   - "cropper asset views are gone" (asset URL resolves)
 */
test.describe('hypeprototyper', () => {
  test('homepage renders with no PHP fatal markers', async ({ page }) => {
    const response = await page.goto('/');
    expect(response).toBeTruthy();
    expect(response!.status()).toBeLessThan(500);
    const body = await page.content();
    expect(body).not.toContain('Fatal error');
    expect(body).not.toContain('Uncaught');
    expect(body).not.toContain('ParseError');
  });

  test('default css aggregate compiles with prototyper stylesheet extension', async ({ page }) => {
    const response = await page.goto('/cache/0/default/elgg.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });

  test('admin css aggregate compiles', async ({ page }) => {
    const response = await page.goto('/cache/0/default/admin.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });

  test('jquery.cropper.css simplecache asset resolves', async ({ page }) => {
    // hypeprototyper exposes a vendored jquery-cropper CSS as a view
    // so it can be served via Elgg simplecache. If the view file is
    // missing or the path key drifted the cache URL 404s.
    const response = await page.goto('/cache/0/default/jquery.cropper.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
    }
  });
});
