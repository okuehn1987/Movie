import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page.getByRole('navigation').getByText('Anträge').click();
    await expect(page).toHaveURL('/dispute');
});

test('admin can decline time correction', async ({ page }) => {
    await expect(page.getByText('Zeitkorrekturen')).toBeVisible();
    await page.getByRole('cell', { name: 'user user' }).click();
    await expect(page.getByText('Zeitkorrektur von user user')).toBeVisible();
    await page.getByRole('button', { name: 'Ablehnen' }).click();
    await expect(page.getByText('Zeitkorrektur erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'keine Zeitkorrekturen' })).toBeVisible();
});
