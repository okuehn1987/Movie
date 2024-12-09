import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('admin can decline time correction', async ({ page }) => {
    await expect(page.getByText('Zeitkorrekturen')).toBeVisible();
    await page.getByRole('cell', { name: 'user user' }).click();
    await expect(page.getByText('Zeitkorrektur von user user')).toBeVisible();
    await page.getByRole('button', { name: 'Ablehnen' }).click();
    await expect(page.getByText('Zeitkorrektur erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'keine Zeitkorrekturen' })).toBeVisible();
});

//TODO:nonsense shouldnt be possible, should only be available for user
test('admin can withdraw time correction request', async ({ page }) => {
    await page.getByRole('navigation').getByText('Arbeitszeiten').click();
    await expect(page).toHaveURL('/workLog');
    await page.getByRole('row', { name: 'user user Gehen 06.12.2024 18:' }).getByRole('button').click();
    await expect(page.getByText('user user')).toBeVisible();
    await expect(page.getByRole('cell', { name: '08:15' })).toBeVisible();
    await page.getByRole('row', { name: '06.12.2024 08:15 06.12.2024' }).getByRole('button').click();
    await expect(page.getByText('Zeitkorrektur')).toBeVisible();
    await page.getByRole('button', { name: 'Antrag zurückziehen' }).click();
    await expect(page.getByText('Antrag auf Zeitkorrektur')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Nicht vorhanden' })).toBeVisible();
});
