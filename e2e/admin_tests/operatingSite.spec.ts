import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await page.getByRole('navigation').getByText('Betriebsstätten').click();
    await expect(page.getByRole('banner').getByText('Betriebsstätten')).toBeVisible();
});

test('create operating Site', async ({ page }) => {
    await page.getByRole('row', { name: '# Name Anschrift Stadt Land' }).getByRole('button').click();
    await expect(page.getByText('Betriebsstätte erstellen')).toBeVisible();
    await page.getByLabel('Name').fill('Happys Wonderworld');
    await page.getByLabel('E-Mail').fill('test@test.com');
    await page.getByLabel('Telefonnummer').fill('0123 456789');
    await page.getByTestId('land').click();
    await page.getByText('Deutschland').click();
    await page.getByTestId('bundesland').click();
    await page.getByText('Schleswig-Holstein').click();
    await page.getByLabel('Straße').fill('Teststraße');
    await page.getByLabel('Hausnummer').fill('10');
    await page.getByLabel('PLZ').fill('12345');
    await page.getByLabel('Ort').fill('Testcity');
    await page.getByLabel('Hauptsitz?').check();
    await page.getByRole('button', { name: 'Erstellen' }).click();
    await page.getByRole('dialog').getByRole('button').first().click();
    await expect(page.getByRole('cell', { name: 'Happys Wonderworld' })).toBeVisible();
    await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
});

test('show and edit created operating site', async ({ page }) => {
    await page
        .getByRole('row', { name: /.* Happys Wonderworld/ })
        .getByRole('link')
        .getByRole('button')
        .click();
    await expect(page.getByText('Betriebsstätte Happys')).toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Schleswig-Holstein$/ })
        .first()
        .click();
    await page.getByText('Saarland').click();
    await page.getByRole('button', { name: 'Aktualisieren' }).click();
    await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
    await page.getByRole('banner').getByRole('button').first().click();
    await page.getByRole('row', { name: '20 Happys Wonderworld' }).getByRole('link').getByRole('button').click();
    await expect(page.getByText('Saarland')).toBeVisible();
});

test('add working hours to operating site', async ({ page }) => {
    await page
        .getByRole('row', { name: /.* Happys Wonderworld/ })
        .getByRole('link')
        .getByRole('button')
        .click();
    await page.getByRole('tab', { name: 'Betriebszeiten' }).click();
    await expect(page.getByText('Montag')).toBeVisible();
    await page.getByLabel('Beginn des Arbeitstages').fill('08:00');
    await page.getByLabel('Ende des Arbeitstages').fill('17:00');
    await page.getByRole('button', { name: 'Hinzufügen' }).click();
    await expect(page.getByText('Betriebszeit erfolgreich')).toBeVisible();
    await expect(page.getByRole('main').getByRole('listbox').getByText('Montag')).toBeVisible();
});

test('delete working hours from operating site', async ({ page }) => {
    await page
        .getByRole('row', { name: /.* Happys Wonderworld/ })
        .getByRole('link')
        .getByRole('button')
        .click();
    await page.getByRole('tab', { name: 'Betriebszeiten' }).click();
    await expect(page.getByText('08:00 Uhr - 17:00 Uhr')).toBeVisible();
    await page.getByRole('main').getByRole('link').getByRole('button').click();
    await expect(page.getByText('Betriebszeit erfolgreich gelö')).toBeVisible();
    await expect(page.getByRole('main').getByRole('listbox').getByText('Montag')).not.toBeVisible();
});

test('delete previously created operating Site', async ({ page }) => {
    await page
        .getByRole('row', { name: /.* Happys Wonderworld/ })
        .getByRole('button')
        .nth(1)
        .click();
    await expect(page.getByText('Betriebsstätte löschen')).toBeVisible();
    await page.getByRole('button', { name: 'Löschen' }).click();
    await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Happys Wonderworld' })).not.toBeVisible();
});
