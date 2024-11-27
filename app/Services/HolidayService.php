<?php

namespace App\Services;

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

    public static function isHoliday($countryCode, $region = null, CarbonInterface $date)
    {
        return Holidays::for(self::$COUNTRIES[$countryCode]['class']::make($countryCode . ($region ? '-' . $region : '')))->isHoliday($date);
    }
}
