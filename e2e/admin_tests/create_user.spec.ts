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
    test.slow();
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

// test('trys organigramm', async ({ page }) => {
//     await page.getByRole('row', { name: 'user user user@' }).getByRole('link').getByRole('button').click();
//     await page.getByRole('tab', { name: 'Organigramm' }).click();
//     await expect(page.getByText('user user', { exact: true })).toBeVisible();
// });
