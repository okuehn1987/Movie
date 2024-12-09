import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('can switch between sites', async ({ page }) => {
    await expect(page).toHaveURL('/dashboard');
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page.getByRole('banner').getByText('Abwesenheiten')).toBeVisible();
    await page.getByText('Arbeitszeiten').click();
    await expect(page.getByRole('banner').getByText('Arbeitszeiten')).toBeVisible();
    await page.getByText('Organisation', { exact: true }).click();
    await expect(page.getByText('Organisation').first()).toBeVisible();
    await page.getByText('Betriebsstätten').click();
    await expect(page.getByRole('banner').getByText('Betriebsstätten')).toBeVisible();
    await page.getByText('Abteilungen').click();
    await expect(page.getByRole('banner').getByText('Abteilungen')).toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Mitarbeitende$/ })
        .first()
        .click();
    await expect(page.getByText('Mitarbeiter')).toBeVisible();
    await page.getByText('Arbeitszeitkonten').click();
    await expect(page.getByText('Arbeitzeitkonten')).toBeVisible();
    await page.getByText('Organigramm').click();
    await expect(page.getByRole('banner').getByText('Organigramm')).toBeVisible();
    await page.getByText('Organisationen').click();
    await expect(page.getByRole('banner').getByText('Organisationen')).toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Dashboard$/ })
        .first()
        .click();
    await expect(page.getByText('Dashboard von admin admin')).toBeVisible();
});
