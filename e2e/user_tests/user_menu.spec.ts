import { test, expect } from '@playwright/test';
import { resetAndSeedDatabase, userLogin } from '../utils';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

// this test just checks the absence navigation because worklogs are only shown when there is a person below you in hierarchy to manage on.
test('switches between sites', async ({ page }) => {
    await expect(page.getByText('Dashboard von user user')).toBeVisible();
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page.getByRole('banner').getByText('Abwesenheiten')).toBeVisible();
});
