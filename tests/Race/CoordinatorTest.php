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
            sleep(3);
            return 'test';
        };

        $coordinator->fork($job);
        $coordinator->run();

        // Coodinator waits until the job to finish.
        $this->assertTrue((time() - $t) >= 3);
    }
}
