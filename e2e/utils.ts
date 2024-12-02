import { expect, Page } from '@playwright/test';

export async function adminLogin(page: Page) {
    await page.goto('/');
    await page.getByLabel('Email').fill('admin@admin.com');
    await page.getByLabel('Passwort').fill('admin');
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page).toHaveURL('/dashboard');
}

// export async function userLogin(page: Page) {}
