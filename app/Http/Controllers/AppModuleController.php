<?php

namespace App\Http\Controllers;

use App\Services\AppModuleService;
use Illuminate\Http\Request;

class AppModuleController extends Controller
{
    public function switchAppModule(Request $request)
    {
        $module = $request->validate([
            'module' => 'required|string|in:herta,timesheets',
        ])['module'];

        AppModuleService::setCurrentAppModule($module);

        return redirect(route('dashboard'));
    }
}
