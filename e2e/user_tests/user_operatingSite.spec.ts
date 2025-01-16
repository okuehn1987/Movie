import { test, expect } from '@playwright/test';
import { resetAndSeedDatabase, userLogin } from '../utils';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page.getByRole('navigation').getByText('Betriebsstätten').click();
    await expect(page.getByRole('banner').getByText('Betriebsstätten')).toBeVisible();
});

test.skip('tests if operatingSite is visible', async ({ page }) => {
    await expect(page.getByRole('cell', { name: 'delete me ORG' })).toBeVisible();
    await page.getByRole('row', { name: 'delete me ORG lösch mich 666' }).getByRole('button').click();
    await expect(page.getByText('Betriebsstätte delete me ORG')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Kontaktinformationen' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Adresse' })).toBeVisible();
    await page.getByRole('tab', { name: 'Betriebszeiten' }).click();
    await expect(page.getByRole('heading', { name: 'Aktuelle Arbeitszeiten' })).toBeVisible();
    await expect(page.getByText('Montag')).toBeVisible();
    await expect(page.locator('.v-list-item-subtitle').first()).toBeVisible();
});
