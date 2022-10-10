<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Api;
use App\Helpers\ApiValidate;
use App\Helpers\TaskHelper;
use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(Request $request){
        $credentials = ApiValidate::task($request, Task::class);
        $task = Task::create([
            'user_id' => Auth::user()->id,
            'title' => $credentials['title'],
            'description' => $credentials['description'],
            'type' =>  $credentials['type'],
            'interval_type' =>  $credentials['interval_type'],
            'start_date' =>  $credentials['start_date'],
            'end_date' =>  $credentials['end_date'],
            'repeat_count' =>  $credentials['repeat_count'],
        ]);

        if ($task->type == 'yearly') {
            Cycle::create([
                'task_id' => $task->id,
                'day' => $credentials['cyles'][0],
                'month' => $credentials['cyles'][1]
            ]);
        } else {
            foreach ($credentials['cycles'] as $cycle) {
                Cycle::create([
                    'task_id' => $task->id,
                    'day' => $cycle
                ]);
            }
        }
        return Api::setResponse('task',$task);
    }

    public function update(Request $request){
        $task = Task::find($request->task_id);
        $task->update([
            'completed' => true
        ]);
        return Api::setResponse('task',$task);

    }

    public function tasks(){
        return response()->json([
            'todayTask' =>  TaskHelper::todayTask(),
            'nextDayTask' => TaskHelper::nextDayTask(),
            'nextWeekTask' => TaskHelper::nextWeekTask(),
            'futureTask' => TaskHelper::futureTask(),
         ]);
    }

}
