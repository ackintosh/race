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
        $coordinator->fork($job);
        $coordinator->run();

        // Preparation takes 3sec
        // +
        // Agents are notify the time which has been added 3sec as candidate of starting time
        // +
        // No failure process
        // = 6sec (We allow for a margin of error)
        $this->assertEquals(6, time() - $t, '', 1);
    }
}
