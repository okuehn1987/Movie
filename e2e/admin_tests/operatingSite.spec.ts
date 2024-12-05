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
    await page.getByLabel('Straße').fill('Teststraße');
    await page.getByLabel('Hausnummer').fill('10');
    await page.getByLabel('PLZ').fill('12345');
    await page.getByLabel('Ort').fill('Testcity');
    await page.getByLabel('Bundesland').fill('Testholstein');
    await page.getByLabel('Land', { exact: true }).fill('Testland');
    await page.getByLabel('Hauptsitz?').check();
    await page.getByRole('button', { name: 'Erstellen' }).click();
    await page.getByRole('dialog').getByRole('button').first().click();
    await expect(page.getByRole('cell', { name: 'Happys Wonderworld' })).toBeVisible();
    await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
});

test('delete previously created operating Site', async ({ page }) => {
    //FIXME:currently not working because of # with numbers
    await page.getByRole('row', { name: '13  Happys Wonderworld' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Betriebsstätte löschen')).toBeVisible();
    await page.getByRole('button', { name: 'Löschen' }).click();
    await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Happys Wonderworld' })).not.toBeVisible();
});
