<?php
namespace Ackintosh\Race;

class CoordinatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function ConsensusAlgorithmRequiresPrescribedTime()
    {
        $coordinator = new Coordinator();
        $t = time();
        $job = function (Agent $agent) {
            // Job requires the preparation that takes 3secs.
            sleep(3);
            $agent->ready();

            return 'test';
        };

        $jobThatMightFail = function (Agent $agent) {
            // Process will fail.
            $agent->behaveAsFailureProcess();
            // Job requires the preparation that takes 3secs.
            sleep(3);
            $agent->ready();

            return 'test';
        };

        $coordinator->fork($job);
        $coordinator->fork($jobThatMightFail);
        $coordinator->fork($job);
        $coordinator->run();

        // Preparation takes 3secs
        //  +
        // Consensus algorithm requires (process * 5)secs
        //  +
        // Agents are notify the time which has been added 3secs as candidate of starting time
        //
        //  = 21sec (We allow for a margin of error as fourth argument of `assertEquals`)
        $this->assertEquals(21, time() - $t, '', 1);
    }
}
