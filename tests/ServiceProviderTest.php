<?php

namespace Iben\Statable\Test;

use Iben\Statable\Services\StateHistoryManager;

class ServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    public function it_registers_state_history_manager()
    {
        $this->assertInstanceOf(
            StateHistoryManager::class,
            app('StateHistoryManager')
        );
    }
}
