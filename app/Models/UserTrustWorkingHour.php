<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserTrustWorkingHour extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 
     * @param array{
     *  start: string,
     *  end: string,
     *  user_id: int
     * } $data
     */
    public static function checkCollisions(array $data): bool
    {
        return self::where('user_id', $data['user_id'])
            ->where(fn($q) =>
            $q->where('active_since', '<=', Carbon::parse($data['end']))
                ->where('active_until', '>=', Carbon::parse($data['start'])))
            ->exists();
    }
}
