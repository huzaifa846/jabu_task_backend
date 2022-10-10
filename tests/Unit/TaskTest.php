<?php

namespace Tests\Feature;

use Mockery;
use App\Models\Cycle;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;
use App\Helpers\TaskHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TaskController;

class TaskTest extends TestCase
{
    private $task;
    private $taskMock;
    private $cycleMock;
    private $taskHelpereMock;

    public function setUp(): void
    {
        parent::setUp();
        $user  = User::factory()->create();
        $this->actingAs($user);
        $this->task = $this->getTaskObject();

        $this->taskMock = Mockery::mock('alias:' . Task::class);
        $this->cycleMock = Mockery::mock('alias:' . Cycle::class);
        $this->taskHelpereMock = Mockery::mock('alias:' . TaskHelper::class);

        $this->app->instance(Task::class, $this->taskMock);
        $this->app->instance(Cycle::class, $this->cycleMock);
        $this->app->instance(Cycle::class, $this->taskHelpereMock);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_task_create()
    {
        $this->taskMock->shouldReceive('create')->andReturn(
            $this->getTaskObject()
        );

        $this->cycleMock->shouldReceive('create')->andReturn(
            $this->getTaskObject()
        );

        $this->taskMock->shouldReceive('taskRules')->andReturn(
            []
        );

        $controller = resolve(TaskController::class);
        $request = resolve(Request::class);
        $request->replace([
            'title' => 'title',
            'description' => 'description',
            'type' =>  'yearly',
            'interval_type' =>  'interval_type',
            'start_date' =>  'start_date',
            'end_date' =>  'end_date',
            'repeat_count' =>  'repeat_count',
            'cycles' =>  ['day', 'month'],
        ]);

        $response = $controller->store($request)->getContent();
        $response = json_decode($response);

        $this->assertEquals($response->error, false);
    }

    public function test_task_update()
    {
        $this->taskMock->shouldReceive('find')->andReturn(
            $this->getTaskObject(false)
        );

        $this->taskMock->shouldReceive('update')->andReturn(
            $this->getTaskObject()
        );

        $controller = resolve(TaskController::class);
        $request = resolve(Request::class);
        $request->replace([
            'completed' => true,
        ]);

        $response = $controller->update($request)->getContent();
        $response = json_decode($response);

        $this->assertEquals($response->error, false);
    }


    public function test_fetch_tasks()
    {

        $this->taskHelpereMock->shouldReceive('todayTask')->andReturn(
            $this->getTasksCollection()
        );
        $this->taskHelpereMock->shouldReceive('nextDayTask')->andReturn(
            $this->getTasksCollection()
        );
        $this->taskHelpereMock->shouldReceive('nextWeekTask')->andReturn(
            $this->getTasksCollection()
        );
        $this->taskHelpereMock->shouldReceive('futureTask')->andReturn(
            $this->getTasksCollection()
        );

        $controller = resolve(TaskController::class);
        $response = $controller->tasks()->getContent();

        $response = json_decode($response);

        $this->assertEquals($response->todayTask[1]->title, "test");
    }

    private function getTasksCollection(){
        $collection = collect(Task::class);
        $collection->push( $this->task);
        return $collection;
    }

    private function getTaskObject($mocked=true){
        if($mocked)
            return (object) [
                'id'=>1,
                'user_id'=>1,
                'title' =>'test',
                'description'=>'test description',
                'completed' => false,
                'type' =>'daily',
                'interval_type' => 'date',
                'start_date' =>'2022-10-10',
                'end_date' =>'2022-10-16'
            ];
        else
            return $this->taskMock;
    }
}
