import { test, expect } from '@playwright/test';
import { adminLogin } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Mitarbeitende' }).nth(2).click();
    await expect(page).toHaveURL('/user');
});

test('creates a new user', async ({ page }) => {
    await page
        .getByRole('row', { name: /.*Vorname Nachname Email/ })
        .getByRole('button')
        .click();

    //Allgemeine Angaben
    await page.getByLabel('Vorname').fill('Test');
    await page.getByLabel('Nachname').fill('Tester');
    await page.getByLabel('Email').fill('test@test.de');
    await page.getByLabel('Passwort').click();
    await page.getByLabel('Passwort').fill('test');
    await page.getByLabel('Geburtsdatum').fill('1111-11-11');
    await page.getByLabel('Trage die wöchentliche').click();
    await page.getByLabel('Trage die wöchentliche').fill('40');
    await page.getByRole('combobox').locator('i').click();
    await page.getByText('Dienstag').click();
    await page.getByText('Donnerstag').click();
    await page.getByText('Freitag').click();
    await page.getByRole('button', { name: 'Weiter' }).click();

    //Adresse
    await page.getByLabel('Straße').fill('test lane');
    await page.getByLabel('Hausnummer').fill('11');
    await page.getByLabel('Postleitzahl').fill('11111');
    await page.getByLabel('Ort', { exact: true }).fill('Testhausen');
    await page
        .locator('div')
        .filter({ hasText: /^BundeslandBundesland$/ })
        .first()
        .click();
    await page.getByTestId('land').click();
    await page.getByRole('option', { name: 'Deutschland' }).click();
    await page.getByTestId('federal_state').click();
    await page.getByRole('option', { name: 'Berlin' }).click();
    await page.getByRole('button', { name: 'Weiter' }).click();

    //Berechtigungen
    // organisation part
    await expect(page.getByRole('heading', { name: 'Organisation' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Betriebsstätte' })).toBeVisible();
    await page.getByTestId('permissionSelector').first().click();
    await page.getByText('Mitarbeitende verwalten').click();
    await page.getByText('Zeitkonten verwalten').click();
    await expect(page.getByText('Zeitkonten verwaltenMitarbeitende verwalten')).toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Lesen$/ })
        .first()
        .click();
    await expect(
        page
            .getByRole('dialog')
            .locator('div')
            .filter({ hasText: /^Lesen$/ })
            .nth(2),
    ).toBeVisible();
    await page.getByText('Schreiben').click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Schreiben$/ })
            .first(),
    ).toBeVisible();

    // operating Site part
    await page.getByTestId('userOperatingSiteSelection').first().click();
    await page.getByText('delete me ORG').click();
    await expect(page.getByTestId('userOperatingSiteSelection').locator('div').filter({ hasText: 'delete me ORG' }).nth(3)).toBeVisible();
    await page.getByTestId('permissionSelector').nth(1).click();
    await page.getByText('Zeitkorrekturen verwalten').click();
    await page.getByText('Abwesenheiten verwalten').click();
    await page.getByText('Abwesenheiten verwaltenZeitkorrekturen verwalten').click();
    await expect(page.getByText('Abwesenheiten verwaltenZeitkorrekturen verwalten')).toBeVisible();
    await expect(page.getByText('Lesen').nth(1)).toBeVisible();
    await page.getByText('Lesen').nth(2).click();
    await page.getByRole('option', { name: 'Schreiben' }).click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Schreiben$/ })
            .nth(2),
    ).toBeVisible();

    // group part
    await page.getByTestId('userGroupSelection').click();
    await page.getByRole('option', { name: /.*/ }).last().click();

    // supervisor part
    await page.getByTestId('userSupervisorSelection').click();
    await page.getByText('admin admin').click();
    await page.getByLabel('Ist ein Vorgesetzter').check();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Mitarbeitenden erfolgreich')).toBeVisible;
    await expect(page.getByRole('cell', { name: 'Test', exact: true })).toBeVisible;
    await expect(page.getByRole('cell', { name: 'test@test.de' })).toBeVisible;
});

