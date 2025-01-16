import { test, expect } from '@playwright/test';
import { resetAndSeedDatabase, userLogin } from '../utils';
import { DateTime } from 'luxon';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page).toHaveURL('/absence');
});

const date = DateTime.now().setLocale('de-DE');

test.skip('checks calendar function', async ({ page }) => {
    await expect(page.getByRole('heading', { name: date.toFormat('MMMM yyyy') })).toBeVisible();
    await page.locator('div:nth-child(3) > button').first().click();
    await expect(page.getByRole('heading', { name: date.plus({ month: 1 }).toFormat('MMMM yyyy') })).toBeVisible();
    await page.locator('button:nth-child(2)').first().click();
    await expect(page.getByRole('heading', { name: date.toFormat('MMMM yyyy') })).toBeVisible();
    await page.locator('div:nth-child(3) > button:nth-child(2)').click();
    await expect(page.getByRole('heading', { name: date.plus({ year: 1 }).toFormat('MMMM yyyy') })).toBeVisible();
    await page.locator('.d-flex > button').first().click();
    await expect(page.getByRole('heading', { name: date.toFormat('MMMM yyyy') })).toBeVisible();
});

//TODO: absences can be stacked, also in database, no edit nor delete option
test.skip('tests absence entry button', async ({ page }) => {
    await page.getByRole('row', { name: 'user user' }).locator('button').click();
    await expect(page.getByText('Abwesenheit beantragen')).toBeVisible();
    await page.locator('.v-field__input').first().click();
    await page.getByRole('option', { name: 'Bildungsurlaub' }).click();
    await page.getByLabel('Von').fill('2025-01-09');
    await page.getByLabel('Bis').fill('2025-01-13');
    await page.getByRole('button', { name: 'beantragen' }).click();
    await expect(page.getByText('Abwesenheit beantragen')).not.toBeVisible();
});
