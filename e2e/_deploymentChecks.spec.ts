import { test, expect } from '@playwright/test';

test('login and logout for admin successfull', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL('/login');
    await expect(page.getByLabel('Email', { exact: true })).toBeVisible();
    await expect(page.getByLabel('Passwort', { exact: true })).toBeVisible();
});

test('invalid user login fails', async ({ page }) => {
    await page.goto('/');
    await page.getByLabel('Email', { exact: true }).fill('noUser@user.com');
    await page.getByLabel('Passwort', { exact: true }).fill('user');
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page.getByText('These credentials do not match our records.')).toBeVisible();
});
