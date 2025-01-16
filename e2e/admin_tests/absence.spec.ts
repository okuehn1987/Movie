import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';
import { DateTime } from 'luxon';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Abwesenheiten' }).nth(2).click();
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

//TODO:absences can be stacked in data base
test('creates an absence in the calendar', async ({ page }) => {
    await page.getByRole('row', { name: 'admin admin' }).locator('button').click();
    await expect(page.getByText('Abwesenheit beantragen')).toBeVisible();
    await page.locator('.v-field__input').first().click();
    await page.getByText('Bildungsurlaub').click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Bildungsurlaub$/ })
            .first(),
    ).toBeVisible();
    await page.getByLabel('Von').fill('2025-01-09');
    await page.getByLabel('Bis').fill('2025-01-13');
    await page.getByRole('button', { name: 'beantragen' }).click();
    await expect(page.getByText('Abwesenheit beantragt.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'BU' }).first()).toBeVisible();
});
