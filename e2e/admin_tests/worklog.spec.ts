import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await page.getByRole('navigation').getByText('Arbeitszeiten').click();
    await expect(page).toHaveURL('/workLog');
});

test('admin login', async ({ page }) => {
    await page
        .getByRole('row', { name: /admin admin Gehen .*/ })
        .getByRole('button')
        .click();
    await expect(page.getByText('admin admin')).toBeVisible();
    await page.getByRole('row').last().getByRole('button').click();
    await expect(page.getByText('Zeitkorrektur')).toBeVisible();
    await page.locator('.v-field__input').first().click();
    await page.getByRole('button', { name: '6', exact: true }).click();
    await page.getByRole('button', { name: 'OK' }).click();
    await page.locator('#input-92').fill('16:00');
    await page.locator('div:nth-child(3) > .v-input > .v-input__control > .v-field > .v-field__field > .v-field__input').click();
    await page.getByRole('button', { name: '6', exact: true }).click();
    await page.getByRole('button', { name: 'OK' }).click();
    await page.locator('#input-97').fill('12:00');
    await page.getByLabel('Homeoffice').check();
    await page.getByRole('button', { name: 'Korrektur beantragen' }).click();
    await expect(page.getByText('Korrektur der Arbeitszeit')).toBeVisible();
    await expect(page.getByRole('cell', { name: '06.12.2024 16:' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Ja' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Akzeptiert' })).toBeVisible();
});
