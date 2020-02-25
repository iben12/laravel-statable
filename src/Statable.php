<?php

namespace Iben\Statable;

use Iben\Statable\Models\StateHistory;
use Sebdesign\SM\StateMachine\StateMachine;

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
     * @param bool $soft
     * @param array $context
     * @return bool
     * @throws \Illuminate\Container\EntryNotFoundException
     * @throws \SM\SMException
     */
    public function apply($transition, $soft = false, $context = [])
    {
        if ($this->getKey() === null && $this->saveBeforeTransition()) {
            $this->save();
        }

        return $this->stateMachine()->apply($transition, $soft, $context);
    }

    /**
     * @param $transition
     * @param array $context
     * @return bool
     * @throws \Illuminate\Container\EntryNotFoundException
     * @throws \SM\SMException
     */
    public function canApply($transition, $context = [])
    {
        return $this->stateMachine()->can($transition, $context);
    }

    /**
     * @return mixed|\Sebdesign\SM\StateMachine\StateMachine
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
