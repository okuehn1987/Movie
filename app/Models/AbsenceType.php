<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsenceType extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $fillable = ['name', 'type', 'abbreviation', 'requires_approval'];

    public static $DEFAULTS = [
        ['name' => 'Unbezahlter Urlaub', 'abbreviation' => 'UB'],
        ['name' => 'Ausbildung/ Berufsschule', 'abbreviation' => 'BS'],
        ['name' => 'Fort- und Weiterbildung', 'abbreviation' => 'FW'],
        ['name' => 'AZV-Tag', 'abbreviation' => 'AZ'],
        ['name' => 'Bildungsurlaub', 'abbreviation' => 'BU'],
        ['name' => 'Sonderurlaub', 'abbreviation' => 'SU'],
        ['name' => 'Elternzeit', 'abbreviation' => 'EZ'],
        ['name' => 'Urlaub', 'abbreviation' => 'EU'],
        ['name' => 'Arbeitsunfähigkeit', 'abbreviation' => 'AU']
    ];

    public static function getTypes()
    {
        return [...collect(self::$DEFAULTS)->map(fn($e) => $e['name']), 'Andere'];
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
