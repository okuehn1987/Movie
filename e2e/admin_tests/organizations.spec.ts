import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('creates an organization', async ({ page }) => {
    await page
        .locator('div')
        .filter({ hasText: /^Organisationen$/ })
        .first()
        .click();
    await page.getByRole('row', { name: 'id owner_id name created_at' }).getByRole('button').click();
    await expect(page.getByText('Organisation erstellen')).toBeVisible();
    await page.getByLabel('Firmenname').fill('Grumpy Tests Ltd.');
    await page.getByLabel('Standortname').fill('Teststadt');
    await page.getByTestId('organizationLand').click();
    await page.getByText('Deutschland').click();
    await page.getByTestId('organizationProvince').click();
    await page.getByText('Schleswig-Holstein').click();
    await page.getByLabel('Straße').fill('Teststraße');
    await page.getByLabel('Hausnummer').fill('14');
    await page.getByLabel('PLZ').fill('12345');
    await page.getByLabel('Ort', { exact: true }).fill('Testhausen');
    await page.getByLabel('Vorname').fill('Grumpy');
    await page.getByLabel('Nachname').fill('Grumpicus');
    await page.getByLabel('E-Mail').fill('testtest@test.com');
    await page.getByLabel('Password').fill('test');
    await page.getByLabel('Geburtsdatum').fill('2024-12-02');
    await page.getByRole('button', { name: 'Erstellen' }).click();
    await page.getByRole('dialog').getByRole('button').first().click();
    await expect(page.getByText('Organisation erfolgreich')).toBeVisible();
});
