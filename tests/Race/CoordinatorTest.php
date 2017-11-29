<?php
namespace Ackintosh\Race;

class CoordinatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function fork()
    {
        $coordinator = new Coordinator();
        $t = time();
        $job = function (Agent $agent) {
            // Job requires the preparation that takes 3secs.
            sleep(3);
            $agent->ready();

            return 'test';
        };

        $coordinator->fork($job);
        $coordinator->fork($job);
        $coordinator->run();

        // Preparation takes 3sec
        // +
        // Coordinator notify the agent the time added 3sec
        // = 6sec (We allow for a margin of error)
        $this->assertEquals(6, time() - $t, '', 1);
    }
}
