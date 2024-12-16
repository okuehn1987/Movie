import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page.getByText('Abteilungen').click();
    await expect(page).toHaveURL('/group');
});

test('creates new group', async ({ page }) => {
    await page.getByRole('row', { name: '# Abteilungsname Mitarbeitende' }).getByRole('button').click();
    await expect(page.getByText('Abteilung erstellen')).toBeVisible();
    await page.getByLabel('Abteilungsname').fill('Test');
    await page.getByTestId('employeeGroupAssignment').click();
    await page.getByText('user user').click();
    await page.getByTestId('employeeGroupAssignment').locator('div').filter({ hasText: 'user user' }).nth(3).click();
    await page.getByRole('button', { name: 'Erstellen' }).click();
    await expect(page.getByText('Gruppe erfolgreich erstellt.')).toBeVisible();
    await expect(page.getByRole('cell', { name: '4' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Test' })).toBeVisible();
});
