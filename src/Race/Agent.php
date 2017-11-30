<?php
namespace Ackintosh\Race;

use Ackintosh\Race\Message\AllProcessIds;
use Ackintosh\Race\Message\Ready;
use Ackintosh\Race\Message\StartingTime;

class Agent
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @var int
     */
    private $coodinatorPid;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @param int $pid
     * @param int $coodinatorPid
     */
    public function __construct($pid, $coodinatorPid)
    {
        $this->pid = $pid;
        $this->coodinatorPid = $coodinatorPid;
        $this->queue = new Queue();
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function ready()
    {

        $this->queue->send($this->coodinatorPid, new Ready());

        $allProcessIds = $this->queue->receive(AllProcessIds::class);

        $this->sendCandidateTo($allProcessIds);
        $candidates = $this->receiveCandidatesFrom($allProcessIds);

        $raceStartsAt = $this->buildConsensus($candidates);

        while (microtime(true) <= $raceStartsAt) {
            // wait until the time race should start
        }
    }

    /**
     * @param AllProcessIds $allProcessIds
     * @return void
     */
    private function sendCandidateTo(AllProcessIds $allProcessIds)
    {
        $candidate = new StartingTime(microtime(true) + 3);
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $this->queue->send($pid, $candidate);
        }
    }

    /**
     * @param AllProcessIds $allProcessIds
     * @return StartingTime[]
     */
    private function receiveCandidatesFrom(AllProcessIds $allProcessIds): array
    {
        $candidates = [];
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $candidates[] = $this->queue->receive(StartingTime::class);
        }

        return $candidates;
    }

    /**
     * @param StartingTime[] $candidates
     * @return double
     */
    private function buildConsensus(array $candidates): float
    {
        $consensus = null;
        foreach ($candidates as $startingTime) {
            if ($consensus === null || $consensus < $startingTime->body()) {
                $consensus = $startingTime->body();
            }
        }

        return $consensus;
    }
}