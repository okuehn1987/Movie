import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
    await page
        .locator('div')
        .filter({ hasText: /^Organisation$/ })
        .first()
        .click();
    await expect(page).toHaveURL(/organization\/.*/);
});

test('changes information ', async ({ page }) => {
    await page.getByLabel('Firmenname').fill('Weihnachtsmann & Co. KG');
    await page.getByLabel('Umsatzsteuer-ID').fill('DE257546956455');
    await page.getByLabel('Handelsregister').fill('HR B');
    await page.getByLabel('Webseite').fill('test.com');
    await page.getByLabel('Nachtzuschüsse?').check();
    await page.getByLabel('Verjährungsfrist bei').check();
    await page.getByRole('button', { name: 'Aktualisieren' }).click();
    await expect(page.getByText('Organisation erfolgreich')).toBeVisible();
    await expect(page.getByText('Organisation Weihnachtsmann &')).toBeVisible();
});

test('adds, edits and deletes specialWorkingHoursFactors', async ({ page }) => {
    await page.getByRole('tab', { name: 'Sonderarbeitszeitfaktor' }).click();
    await expect(page.getByRole('cell', { name: 'Keine Daten vorhanden' })).toBeVisible();
    await page.getByRole('row', { name: 'Tag Faktor' }).getByRole('button').click();
    await expect(page.getByText('Besondere Arbeitszeitzuschläge')).toBeVisible();
    await page.getByTestId('swhfWeekday').first().click();
    await page.getByText('Montag').click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Montag$/ })
            .first(),
    ).toBeVisible();
    await page.getByLabel('Faktor Zuschlag').fill('2');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Besondere Arbeitszeitzuschläge')).not.toBeVisible();
    await expect(page.getByText('Besonderer')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Montag' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '2' })).toBeVisible();

    // edits specialWorkingHoursFactors
    await page.getByRole('row', { name: 'Montag' }).getByRole('button').first().click();
    await page
        .locator('div')
        .filter({ hasText: /^Montag$/ })
        .first()
        .click();
    await page.getByText('Freitag').click();
    await expect(
        page
            .locator('div')
            .filter({ hasText: /^Freitag$/ })
            .first(),
    ).toBeVisible();
    await page.getByLabel('Faktor Zuschlag').fill('3');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Besondere Arbeitszeitzuschläge')).not.toBeVisible();
    await expect(page.getByText('Besonderer')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Freitag' })).toBeVisible();
    await expect(page.getByRole('cell', { name: '3' })).toBeVisible();

    // deletes specialWorkingHoursFactors
    await page.getByRole('row', { name: 'Freitag' }).getByRole('button').nth(1).click();
    await expect(page.getByText('Arbeitszuschlag löschen')).toBeVisible();
    await page.getByRole('button', { name: 'Löschen' }).click();
    await expect(page.getByText('Arbeitszuschlag löschen')).not.toBeVisible();
    await expect(page.getByText('Arbeitszuschlag erfolgreich')).toBeVisible();
});

test('adds absence type', async ({ page }) => {
    await page.getByRole('tab', { name: 'Abwesenheitsgründe' }).click();
    await page.getByRole('row', { name: '# Abwesenheitsgrund Abkürzung' }).getByRole('button').click();
    await expect(page.getByText('Abwesenheitgrund erstellen')).toBeVisible();
    await page.getByLabel('Abwesenheitsgrund').fill('gelber Schein');
    await page.getByLabel('Abkürzung').fill('Freiheit');
    await page.getByTestId('absenceType').click();
    await page.getByRole('option', { name: 'Bildungsurlaub' }).click();
    await page.getByLabel('Muss genehmigt werden?').check();
    await page.getByRole('button', { name: 'Erstellen' }).click();
    await expect(page.getByText('Abwesenheitgrund erstellen')).not.toBeVisible();
    await expect(page.getByText('Abwesenheitstyp erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'gelber Schein' })).toBeVisible();
});
