<?php

namespace Iben\Statable\Test;

use Iben\Statable\Models\StateHistory;
use Iben\Statable\Services\StateHistoryManager; 

class CustomStateHistory extends StateHistory {}

class ConfigTest extends TestCase
{
    /** @var StatableArticle */
    public $article;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->bind(StateHistoryManager::class);
        $this->app['config']->set('state-machine.article.class', StatableArticle::class);
        $this->app['config']->set('laravel-statable.models.state_history', CustomStateHistory::class);

        $this->article = StatableArticle::firstOrCreate([
            'title' => 'Test Article',
            'state' => 'new',
        ]);
    }

    /**
     * @test
     */
    public function it_can_use_custom_state_history_model()
    {
        $this->article->apply('create');
        $this->assertInstanceOf(CustomStateHistory::class, $this->article->stateHistory()->first());
    }
}

