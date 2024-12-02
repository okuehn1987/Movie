import { test, expect } from '@playwright/test';

test('login and logout for admin successfull', async ({ page }) => {
    await page.setViewportSize({ width: 1600, height: 900 });
    await page.goto('/');
    await page.getByLabel('Email').fill('admin@admin.com');
    await page.getByLabel('Passwort').fill('admin');
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page).toHaveURL('/dashboard');
    await page.getByRole('button', { name: 'Abmelden' }).click();
    await expect(page).toHaveURL('/login');
});

test('invalid user login fails', async ({ page }) => {
    await page.setViewportSize({ width: 1600, height: 900 });
    await page.goto('/');
    await page.getByLabel('Email', { exact: true }).fill('noUser@user.com');
    await page.getByLabel('Passwort', { exact: true }).fill('user');
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page).not.toHaveURL('/dashboard');
});
