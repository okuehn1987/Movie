<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Spatie\Holidays\Countries\Germany;
use Spatie\Holidays\Holidays;

class HolidayService
{
    public static $COUNTRIES = [
        'DE' => [
            'class' => Germany::class,
            'name' => 'Deutschland',
            'regions' => [
                'BW' => 'Baden-Württemberg',
                'BY' => 'Bayern',
                'BE' => 'Berlin',
                'BB' => 'Brandenburg',
                'HB' => 'Bremen',
                'HH' => 'Hamburg',
                'HE' => 'Hessen',
                'MV' => 'Mecklenburg-Vorpommern',
                'NI' => 'Niedersachsen',
                'NW' => 'Nordrhein-Westfalen',
                'RP' => 'Rheinland-Pfalz',
                'SL' => 'Saarland',
                'SN' => 'Sachsen',
                'ST' => 'Sachsen-Anhalt',
                'SH' => 'Schleswig-Holstein',
                'TH' => 'Thüringen'
            ],
        ]
    ];

    public static function getCountries()
    {
        return collect(array_keys(self::$COUNTRIES))->map(fn($c) => ['title' => self::$COUNTRIES[$c]['name'], 'value' => $c, 'regions' => self::$COUNTRIES[$c]['regions']]);
    }

    public static function isHoliday($countryCode, $region = null, CarbonInterface $date)
    {
        return Holidays::for(self::$COUNTRIES[$countryCode]['class']::make($countryCode . ($region ? '-' . $region : '')))->isHoliday($date);
    }

    public static function getHolidays($countryCode, $region = null, CarbonInterface $date)
    {
        if ($date->gte(Carbon::createFromFormat('Y-m-d', '2038-01-01'))) return [];
        return Holidays::for(self::$COUNTRIES[$countryCode]['class']::make($countryCode . ($region ? '-' . $region : '')))->getInRange($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
    }

    public static function getCountryCodes()
    {
        return array_keys(self::$COUNTRIES);
    }

    public static function getRegionCodes(string|null $countryCode)
    {
        return in_array($countryCode, HolidayService::getCountryCodes()) ?  array_keys(HolidayService::$COUNTRIES[$countryCode]["regions"]) : [];
    }
}
