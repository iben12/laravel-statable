<?php

namespace Iben\Statable;

use SM\StateMachine\StateMachine;
use Iben\Statable\Models\StateHistory;

trait Statable
{
    /**
     * @var StateMachine
     */
    protected $SM;

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function stateHistory()
    {
        return $this->morphMany(StateHistory::class, 'statable');
    }

    /**
     * @param array $transitionData
     */
    public function addHistoryLine(array $transitionData)
    {
        if ($this->getKey()) {
            $transitionData['actor_id'] = $this->getActorId();
            $this->stateHistory()->create($transitionData);
        }
    }

    /**
     * @return int|null
     */
    public function getActorId()
    {
        return auth()->id();
    }

    /**
     * @return mixed|string
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function stateIs()
    {
        return $this->stateMachine()->getState();
    }

    /**
     * @param $transition
     * @return bool
     * @throws \SM\SMException|\Illuminate\Container\EntryNotFoundException
     */
    public function apply($transition)
    {
        if ($this->getKey() === null && $this->saveBeforeTransition()) {
            $this->save();
        }

        return $this->stateMachine()->apply($transition);
    }

    /**
     * @param $transition
     * @return bool
     * @throws \SM\SMException|\Illuminate\Container\EntryNotFoundException
     */
    public function canApply($transition)
    {
        return $this->stateMachine()->can($transition);
    }

    /**
     * @return mixed|\SM\StateMachine\StateMachine
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function stateMachine()
    {
        if (! $this->SM) {
            $this->SM = app('sm.factory')->get($this, $this->getGraph());
        }

        return $this->SM;
    }

    /**
     * @return string
     */
    protected function getGraph()
    {
        return 'default';
    }

    /**
     * @return bool
     */
    protected function saveBeforeTransition()
    {
        return false;
    }
}
