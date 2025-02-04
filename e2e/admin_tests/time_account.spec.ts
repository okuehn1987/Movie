import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Mitarbeitende' }).nth(2).click();
    await expect(page).toHaveURL('/user');

    //creates time account type because otherwise not testable
    await page.getByText('Organisation', { exact: true }).click();
    await page.getByRole('tab', { name: 'Zeitkontoeinstellungen' }).click();
    await page.getByRole('row', { name: 'Art Berechnungszeitraum' }).getByRole('button').click();
    await expect(page.getByText('Neue Variante Erstellen')).toBeVisible();
    await page.getByLabel('Bezeichnung').fill('TestAcc');
    await page
        .locator('div')
        .filter({ hasText: /^Unbegrenzt$/ })
        .first()
        .click();
    await page.getByText('Quartalsweise').click();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Neue Variante erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'TestAcc' })).toBeVisible();

    await page.getByText('Mitarbeitende').click();
});

test('creates a time_account and deletes it', async ({ page }) => {
    // creates account with hours on it
    await page.getByRole('row', { name: 'user user user@' }).getByRole('link').getByRole('button').click();
    await page.getByRole('tab', { name: 'Arbeitszeitkonten' }).click();
    await page.getByRole('row', { name: 'Name Überstunden Limit Typ' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Neues Arbeitszeitkonto')).toBeVisible();
    await page.getByLabel('Name', { exact: true }).fill('This will be deleted');
    await page.getByText('TestAcc (Quartalsweise)').click();
    await page.getByLabel('Startbetrag in Stunden').fill('10');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Arbeitszeitkonto erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This will be deleted' })).toBeVisible();

    // trys to delete account with hours still on
    await page.getByRole('row', { name: 'This will be deleted' }).getByRole('button').nth(2).click();
    await expect(page.getByText('Konto This will be deleted lö')).toBeVisible();
    await expect(page.getByText('Konten können nur gelöscht')).toBeVisible();

    // deletes hours on account
    await page.getByRole('dialog').getByRole('button').click();
    await page.getByRole('row', { name: 'This will be deleted' }).getByRole('button').first().click();
    await expect(page.getByText('Stunden für Konto This will')).toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Stunden hinzufügen$/ })
        .first()
        .click();
    await page.getByRole('option', { name: 'Stunden abziehen' }).click();
    await page.getByLabel('Stunden', { exact: true }).fill('10');
    await page.getByLabel('Beschreibung').fill('Test deletion');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Transaktion erfolgreich')).toBeVisible();
    await expect(page.locator('#app span').filter({ hasText: '0' }).nth(1)).toBeVisible();

    // deletes account and checks if its successfully deleted
    await page.getByRole('row', { name: 'This will be deleted' }).getByRole('button').nth(2).click();
    await page.getByText('Konto This will be deleted lö').click();
    await page.getByText('Sind Sie sich sicher das').click();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Arbeitszeitkonto erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This will be deleted' })).not.toBeVisible();
    await page.getByRole('tab', { name: 'Transaktionen' }).click();
    await expect(page.getByRole('cell', { name: 'Test deletion' })).toBeVisible();
});

test('tests time_account function', async ({ page }) => {
    await page.getByRole('row', { name: 'user user user@' }).getByRole('link').getByRole('button').click();
    await page.getByRole('tab', { name: 'Arbeitszeitkonten' }).click();

    //adds hours to standard time account
    await page.getByRole('row', { name: 'Gleitzeitkonto' }).getByRole('button').first().click();
    await expect(page.getByText('Stunden für Konto')).toBeVisible();
    await page.getByLabel('Stunden', { exact: true }).fill('40');
    await page.getByLabel('Beschreibung').fill('This is a test');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Transaktion erfolgreich')).toBeVisible();
    await expect(page.getByText('Stunden für Konto')).not.toBeVisible();
    await expect(page.locator('span').filter({ hasText: '40' }).first()).toBeVisible();

    //updates time_account information
    await page.getByRole('row', { name: 'Gleitzeitkonto' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Einstellungen für Konto')).toBeVisible();
    await page.getByLabel('Name', { exact: true }).fill('StandardkontoTest');
    await page.getByLabel('Limit in Stunden').fill('50');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Arbeitszeitkonto erfolgreich')).toBeVisible();
    await expect(page.getByText('Einstellungen für Konto')).not.toBeVisible();
    await expect(page.getByRole('cell', { name: '50' })).toBeVisible();

    // creates a new time account
    await page.getByRole('row', { name: 'Name Überstunden Limit Typ' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Neues Arbeitszeitkonto')).toBeVisible();
    await page.getByLabel('Name', { exact: true }).fill('Testkonto');
    await page.getByLabel('Limit in Stunden').fill('40');
    await page.getByLabel('Startbetrag in Stunden').fill('10');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Arbeitszeitkonto erfolgreich')).toBeVisible();
    await expect(page.getByText('Neues Arbeitszeitkonto')).not.toBeVisible();

    // tests transaction function
    await page.getByRole('row', { name: 'Name Überstunden Limit Typ' }).getByRole('button').first().click();
    await expect(page.getByText('Stundentransaktion durchführen')).toBeVisible();
    await page.getByTestId('timeAccountTransactionStartAccount').click();
    await page.getByRole('option', { name: 'StandardkontoTest' }).click();
    await page.getByTestId('timeAccountTransactionDestinationAccount').getByLabel('Öffnen').fill('t');

    // await page.getByRole('option', { name: 'Testkonto' }).click();
    await page.getByLabel('Stunden', { exact: true }).fill('10');
    await page.getByLabel('Beschreibung').fill('This is another test');
    await expect(page.getByText('Die Beschreibung ist für die')).toBeVisible();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Transaktion erfolgreich')).toBeVisible();
    await expect(page.getByText('Stundentransaktion durchführen')).not.toBeVisible();

    // tests visibility of transactions
    await page.getByRole('tab', { name: 'Transaktionen' }).click();
    await expect(page.getByRole('cell', { name: 'Testkonto' }).first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This is another test' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Initialer Kontostand' }).first()).toBeVisible();
    await expect(page.getByRole('cell', { name: '+ 10' }).locator('span').first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This is a test' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '+ 40' }).locator('span').first()).toBeVisible();
});
