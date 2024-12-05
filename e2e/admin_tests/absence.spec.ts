import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Abwesenheiten' }).nth(2).click();
    await expect(page).toHaveURL('/absence');
});

test('checks calendar function', async ({ page }) => {
    await expect(page.getByRole('heading', { name: 'Dezember' })).toBeVisible();
    await page.locator('div:nth-child(3) > button').first().click();
    await expect(page.getByRole('heading', { name: 'Januar' })).toBeVisible();
    await page.locator('button:nth-child(2)').first().click();
    await expect(page.getByRole('heading', { name: 'Dezember' })).toBeVisible();
    await page.locator('div:nth-child(3) > button:nth-child(2)').click();
    await expect(page.getByRole('heading', { name: 'Dezember' })).toBeVisible();
    await page.locator('.d-flex > button').first().click();
    await expect(page.getByRole('heading', { name: 'Dezember' })).toBeVisible();
});

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
    await page.getByLabel('Von', { exact: true }).click();
    await page.getByRole('button', { name: '12' }).click();
    await page.getByRole('button', { name: 'OK' }).click();
    await page.getByLabel('Bis', { exact: true }).click();
    await page.getByRole('button', { name: '15' }).click();
    await page.getByRole('button', { name: 'OK' }).click();
    await page.getByRole('button', { name: 'beantragen' }).click();
    await expect(page.getByText('Abwesenheit beantragt.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'BU' }).first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'BU' }).nth(3)).toBeVisible();
});
