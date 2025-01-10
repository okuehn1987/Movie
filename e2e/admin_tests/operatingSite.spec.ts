// import { test, expect } from '@playwright/test';
// import { adminLogin, resetAndSeedDatabase } from '../utils';

// test.beforeEach('admin login', async ({ page }) => {
//     await resetAndSeedDatabase(page);
//     await adminLogin(page);
//     await page.getByRole('navigation').getByText('Betriebsstätten').click();
//     await expect(page.getByRole('banner').getByText('Betriebsstätten')).toBeVisible();
// });

// test('create operating Site', async ({ page }) => {
//     await page.getByRole('row', { name: 'Name Anschrift Stadt Land' }).getByRole('button').click();
//     await expect(page.getByText('Betriebsstätte erstellen')).toBeVisible();
//     await page.getByLabel('Name').fill('Happys Wonderworld');
//     await page.getByLabel('E-Mail').fill('test@test.com');
//     await page.getByLabel('Telefonnummer').fill('0123 456789');
//     await page.getByTestId('land').click();
//     await page.getByText('Deutschland').click();
//     await page.getByTestId('bundesland').click();
//     await page.getByText('Schleswig-Holstein').click();
//     await page.getByLabel('Straße').fill('Teststraße');
//     await page.getByLabel('Hausnummer').fill('10');
//     await page.getByLabel('PLZ').fill('12345');
//     await page.getByLabel('Ort').fill('Testcity');
//     await page.getByLabel('Hauptsitz?').check();
//     await page.getByRole('button', { name: 'Erstellen' }).click();
//     await page.getByRole('dialog').getByRole('button').first().click();
//     await expect(page.getByRole('cell', { name: 'Happys Wonderworld' })).toBeVisible();
//     await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
// });

// test('show and edit created operating site', async ({ page }) => {
//     await page.getByRole('row', { name: 'delete me ORG lösch mich 666' }).getByRole('link').getByRole('button').click();
//     await expect(page.getByText('delete me ORG')).toBeVisible();
//     await page
//         .locator('div')
//         .filter({ hasText: /^Schleswig-Holstein$/ })
//         .first()
//         .click();
//     await page.getByText('Saarland').click();
//     await page.getByRole('button', { name: 'Aktualisieren' }).click();
//     await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
//     await page.getByRole('banner').getByRole('button').first().click();
//     await page
//         .getByRole('row', { name: /.*delete me ORG/ })
//         .getByRole('link')
//         .getByRole('button')
//         .click();
//     await expect(page.getByText('Saarland')).toBeVisible();
// });

// test('add and delete working hours to operating site', async ({ page }) => {
//     //add working hours
//     await page.getByRole('row', { name: 'delete me ORG lösch mich 666' }).getByRole('link').getByRole('button').click();
//     await page.getByRole('tab', { name: 'Betriebszeiten' }).click();
//     await expect(page.getByRole('main').getByRole('listbox').getByText('Montag')).toBeVisible();
//     await page.getByLabel('Beginn des Arbeitstages').fill('08:00');
//     await page.getByLabel('Ende des Arbeitstages').fill('17:00');
//     await page.getByRole('button', { name: 'Hinzufügen' }).click();
//     await expect(page.getByText('Betriebszeit erfolgreich')).toBeVisible();
//     await expect(page.getByRole('main').getByRole('listbox').getByText('Montag')).toBeVisible();

//     //delete working hours
//     await page.getByRole('tab', { name: 'Betriebszeiten' }).click();
//     await expect(page.getByText('08:00 Uhr - 17:00 Uhr')).toBeVisible();
//     await page.locator('.v-list-item__append > button > .v-btn').first().click();
//     await expect(page.getByText('Betriebszeit erfolgreich gelö')).toBeVisible();
//     await expect(page.getByRole('main').getByRole('listbox').getByText('Montag')).not.toBeVisible();
// });

// test('delete operating Site', async ({ page }) => {
//     await page.getByRole('row', { name: 'delete me ORG lösch mich 666' }).getByRole('button').nth(1).click();
//     await expect(page.getByText('Betriebsstätte löschen')).toBeVisible();
//     await page.getByRole('button', { name: 'Löschen' }).click();
//     await expect(page.getByText('Betriebsstätte erfolgreich')).toBeVisible();
//     await expect(page.getByRole('cell', { name: 'delete me ORG' })).not.toBeVisible();
// });
