<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Laravel\Pennant\Feature;
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

    private static function getCustomHolidays()
    {
        return [
            ...Feature::active("new_year_vacation_day") ? ['12-31' => 'Silvester'] : [],
            ...Feature::active("christmas_vacation_day") ? ['12-24' => 'Weihnachten'] : []
        ];
    }

    private static function generateHolidays($countryCode, $region = null)
    {
        return  Holidays::for(self::$COUNTRIES[$countryCode]['class']::make($countryCode . ($region ? '-' . $region : '')));
    }

    public static function getCountries()
    {
        return collect(array_keys(self::$COUNTRIES))->map(fn($c) => ['title' => self::$COUNTRIES[$c]['name'], 'value' => $c, 'regions' => self::$COUNTRIES[$c]['regions']]);
    }

    public static function isHoliday($countryCode, $region = null, CarbonInterface $date)
    {
        return self::generateHolidays($countryCode, $region)->isHoliday($date) ||
            array_key_exists($date->copy()->format('m-d'), self::getCustomHolidays());
    }

    public static function getHolidayName($countryCode, $region = null, CarbonInterface $date)
    {
        if (array_key_exists($date->format('m-d'), self::getCustomHolidays())) return self::getCustomHolidays()[$date->format('m-d')];

        return self::generateHolidays($countryCode, $region)->getName($date);
    }

    public static function getHolidaysForMonth($countryCode, $region = null, CarbonInterface $date)
    {
        if ($date->gte(Carbon::createFromFormat('Y-m-d', '2038-01-01'))) return collect([]);
        return collect(
            self::generateHolidays($countryCode, $region)
                ->getInRange($date->copy()->startOfMonth(), $date->copy()->endOfMonth())
        )->merge(
            collect(self::getCustomHolidays())
                ->mapWithKeys(fn($v, $k) => [$date->year . '-' . $k => $v])
                ->filter(
                    fn($v, $k) =>
                    Carbon::parse($k)
                        ->between(
                            $date->copy()->startOfMonth(),
                            $date->copy()->endOfMonth()
                        )
                )
        );
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
