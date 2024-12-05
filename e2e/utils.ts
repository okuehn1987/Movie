import { expect, Page } from '@playwright/test';
import { refreshDatabase } from './laravel-helpers';

export async function adminLogin(page: Page) {
    await page.goto('/');
    await page.getByLabel('Email').fill('admin@admin.com');
    await page.getByLabel('Passwort').fill('admin');
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page).toHaveURL('/dashboard');
}

// export async function userLogin(page: Page) {}

export async function resetAndSeedDatabase(page: Page) {
    await refreshDatabase({ page, parameters: { '--seeder': 'DatabaseSeederE2E' } });
}
