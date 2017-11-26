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
        $coordinator->run();

        // Coodinator waits until the job to finish.
        $this->assertTrue((time() - $t) >= 3);
    }
}
