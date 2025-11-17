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
            ...(Feature::active("new_year_vacation_day") ? ['12-31' => 'Silvester'] : []),
            ...(Feature::active("christmas_vacation_day") ? ['12-24' => 'Weihnachten'] : []),
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

    public static function getSchoolHolidaysForMonth(CarbonInterface $date)
    {
        return collect(self::getSchoolHolidays())->map(
            fn($federalState) => collect($federalState)->filter(
                fn($entry) =>
                Carbon::parse($entry['start'])->lte($date->copy()->endOfMonth()) &&
                    Carbon::parse($entry['end'])->gte($date->copy()->startOfMonth())
            )->values()
        );
    }

    private static function getSchoolHolidays()
    {
        return [
            "Baden-Württemberg" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-27",
                    "end" => "2025-10-30"
                ],
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-31",
                    "end" => "2025-10-31"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-05"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-11"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-06-05"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-30",
                    "end" => "2026-09-12"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-26",
                    "end" => "2026-10-30"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-31",
                    "end" => "2026-10-31"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-09"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-25",
                    "end" => "2027-03-25"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-30",
                    "end" => "2027-04-03"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-29"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-29",
                    "end" => "2027-09-11"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-11-02",
                    "end" => "2027-11-06"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-08"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-13",
                    "end" => "2028-04-13"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-18",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-06",
                    "end" => "2028-06-17"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-27",
                    "end" => "2028-09-09"
                ]
            ],
            "Bayern" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-11-03",
                    "end" => "2025-11-07"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-05"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-02-16",
                    "end" => "2026-02-20"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-06-05"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-08-03",
                    "end" => "2026-09-14"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-11-02",
                    "end" => "2026-11-06"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-24",
                    "end" => "2027-01-08"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-02-08",
                    "end" => "2027-02-12"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-02"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-28"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-08-02",
                    "end" => "2027-09-13"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-11-02",
                    "end" => "2027-11-05"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-24",
                    "end" => "2028-01-07"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-02-28",
                    "end" => "2028-03-03"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-21"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-06",
                    "end" => "2028-06-16"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-31",
                    "end" => "2028-09-11"
                ]
            ],
            "Berlin" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-20",
                    "end" => "2025-11-01"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-02"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-02",
                    "end" => "2026-02-07"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-05-26"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-09",
                    "end" => "2026-08-22"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-19",
                    "end" => "2026-10-31"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-02"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-01",
                    "end" => "2027-02-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-02"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-19"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-01",
                    "end" => "2027-08-14"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-11",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-22",
                    "end" => "2027-12-31"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-01-31",
                    "end" => "2028-02-05"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-01",
                    "end" => "2028-06-02"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-01",
                    "end" => "2028-08-12"
                ]
            ],
            "Brandenburg" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-20",
                    "end" => "2025-11-01"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-02"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-02",
                    "end" => "2026-02-07"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-05-26"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-09",
                    "end" => "2026-08-22"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-19",
                    "end" => "2026-10-30"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-02"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-01",
                    "end" => "2027-02-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-03"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-18"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-01",
                    "end" => "2027-08-14"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-11",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2027-12-31"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-01-31",
                    "end" => "2028-02-05"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-06-29",
                    "end" => "2028-08-12"
                ]
            ],
            "Bremen" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-13",
                    "end" => "2025-10-25"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-05"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-02",
                    "end" => "2026-02-03"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-23",
                    "end" => "2026-04-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-05-26"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-02",
                    "end" => "2026-08-12"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-12",
                    "end" => "2026-10-24"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-09"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-01",
                    "end" => "2027-02-02"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-03"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-18"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-08",
                    "end" => "2027-08-18"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-18",
                    "end" => "2027-10-30"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-08"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-01-31",
                    "end" => "2028-02-01"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-06",
                    "end" => "2028-06-06"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-20",
                    "end" => "2028-08-30"
                ]
            ],
            "Hamburg" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-20",
                    "end" => "2025-10-31"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-17",
                    "end" => "2026-01-02"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-01-30",
                    "end" => "2026-01-30"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-02",
                    "end" => "2026-03-13"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-11",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-09",
                    "end" => "2026-08-19"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-19",
                    "end" => "2026-10-30"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-21",
                    "end" => "2027-01-01"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-01-29",
                    "end" => "2027-01-29"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-01",
                    "end" => "2027-03-12"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-14"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-01",
                    "end" => "2027-08-11"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-11",
                    "end" => "2027-10-22"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-20",
                    "end" => "2027-12-31"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-01-28",
                    "end" => "2028-01-28"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-03-06",
                    "end" => "2028-03-17"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-22",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-03",
                    "end" => "2028-08-11"
                ]
            ],
            "Hessen" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-06",
                    "end" => "2025-10-18"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-10"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-06-29",
                    "end" => "2026-08-07"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-05",
                    "end" => "2026-10-17"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-12"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-02"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-06-28",
                    "end" => "2027-08-06"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-04",
                    "end" => "2027-10-16"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-11"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-03",
                    "end" => "2028-04-14"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-03",
                    "end" => "2028-08-11"
                ]
            ],
            "Mecklenburg-Vorpommern" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-02",
                    "end" => "2025-10-02"
                ],
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-20",
                    "end" => "2025-10-24"
                ],
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-11-03",
                    "end" => "2025-11-03"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-20",
                    "end" => "2026-01-03"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-09",
                    "end" => "2026-02-20"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-08"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-22",
                    "end" => "2026-05-26"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-13",
                    "end" => "2026-08-22"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-15",
                    "end" => "2026-10-24"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-21",
                    "end" => "2027-01-02"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-08",
                    "end" => "2027-02-19"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-24",
                    "end" => "2027-04-02"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-14",
                    "end" => "2027-05-18"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-05",
                    "end" => "2027-08-14"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-14",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-22",
                    "end" => "2028-01-04"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-02-05",
                    "end" => "2028-02-17"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-02-18",
                    "end" => "2028-02-18"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-12",
                    "end" => "2028-04-21"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-02",
                    "end" => "2028-06-06"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-06-26",
                    "end" => "2028-08-05"
                ]
            ],
            "Niedersachsen" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-13",
                    "end" => "2025-10-25"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-05"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-02",
                    "end" => "2026-02-03"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-23",
                    "end" => "2026-04-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-05-26"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-02",
                    "end" => "2026-08-12"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-12",
                    "end" => "2026-10-24"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-09"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-01",
                    "end" => "2027-02-02"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-03"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-18"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-08",
                    "end" => "2027-08-18"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-16",
                    "end" => "2027-10-30"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-08"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-01-31",
                    "end" => "2028-02-01"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-06",
                    "end" => "2028-06-06"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-20",
                    "end" => "2028-08-30"
                ]
            ],
            "Nordrhein-Westfalen" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-13",
                    "end" => "2025-10-25"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-11"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-05-26"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-20",
                    "end" => "2026-09-01"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-17",
                    "end" => "2026-10-31"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-03"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-18",
                    "end" => "2027-05-18"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-19",
                    "end" => "2027-08-31"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-23",
                    "end" => "2027-11-06"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-24",
                    "end" => "2028-01-08"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-10",
                    "end" => "2028-08-22"
                ]
            ],
            "Rheinland-Pfalz" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-13",
                    "end" => "2025-10-24"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-07"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-06-29",
                    "end" => "2026-08-07"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-05",
                    "end" => "2026-10-16"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-08"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-02"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-06-28",
                    "end" => "2027-08-06"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-04",
                    "end" => "2027-10-15"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-07"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-21"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-03",
                    "end" => "2028-08-11"
                ]
            ],
            "Saarland" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-13",
                    "end" => "2025-10-24"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-02"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-16",
                    "end" => "2026-02-20"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-04-07",
                    "end" => "2026-04-17"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-06-29",
                    "end" => "2026-08-07"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-05",
                    "end" => "2026-10-16"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-21",
                    "end" => "2026-12-31"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-08",
                    "end" => "2027-02-12"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-30",
                    "end" => "2027-04-09"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-06-28",
                    "end" => "2027-08-06"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-04",
                    "end" => "2027-10-15"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-20",
                    "end" => "2027-12-31"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-02-21",
                    "end" => "2028-02-29"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-12",
                    "end" => "2028-04-21"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-03",
                    "end" => "2028-08-11"
                ]
            ],
            "Sachsen" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-06",
                    "end" => "2025-10-18"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-02"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-09",
                    "end" => "2026-02-21"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-04-03",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-04",
                    "end" => "2026-08-14"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-12",
                    "end" => "2026-10-24"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-02"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-08",
                    "end" => "2027-02-19"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-26",
                    "end" => "2027-04-02"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-15",
                    "end" => "2027-05-18"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-10",
                    "end" => "2027-08-20"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-11",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-01"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-02-14",
                    "end" => "2028-02-26"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-14",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-22",
                    "end" => "2028-09-01"
                ]
            ],
            "Sachsen-Anhalt" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-13",
                    "end" => "2025-10-25"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-05"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-01-31",
                    "end" => "2026-02-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-30",
                    "end" => "2026-04-04"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-26",
                    "end" => "2026-05-29"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-04",
                    "end" => "2026-08-14"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-19",
                    "end" => "2026-10-30"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-21",
                    "end" => "2027-01-02"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-01",
                    "end" => "2027-02-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-03-27"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-15",
                    "end" => "2027-05-22"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-10",
                    "end" => "2027-08-20"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-18",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-20",
                    "end" => "2027-12-31"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-02-07",
                    "end" => "2028-02-12"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-10",
                    "end" => "2028-04-22"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-06-03",
                    "end" => "2028-06-10"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-22",
                    "end" => "2028-09-01"
                ]
            ],
            "Schleswig-Holstein" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-20",
                    "end" => "2025-10-30"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-19",
                    "end" => "2026-01-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-03-26",
                    "end" => "2026-04-10"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-04",
                    "end" => "2026-08-15"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-12",
                    "end" => "2026-10-24"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-21",
                    "end" => "2027-01-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-30",
                    "end" => "2027-04-10"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-03",
                    "end" => "2027-08-14"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-11",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2028-01-08"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-03",
                    "end" => "2028-04-15"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-06-24",
                    "end" => "2028-08-04"
                ]
            ],
            "Thüringen" => [
                [
                    "name" => "Herbst 2025",
                    "start" => "2025-10-06",
                    "end" => "2025-10-18"
                ],
                [
                    "name" => "Weihnachten 2025/2026",
                    "start" => "2025-12-22",
                    "end" => "2026-01-03"
                ],
                [
                    "name" => "Winter 2026",
                    "start" => "2026-02-16",
                    "end" => "2026-02-21"
                ],
                [
                    "name" => "Ostern/Frühjahr 2026",
                    "start" => "2026-04-07",
                    "end" => "2026-04-17"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2026",
                    "start" => "2026-05-15",
                    "end" => "2026-05-15"
                ],
                [
                    "name" => "Sommer 2026",
                    "start" => "2026-07-04",
                    "end" => "2026-08-14"
                ],
                [
                    "name" => "Herbst 2026",
                    "start" => "2026-10-12",
                    "end" => "2026-10-24"
                ],
                [
                    "name" => "Weihnachten 2026/2027",
                    "start" => "2026-12-23",
                    "end" => "2027-01-02"
                ],
                [
                    "name" => "Winter 2027",
                    "start" => "2027-02-01",
                    "end" => "2027-02-06"
                ],
                [
                    "name" => "Ostern/Frühjahr 2027",
                    "start" => "2027-03-22",
                    "end" => "2027-04-03"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2027",
                    "start" => "2027-05-07",
                    "end" => "2027-05-07"
                ],
                [
                    "name" => "Sommer 2027",
                    "start" => "2027-07-10",
                    "end" => "2027-08-20"
                ],
                [
                    "name" => "Herbst 2027",
                    "start" => "2027-10-09",
                    "end" => "2027-10-23"
                ],
                [
                    "name" => "Weihnachten 2027/2028",
                    "start" => "2027-12-23",
                    "end" => "2027-12-31"
                ],
                [
                    "name" => "Winter 2028",
                    "start" => "2028-02-07",
                    "end" => "2028-02-12"
                ],
                [
                    "name" => "Ostern/Frühjahr 2028",
                    "start" => "2028-04-03",
                    "end" => "2028-04-15"
                ],
                [
                    "name" => "Himmelfahrt/Pfingsten 2028",
                    "start" => "2028-05-26",
                    "end" => "2028-05-26"
                ],
                [
                    "name" => "Sommer 2028",
                    "start" => "2028-07-22",
                    "end" => "2028-09-01"
                ]
            ]
        ];
    }
}
