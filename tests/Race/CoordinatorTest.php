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
        $job = function (Agent $agent) {
            return 'test';
        };

        $coordinator->fork($job);
    }
}
