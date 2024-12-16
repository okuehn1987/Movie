import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await page.getByRole('navigation').locator('div').filter({ hasText: 'Mitarbeitende' }).nth(2).click();
    await expect(page).toHaveURL('/user');
});

test('can create a new user', async ({ page }) => {
    await page.getByRole('row', { name: '# Vorname Nachname Email' }).getByRole('button').click();

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
    await page.getByRole('dialog').locator('i').nth(2).click();
    await page.getByText('Deutschland').click();
    await page.getByRole('dialog').locator('i').nth(3).click();
    await page.getByRole('option', { name: 'Hamburg' }).click();
    await page.getByRole('button', { name: 'Weiter' }).click();

    //Berechtigungen
    await page.getByTestId('userOperatingSite').click();
    await page.getByText('delete me ORG').click();
    await page.locator('.v-col > .v-input > .v-input__control > .v-field > .v-field__field > .v-field__input').first().click();
    await page.getByText('Mitarbeitende verwalten').click();
    await page.locator('.v-col > .v-input > .v-input__control > .v-field > .v-field__append-inner > .mdi-menu-down').first().click();
    await page.locator('span').filter({ hasText: 'StufeLesenStufe' }).locator('i').click();
    await page.getByRole('option', { name: 'Schreiben' }).click();
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Mitarbeitenden erfolgreich')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'Test', exact: true })).toBeVisible();
});
