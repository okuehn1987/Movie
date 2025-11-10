<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Session;

class AppModuleService
{
    public static $APP_MODULES = [
        'herta' => 'Flow',
        'timesheets' => 'Tide'
    ];

    public static function getAppModules()
    {
        return collect(array_keys(self::$APP_MODULES))->map(fn($c) => ['title' => self::$APP_MODULES[$c], 'value' => $c]);
    }

    /**
     * @return 'herta' | 'timesheets'
     */
    public static function currentAppModule()
    {
        if (self::hasAppModule(session('app_module'))) {
            return session('app_module');
        } else {
            self::setCurrentAppModule(match (true) {
                self::hasAppModule('herta') => 'herta',
                self::hasAppModule('timesheets') => 'timesheets',
                default => throw new \Exception('No app module available for this organization'),
            });
            return session('app_module');
        }
    }

    /**
     * @param 'herta' | 'timesheets' $module 
     */
    public static function setCurrentAppModule($module)
    {
        if (array_key_exists($module, self::$APP_MODULES)) {
            Session::put('app_module', $module);
        }
    }

    /**
     * @return bool
     * @param 'herta' | 'timesheets' $module 
     */
    public static function hasAppModule($module)
    {
        return Organization::getCurrent()->modules->pluck('module')->contains($module);
    }
}
