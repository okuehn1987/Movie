<?php

namespace App\Http\Controllers;

use App\Services\AppModuleService;
use Illuminate\Http\Request;

class AppModuleController extends Controller
{
    public function switchAppModule(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:herta,timesheets',
        ]);

        AppModuleService::setCurrentAppModule($request->input('module'));

        return redirect(route('dashboard'));
    }
}
