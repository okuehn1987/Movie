<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbscenceType extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    public static $DEFAULTS = [
        ['name' => 'Unbezahlter Urlaub', 'abbreviation' => 'UB'],
        ['name' => 'Ausbildung/ Berufsschule', 'abbreviation' => 'BS'],
        ['name' => 'Fort- und Weiterbildung', 'abbreviation' => 'FW'],
        ['name' => 'AZV-Tag', 'abbreviation' => 'AZ'],
        ['name' => 'Bildungsurlaub', 'abbreviation' => 'BU'],
        ['name' => 'Sonderurlaub', 'abbreviation' => 'SU'],
        ['name' => 'Elternzeit', 'abbreviation' => 'EZ'],
        ['name' => 'Urlaub', 'abbreviation' => 'EU']
    ];

    public static function getTypes()
    {
        return [...collect(self::$DEFAULTS)->map(fn($e) => $e['name']), 'Andere'];
    }

    public function abscences()
    {
        return $this->hasMany(Abscence::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
