<?php

namespace Iben\Statable\Test;

use Iben\Statable\Models\StateHistory;
use Iben\Statable\Services\StateHistoryManager;

class StatableTest extends TestCase
{
    /** @var StatableArticle */
    public $article;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->bind(StateHistoryManager::class);
        $this->app['config']->set('state-machine.article.class', StatableArticle::class);

        $this->article = StatableArticle::firstOrCreate([
            'title' => 'Test Article',
            'state' => 'new',
        ]);
    }

    /**
     * @test
     */
    public function it_initiates_the_state_machine()
    {
        $this->assertInstanceOf(StateMachine::class, $this->article->stateMachine());
    }

    /**
     * @test
     */
    public function it_returns_current_state()
    {
        $this->assertEquals('new', $this->article->stateIs());
        $this->assertEquals('new', $this->article->state);
    }

    /**
     * @test
     */
    public function it_applies_transition()
    {
        $this->article->apply('create');

        $this->assertEquals('pending_review', $this->article->stateIs());

        $this->assertEquals('create', $this->article->stateHistory()->first()->transition);
    }

    /**
     * @test
     */
    public function it_applies_transition_with_context()
    {
        Event::fake([SMEvents::POST_TRANSITION]);

        $this->article->apply('create', false, ['foo' => 'bar']);

        Event::assertDispatched(SMEvents::POST_TRANSITION, function ($name, TransitionEvent $event) {
            $this->assertInstanceOf(TransitionEvent::class, $event);
            $this->assertEquals(['foo' => 'bar'], $event->getContext());

            return true;
        });
    }

    /**
     * @test
     */
    public function it_saves_history_with_actor()
    {
        Auth::login(User::first());

        $this->article->apply('create');

        $this->assertEquals('create', $this->article->stateHistory()->first()->transition);

        $this->assertEquals(Auth::id(), $this->article->stateHistory()->first()->actor_id);
    }

    /**
     * @test
     */
    public function it_does_not_fail_on_unsaved_model()
    {
        $article = new StatableArticle;
        $article->title = 'Test Article';
        $article->state = 'new';

        $article->apply('create');

        $this->assertEquals('pending_review', $article->state);
    }

    /**
     * @test
     */
    public function it_saves_model_before_transition_if_enabled()
    {
        $this->app['config']->set('state-machine.article.class', StatableArticleWithAutoSave::class);

        $article = new StatableArticleWithAutoSave;
        $article->title = 'Test Article';
        $article->state = 'new';
        $article->save();

        $article->apply('create');
        $this->assertEquals('pending_review', $article->state);
        $this->assertEquals('create', $article->stateHistory()->first()->transition);
    }

    /**
     * @test
     */
    public function it_tests_transition_applicable()
    {
        $this->assertTrue($this->article->canApply('create'));
        $this->assertFalse($this->article->canApply('approve'));
    }

    /**
     * @test
     */
    public function it_tests_transition_applicable_with_context()
    {
        Event::fake([SMEvents::TEST_TRANSITION]);

        $this->assertTrue($this->article->canApply('create', ['foo' => 'bar']));

        Event::assertDispatched(SMEvents::TEST_TRANSITION, function ($name, TransitionEvent $event) {
            $this->assertInstanceOf(TransitionEvent::class, $event);
            $this->assertEquals(['foo' => 'bar'], $event->getContext());

            return true;
        });
    }

    /**
     * @test
     */
    public function it_throws_exception_if_transition_not_applicable()
    {
        $this->expectException('SM\SMException');

        $this->article->apply('approve');
    }
}
