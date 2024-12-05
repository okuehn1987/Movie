import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test.beforeEach('absence', async ({ page }) => {
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page.getByRole('banner').getByText('Abwesenheiten')).toBeVisible();
    //TODO: find out how to not nth child for test
    await page.locator('tr:nth-child(32) > td:nth-child(2)').click();
});