test('changes information of previously added user', async ({ page }) => {
    // Allgemeine Angaben
    await expect(page.getByRole('cell', { name: 'Test', exact: true })).toBeVisible();
    await page.getByRole('row', { name: 'Test Tester test@test.de' }).getByRole('link').getByRole('button').click();
    await expect(page.getByText('Test Tester bearbeiten')).toBeVisible;
    await page.getByLabel('Vorname').fill('Changed');
    await page.getByLabel('Email').fill('changed@changed.de');
    await page.getByLabel('Geburtsdatum').fill('1111-11-13');
    await page.getByRole('button', { name: 'Weiter' }).click();

    // Adresse
    await page.getByLabel('Straße').fill('changedbytest');
    await page.getByRole('button', { name: 'Weiter' }).click();

    // Berechtigungen
    await page.getByText('Mitarbeitende verwaltenZeitkonten verwalten').click();
    await page.getByRole('option', { name: 'Zeitkonten verwalten' }).click();
    await page
        .locator('div')
        .filter({ hasText: /^OrganisationsrechteMitarbeitende verwalten$/ })
        .locator('div')
        .first()
        .click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Mitarbeitende verwalten$/ })
            .first(),
    ).toBeVisible();
    await expect(page.getByText('Zeitkonten verwalten').nth(1)).not.toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Lesen$/ })
        .first()
        .click();
    await page.getByRole('option', { name: 'Schreiben' }).click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Schreiben$/ })
            .first(),
    ).toBeVisible();
    await page.getByText('Zeitkorrekturen verwaltenAbwesenheiten verwalten').click();
    await page.getByRole('option', { name: 'Zeitkorrekturen verwalten' }).click();
    await page
        .locator('div')
        .filter({ hasText: /^BetriebstättenrechteAbwesenheiten verwalten$/ })
        .locator('div')
        .first()
        .click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Abwesenheiten verwalten$/ })
            .first(),
    ).toBeVisible();
    await expect(page.getByText('Zeitkorrekturen verwalten').nth(1)).not.toBeVisible();
    await page
        .locator('div')
        .filter({ hasText: /^Lesen$/ })
        .first()
        .click();
    await page.getByRole('option', { name: 'Schreiben' }).click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Schreiben$/ })
            .nth(2),
    ).toBeVisible();
    await page.getByTestId('permissionSelector').last().click();
    await page.getByText('Zeitkontovarianten verwalten').click();
    await page
        .locator('div')
        .filter({ hasText: /^AbteilungsrechteZeitkontovarianten verwalten$/ })
        .locator('div')
        .first()
        .click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Zeitkontovarianten verwalten$/ })
            .first(),
    ).toBeVisible();
    await expect(page.getByText('Zeitkontovarianten verwalten').nth(1)).toBeVisible();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Mitarbeitenden erfolgreich')).toBeVisible();

    // checks if updated information are visible
    await expect(page.getByText('Changed Tester bearbeiten')).toBeVisible();
    await page.getByRole('banner').getByRole('button').first().click();
    await expect(page.getByRole('cell', { name: 'Changed', exact: true })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'changed@changed.de' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '13.11.1111' })).toBeVisible();
});

