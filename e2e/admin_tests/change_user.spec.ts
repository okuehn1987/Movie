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

    await page.getByText('Mitarbeitende').click();
});

test('changes seeded user', async ({ page }) => {
    test.slow();

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
    // await page
    //     .locator('div')
    //     .filter({ hasText: /^Abteilungen verwaltenKeine Rechte$/ })
    //     .locator('span')
    //     .click();
    // await page.getByText('Lesen').click();

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

    // await page
    //     .getByTestId('userOperatingSitePermissions')
    //     .locator('div')
    //     .filter({ hasText: /^Zeitkonten verwaltenKeine Rechte$/ })
    //     .locator('span')
    //     .click();
    // await page.getByRole('option', { name: 'Schreiben' }).click();

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
    await page.getByRole('option', { name: 'Schreiben' }).click();

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
