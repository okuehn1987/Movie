import { test, expect } from '@playwright/test';
import { resetAndSeedDatabase, userLogin } from '../utils';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('switches between sites', async ({ page }) => {
    await expect(page.getByText('Dashboard von user user')).toBeVisible();
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page.getByRole('banner').getByText('Abwesenheiten')).toBeVisible();
    await page.getByText('Arbeitszeiten').click();
    await expect(page.getByRole('banner').getByText('Arbeitszeiten')).toBeVisible();
});
