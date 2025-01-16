import { test, expect } from '@playwright/test';
import { resetAndSeedDatabase, userLogin } from '../utils';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

//FIXME:this test just checks the absence navigation because worklogs are only shown when there is a person below you in hierarchy to manage on.
test.skip('switches between sites', async ({ page }) => {
    await expect(page.getByText('Dashboard von user user')).toBeVisible();
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page.getByRole('banner').getByText('Abwesenheiten')).toBeVisible();
});

test.skip('ends shift and starts again', async ({ page }) => {
    await page.getByRole('button', { name: 'Kommen' }).click();
    await expect(page.getByText('Arbeitsstatus erfolgreich')).toBeVisible();
    await expect(page.locator('.mdi-timer')).toBeVisible();
    await page.getByRole('button', { name: 'Gehen' }).click();
    await expect(page.getByText('Arbeitsstatus erfolgreich')).toBeVisible();
});
