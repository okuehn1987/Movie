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

test('checks calendar function', async ({ page }) => {
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
test('can request a vacation', async ({ page }) => {
    await page.getByRole('cell', { name: 'user user' }).click();
    await page.getByRole('row', { name: 'user user' }).locator('button').click();
    await page.locator('form').filter({ hasText: 'Abwesenheitsgrund' }).locator('i').click();
    await page.getByText('Urlaub', { exact: true }).click();
    await page.getByLabel('Von').fill('2025-01-10');
    await page.getByLabel('Bis').fill('2025-01-20');
    await page.getByRole('button', { name: 'beantragen' }).click();

    //admin login
    await page.getByRole('button', { name: 'Abmelden' }).click();
    await page.getByLabel('Email').fill('admin@admin.com');
    await page.getByLabel('Passwort').fill('admin');
    await page.getByRole('button', { name: 'Login' }).click();

    //admin checks absences
    await page.getByText('Mitarbeitende').click();
    await page.getByRole('row', { name: 'user user user@user.com /' }).getByRole('link').getByRole('button').click();
    await page.getByRole('tab', { name: 'Abwesenheiten' }).click();
    await expect(page.getByRole('cell', { name: 'Genutzte Urlaubstage für das' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '7 von 0' })).toBeVisible();
    await expect(page.getByText('Genehmigt')).toBeVisible();
});
