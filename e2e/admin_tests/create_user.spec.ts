//TODO:
import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Mitarbeitende' }).nth(2).click();
    await expect(page).toHaveURL('/user');
});

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Abwesenheiten' }).nth(2).click();
    await expect(page).toHaveURL('/absence');
});
