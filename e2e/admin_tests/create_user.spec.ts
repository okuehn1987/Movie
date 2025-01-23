import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';
import { php } from '../laravel-helpers';
import { DateTime } from 'luxon';

const date = DateTime.now().setLocale('de-DE');

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

test('creates and deletes a new user', async ({ page, browserName }) => {
    // test.slow(browserName === 'webkit', 'This test is slow in Safari');
    test.setTimeout(120_000);
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
    await page.getByLabel('Geburtsdatum').fill('1970-11-11');

    //Adresse
    await page.getByLabel('Straße').fill('test lane');
    await page.getByLabel('Hausnummer').fill('11');
    await page.getByLabel('Postleitzahl').fill('11111');
    await page.getByLabel('Ort (optional)', { exact: true }).fill('Testhausen');
    await page
        .locator('div')
        .filter({ hasText: /^BundeslandBundesland$/ })
        .first()
        .click();
    await page.getByTestId('land').click();
    await page.getByRole('option', { name: 'Deutschland' }).click();
    await page.getByTestId('federal_state').click();
    await page.getByRole('option', { name: 'Berlin' }).click();

    //Wochenarbeitszeit
    await page.getByRole('row', { name: 'Stunden pro Woche Aktiv seit' }).getByRole('button').click();
    await page.getByTestId('userWorkingHours-hours').getByLabel('').fill('40');
    await page
        .getByTestId('userWorkingHours-since')
        .getByLabel('')
        .fill(date.minus({ day: 5 }).toFormat('yyyy-MM-dd'));

    //Arbeitswoche
    await page.getByRole('row', { name: 'Beschäftigungstage Aktiv seit' }).getByRole('button').click();
    await page.getByTestId('userWorkingDays').click();
    await page.getByText('Montag').click();
    await page.getByText('Mittwoch').click();
    await page.getByText('Donnerstag').click();
    await page.getByText('Freitag').click();
    await page.getByTestId('userWorkingDays').click();
    await page
        .getByTestId('userWorkingDays-since')
        .getByLabel('')
        .fill(date.minus({ day: 5 }).toFormat('yyyy-MM-dd'));

    //Urlaubstage
    await page.getByRole('row', { name: 'Anzahl der Urlaubstage Aktiv' }).getByRole('button').click();
    await page.getByTestId('userLeaveDays').getByLabel('').fill('40');
    await page
        .getByTestId('userLeaveDays-since')
        .getByLabel('')
        .fill(date.minus({ day: 5 }).toFormat('yyyy-MM'));

    //Homeoffice
    await page.getByLabel('Darf der Mitarbeitende').check();
    await page.getByLabel('Homeoffice Stunden pro Woche').click();
    await page.getByLabel('Homeoffice Stunden pro Woche').fill('20');

    //Organisation
    await page
        .locator('div')
        .filter({ hasText: /^Organisation verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByText('Schreiben').click();
    await page
        .locator('div')
        .filter({ hasText: /^Abteilungen verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByText('Lesen').click();

    //Betriebsstätte
    await page.getByTestId('userOperatingSiteSelection').locator('i').click();
    await page.getByText('delete me ORG').click();
    await page
        .getByTestId('userOperatingSitePermissions')
        .locator('div')
        .filter({ hasText: /^Abwesenheiten verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByRole('option', { name: 'Lesen' }).click();
    await page
        .getByTestId('userOperatingSitePermissions')
        .locator('div')
        .filter({ hasText: /^Zeitkonten verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByRole('option', { name: 'Schreiben' }).click();

    //Abteilung
    const group = await php({ page, command: 'App\\Models\\Group::first()->name' });
    await page.getByTestId('userGroupSelection').locator('i').click();
    await page.getByRole('option', { name: group }).click();
    await page
        .getByTestId('userGroupPermissions')
        .locator('div')
        .filter({ hasText: /^Zeitkonten verwaltenKeine Rechte$/ })
        .locator('div')
        .first()
        .click();
    await page.getByRole('option', { name: 'Lesen' }).click();

    //Vorgesetzter
    await page.getByTestId('userSupervisorSelection').locator('i').click();
    await page.getByRole('option', { name: 'admin admin' }).click();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Mitarbeitenden erfolgreich')).toBeVisible();

    //deletes previously created user
    await page.getByRole('row', { name: 'Test Tester' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Mitarbeiter löschen')).toBeVisible();
    await page.getByRole('button', { name: 'Löschen' }).click();
    await expect(page.getByText('Mitarbeitenden erfolgreich')).toBeVisible();
    await expect(page.getByText('Mitarbeiter löschen')).not.toBeVisible();
    await expect(page.getByRole('cell', { name: 'Test Tester' })).not.toBeVisible();
});

test('changes seeded user', async ({ page }) => {
    test.setTimeout(120_000);
    // Allgemeine Angaben
    await expect(page.getByRole('cell', { name: 'user', exact: true }).first()).toBeVisible();
    await page.getByRole('row', { name: 'user user user@user.com' }).getByRole('link').getByRole('button').click();
    await expect(page.getByText('user user bearbeiten')).toBeVisible;
    await page.getByLabel('Vorname').fill('Changed');
    await page.getByLabel('Email').fill('changed@changed.de');

    // Wochenarbeitszeit
    await page.getByRole('row', { name: 'Stunden pro Woche Aktiv seit' }).getByRole('button').click();
    await page.getByTestId('userWorkingHours-hours').getByLabel('').nth(1).fill('35');
    await page
        .getByTestId('userWorkingHours-since')
        .getByLabel('')
        .nth(1)
        .fill(date.plus({ day: 2 }).toFormat('yyyy-MM-dd'));
    //TODO: add check if things in past cannot be saved, use reload window

    //Arbeitswoche
    await page.getByRole('row', { name: 'Beschäftigungstage Aktiv seit' }).getByRole('button').click();
    await page.getByTestId('userWorkingDays').nth(1).click();
    await page.getByText('Samstag').click();
    await page.getByText('Sonntag').click();
    await page.getByTestId('userWorkingDays').nth(1).click();
    await page
        .getByTestId('userWorkingDays-since')
        .getByLabel('')
        .nth(1)
        .fill(date.plus({ day: 2 }).toFormat('yyyy-MM-dd'));
    //TODO: add check if things in past cannot be saved, use reload window

    //Urlaubstage
    await page.getByRole('row', { name: 'Anzahl der Urlaubstage Aktiv' }).getByRole('button').click();
    await page.getByTestId('userLeaveDays').getByLabel('').nth(1).fill('20');
    await page
        .getByTestId('userLeaveDays-since')
        .getByLabel('')
        .nth(1)
        .fill(date.plus({ month: 1 }).toFormat('yyyy-MM'));
    //TODO: add check if things in past cannot be saved, use reload window

    //Homeoffice
    await page.getByLabel('Darf der Mitarbeitende').check();
    await page.getByLabel('Homeoffice Stunden pro Woche').click();
    await page.getByLabel('Homeoffice Stunden pro Woche').fill('10');

    //Organisation
    await page
        .locator('div')
        .filter({ hasText: /^Organisation verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByText('Schreiben').click();
    await page
        .locator('div')
        .filter({ hasText: /^Abteilungen verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByText('Lesen').click();

    //Betriebsstätte
    await page.getByTestId('userOperatingSiteSelection').locator('i').click();
    await page.getByText('delete me ORG').click();
    await page
        .getByTestId('userOperatingSitePermissions')
        .locator('div')
        .filter({ hasText: /^Abwesenheiten verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByRole('option', { name: 'Lesen' }).click();

    await page
        .getByTestId('userOperatingSitePermissions')
        .locator('div')
        .filter({ hasText: /^Zeitkonten verwaltenKeine Rechte$/ })
        .locator('span')
        .click();
    await page.getByRole('option', { name: 'Schreiben' }).click();

    //Abteilung
    // const group = await php({ page, command: 'App\\Models\\Group::first()->name' });
    await page.getByTestId('userGroupSelection').locator('i').click();
    await page.getByRole('option', { name: 'Beispiel' }).click();
    await page
        .getByTestId('userGroupPermissions')
        .locator('div')
        .filter({ hasText: /^Zeitkonten verwaltenKeine Rechte$/ })
        .locator('div')
        .first()
        .click();
    await page.getByRole('option', { name: 'Lesen' }).click();

    //Vorgesetzter
    await page.getByTestId('userSupervisorSelection').locator('i').click();
    await page.getByRole('option', { name: 'admin admin' }).click();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await page.getByText('Mitarbeitenden erfolgreich').click();

    // checks if updated information are visible
    await page
        .locator('div')
        .filter({ hasText: /^Mitarbeitende$/ })
        .first()
        .click();
    await page.getByRole('cell', { name: 'Changed', exact: true }).click();
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
    // await page.locator('span').filter({ hasText: /^20$/ }).first().click();
    //FIXME: ??

    // tests visibility of transactions
    await page.getByRole('tab', { name: 'Transaktionen' }).click();
    await expect(page.getByRole('cell', { name: 'Testkonto' }).first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This is another test' })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Initialer Kontostand' }).first()).toBeVisible();
    await expect(page.getByRole('cell', { name: '+ 10' }).locator('span').first()).toBeVisible();
    await expect(page.getByRole('cell', { name: 'This is a test' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '+ 40' }).locator('span').first()).toBeVisible();
});

// test('trys organigramm', async ({ page }) => {
//     await page.getByRole('row', { name: 'user user user@' }).getByRole('link').getByRole('button').click();
//     await page.getByRole('tab', { name: 'Organigramm' }).click();
//     await expect(page.getByText('user user', { exact: true })).toBeVisible();
// });
