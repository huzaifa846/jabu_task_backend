<?php

namespace App\Models;

use App\Traits\TaskMethods;
use App\Traits\UserMethods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory,TaskMethods;
    protected $casts = [
        'date' => 'datetime',
    ];
    protected $fillable = [
        'user_id','title','description','completed','type','start_date','end_date','repeat_count','interval_type'
    ];

    public function cycles(){
        return $this->hasMany(Cycle::class);
    }
}
