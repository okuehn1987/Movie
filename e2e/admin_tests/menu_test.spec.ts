import { test, expect } from '@playwright/test';
import { adminLogin, resetAndSeedDatabase } from '../utils';

test.beforeEach('admin login', async ({ page }) => {
    await resetAndSeedDatabase(page);
    await adminLogin(page);
    await expect(page).toHaveURL('/dashboard');
});

test('can switch between sites', async ({ page }) => {
    //Dashboard
    await expect(page).toHaveURL('/dashboard');

    //Abwesenheiten
    await page.getByRole('navigation').getByText('Abwesenheiten').click();
    await expect(page.getByRole('banner').getByText('Abwesenheiten')).toBeVisible();

    //Arbeitszeiten
    await page.getByRole('navigation').getByText('Arbeitszeiten').click();
    await expect(page.getByRole('banner').getByText('Arbeitszeiten')).toBeVisible();

    //Anträge
    await page.getByRole('navigation').getByText('Anträge', { exact: true }).click();
    await expect(page.getByRole('banner').getByText('Anträge')).toBeVisible();

    //Organisation
    await page.getByRole('navigation').getByText('Organisation', { exact: true }).click();
    await expect(page.getByRole('banner').getByText('Organisation')).toBeVisible();

    //Betriebsstätten
    await page.getByRole('navigation').getByText('Betriebsstätten').click();
    await expect(page.getByRole('banner').getByText('Betriebsstätten')).toBeVisible();

    //Abteilungen
    await page.getByRole('navigation').getByText('Abteilungen').click();
    await expect(page.getByRole('banner').getByText('Abteilungen')).toBeVisible();

    //Mitarbeitende
    await page.getByRole('navigation').getByText('Mitarbeitende').click();
    await expect(page.getByRole('banner').getByText('Mitarbeiter')).toBeVisible();

    //Organigramm
    await page.getByRole('navigation').getByText('Organigramm').click();
    await expect(page.getByRole('banner').getByText('Organigramm')).toBeVisible();

    //Organisationen
    await page.getByRole('navigation').getByText('Organisationen').click();
    await expect(page.getByRole('banner').getByText('Organisationen')).toBeVisible();
});
