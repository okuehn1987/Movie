import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('admin login', async ({ page }) => {
    await page.getByRole('navigation').getByText('Arbeitszeiten').click();
    await expect(page.getByRole('banner').getByText('Arbeitszeiten')).toBeVisible();
    await page.getByRole('row', { name: 'admin admin Gehen 02.12.2024 15:' }).getByRole('button').click();
    await expect(page.getByText('admin admin')).toBeVisible();
});
