<?php

namespace Iben\Statable\Test;

use Iben\Statable\Services\StateHistoryManager;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use SM\Event\TransitionEvent;
use SM\StateMachine\StateMachine;

class StateHistoryManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_models_history_storing_method()
    {
        /**
         * @var Model | Mockery\Mock
         */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('addHistoryLine')->with([
            'transition' => 'foo',
            'from' => 'baz',
            'to' => 'bar',
        ]);

        $sm = Mockery::mock(StateMachine::class);
        $sm->shouldReceive('getObject')->andReturn($model);
        $sm->shouldReceive('getState')->andReturn('bar');

        $event = Mockery::mock(TransitionEvent::class);
        $event->shouldReceive('getStateMachine')->andReturn($sm);
        $event->shouldReceive('getTransition')->andReturn('foo');
        $event->shouldReceive('getState')->andReturn('baz');

        // Act
        $historyManager = app(StateHistoryManager::class);
        $historyManager->storeHistory($event);
    }
}
