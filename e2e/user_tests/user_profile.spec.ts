import { test, expect } from '@playwright/test';
import { resetAndSeedDatabase, userLogin } from '../utils';

test.beforeEach('user login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await userLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('changes profile information, after success changes back', async ({ page }) => {
    await page.getByRole('button', { name: 'Profil' }).click();
    await expect(page.getByText('Profil', { exact: true })).toBeVisible();
    await expect(page.getByText('Profil-Informationen')).toBeVisible();
    await page.getByLabel('Vorname').fill('testuser');
    await page.getByLabel('Nachname').fill('testuser');
    await page.getByLabel('Email').fill('testuser@testuser.com');
    await page.locator('form').filter({ hasText: 'Aktualisieren Sie die' }).getByRole('button').click();
    await expect(page.getByText('Profilinformationen erfolgreich gespeichert.')).toBeVisible();

    //changes back to old
    await page.getByLabel('Vorname').fill('user');
    await page.getByLabel('Nachname').fill('user');
    await page.getByLabel('Email').fill('user@user.com');
    await page.locator('form').filter({ hasText: 'Aktualisieren Sie die' }).getByRole('button').click();
    await expect(page.getByText('Profilinformationen erfolgreich gespeichert.')).toBeVisible();
});

test('trys to fill false password length then changes password', async ({ page }) => {
    //trys to fill password with less than 8 characters
    await page.getByRole('button', { name: 'Profil' }).click();
    await page.getByLabel('Aktuelles Passwort').fill('user');
    await page.getByLabel('Neues Passwort').fill('test');
    await page.getByLabel('Passwort bestätigen').fill('test');
    await page.locator('form').filter({ hasText: 'Stellen Sie sicher, dass Ihr' }).getByRole('button').click();
    await expect(page.getByText('Passwort muss mindestens 8 Zeichen enthalten.')).toBeVisible();

    //continues with normal test
    await page.getByLabel('Aktuelles Passwort').fill('user');
    await page.getByLabel('Neues Passwort').fill('testtest');
    await page.getByLabel('Passwort bestätigen').fill('testtest');
    await page.locator('form').filter({ hasText: 'Stellen Sie sicher, dass Ihr' }).getByRole('button').click();
    await expect(page.getByText('Passwort erfolgreich')).toBeVisible();
});