test('creates a time_account and deletes it', async ({ page }) => {
    // creates account with hours on it
    await page.getByRole('row', { name: 'Changed Tester changed@' }).getByRole('link').getByRole('button').click();
    await page.getByRole('tab', { name: 'Arbeitszeitkonten' }).click();
    await page.getByRole('row', { name: 'Name Überstunden Limit Typ' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Neues Arbeitszeitkonto')).toBeVisible();
    await page.getByLabel('Name', { exact: true }).fill('This will be deleted');
    await page.getByLabel('Startbetrag in Stunden').fill('10');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Arbeitszeitkonto erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This will be deleted' })).toBeVisible();

    // trys to delete account with hours still on
    await page.getByRole('row', { name: 'This will be deleted 10 80' }).getByRole('button').nth(2).click();
    await expect(page.getByText('Konto This will be deleted lö')).toBeVisible();
    await expect(page.getByText('Konten können nur gelöscht')).toBeVisible();

    // deletes hours on account
    await page.getByRole('dialog').getByRole('button').click();
    await page.getByRole('row', { name: 'This will be deleted 10 80' }).getByRole('button').first().click();
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

    // deletes account
    await page.getByRole('row', { name: 'This will be deleted 0 80' }).getByRole('button').nth(2).click();
    await page.getByText('Konto This will be deleted lö').click();
    await page.getByText('Sind Sie sich sicher das').click();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Arbeitszeitkonto erfolgreich')).toBeVisible();
});

test('tests time_account function', async ({ page }) => {
    await page.getByRole('row', { name: 'Changed Tester changed@' }).getByRole('link').getByRole('button').click();
    await expect(page.getByText('Changed Tester bearbeiten')).toBeVisible();
    await page.getByRole('tab', { name: 'Arbeitszeitkonten' }).click();

    //adds hours to standard time account
    await expect(page.getByRole('cell', { name: 'Standardkonto' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '80' })).toBeVisible();
    await page.getByRole('row', { name: 'Standardkonto 0 80 Standard' }).getByRole('button').first().click();
    await expect(page.getByText('Stunden für Konto')).toBeVisible();
    await page.getByLabel('Stunden', { exact: true }).fill('40');
    await page.getByLabel('Beschreibung').fill('This is a test');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Changed Tester bearbeiten')).toBeVisible();
    await expect(page.getByText('Transaktion erfolgreich')).toBeVisible();
    await expect(page.getByText('Stunden für Konto')).not.toBeVisible();
    await expect(page.locator('span').filter({ hasText: '40' }).first()).toBeVisible();

    //updates time_account information
    await page.getByRole('row', { name: 'Standardkonto 40 80 Standard' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Einstellungen für Konto')).toBeVisible();
    await page.getByLabel('Name', { exact: true }).fill('StandardkontoTest');
    await page.getByLabel('Limit in Stunden').fill('50');
    await expect(page.getByText('entspricht 1.25x wöchentliche')).toBeVisible();
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
    await page.getByTestId('timeAccountTransactionDestinationAccount').click();
    await page.getByRole('option', { name: 'Testkonto' }).click();
    await page.getByLabel('Stunden', { exact: true }).fill('10');
    await page.getByLabel('Beschreibung').fill('This is another test');
    await expect(page.getByText('Die Beschreibung ist für die')).toBeVisible();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Transaktion erfolgreich')).toBeVisible();
    await expect(page.getByText('Stundentransaktion durchführen')).not.toBeVisible();
    await page.locator('span').filter({ hasText: /^20$/ }).first().click();
});

test('tests visibility of transactions', async ({ page }) => {
    await page.getByRole('row', { name: 'Changed Tester changed@' }).getByRole('link').getByRole('button').click();
    await page.getByRole('tab', { name: 'Transaktionen' }).click();
    await expect(page.getByText('Changed Tester bearbeiten')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'StandardkontoTest' }).first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Testkonto' }).first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This is another test' })).toBeVisible();

    await expect(page.getByRole('cell', { name: 'Initialer Kontostand' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '+ 10' }).locator('span').first()).toBeVisible();

    await expect(page.getByRole('cell', { name: 'StandardkontoTest' }).nth(1)).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This is a test' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '+ 40' }).locator('span').first()).toBeVisible();
});

test('trys organigramm', async ({ page }) => {
    await page.getByRole('row', { name: 'Changed Tester changed@' }).getByRole('link').getByRole('button').click();
    await expect(page.getByText('Changed Tester bearbeiten')).toBeVisible();
    await page.getByRole('tab', { name: 'Organigramm' }).click();
    await expect(page.getByText('Changed Tester bearbeiten')).toBeVisible();
    await expect(page.getByText('Changed Tester', { exact: true })).toBeVisible();
});

test('deletes previously created user', async ({ page }) => {
    await page.getByRole('row', { name: 'Changed Tester changed@' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Mitarbeiter löschen')).toBeVisible();
    await page.getByRole('button', { name: 'Löschen' }).click();
    await expect(page.getByText('Mitarbeitenden erfolgreich')).toBeVisible();
    await expect(page.getByText('Mitarbeiter löschen')).not.toBeVisible();
    await expect(page.getByRole('cell', { name: 'Changed' })).not.toBeVisible();
});
