<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTrustWorkingHour extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // /** 
    //  * @param Log|Patch $model 
    //  */
    // public static function checkCollisions($model)
    // {

    //     return self::where('user_id', $model->user_id)->where(fn($q) => $q->where)
    // }
}
