import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase, userLogin } from '../utils';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('withdraws pre seeded time correction and ads another, accepts as admin and checks as user if successfull', async ({ page }) => {
    //withdraws seeded time correction
    await page.getByRole('button').nth(3).click();
    await expect(page.getByRole('cell', { name: 'Beantragt' })).toBeVisible();
    // await page.getByRole('row', { name: '14.01.2025 06:06 14.01.2025' }).getByRole('button').click();
    await page.getByTestId('entryToWorkLog').click();
    await page.getByRole('button', { name: 'Antrag zurückziehen' }).click();
    await expect(page.getByText('Antrag auf Zeitkorrektur')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Nicht vorhanden' })).toBeVisible();

    // adds time correction again
    await page.getByTestId('entryToWorkLog').click();
    await page.getByTestId('userTimeCorrectionStartTime').getByLabel('Start').fill('10:30');
    await page.getByTestId('userTimeCorrectionEndTime').getByLabel('Ende').fill('18:30');
    await page.getByRole('button', { name: 'Korrektur beantragen' }).click();
    await expect(page.getByText('Korrektur der Arbeitszeit')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Beantragt' })).toBeVisible();

    // admin logs in, tests notification button and accepts time correction
    await page.getByRole('button', { name: 'Abmelden' }).click();
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page
        .locator('div')
        .filter({ hasText: /^Anträge$/ })
        .first()
        .click();
    await page.getByRole('cell', { name: 'user user' }).click();
    await expect(page.getByText('Zeitkorrektur von user user')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Alter Stand:' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Neuer Stand:' })).toBeVisible();
    await page.getByRole('button', { name: 'Akzeptieren' }).click();
    await expect(page.getByText('Zeitkorrektur erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'keine Zeitkorrekturen' })).toBeVisible();
    await expect(page.getByText('Zeitkorrektur von user user')).not.toBeVisible();

    //user logs in to see if time correction accepted status is present.
    await page.getByRole('button', { name: 'Abmelden' }).click();
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page.getByRole('button').nth(3).click();
    await expect(page.getByRole('cell', { name: '10:30' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Akzeptiert' })).toBeVisible();
});
