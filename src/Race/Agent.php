<?php
namespace Ackintosh\Race;

use Ackintosh\Race\Message\AllProcessId;
use Ackintosh\Race\Message\Ready;

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

        $allProcessIds = $this->queue->receive(AllProcessId::class);

        $this->sendCandidateTo($allProcessIds);
        $candidates = $this->receiveCandidatesFrom($allProcessIds);

        $raceStartsAt = $this->buildConsensus($candidates);

        while (microtime(true) <= $raceStartsAt) {
            // wait until the time race should start
        }
    }

    /**
     * @param AllProcessId $allProcessId
     * @return void
     */
    private function sendCandidateTo(AllProcessId $allProcessIds)
    {
        $candidate = microtime(true) + 3;
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $this->queue->send($pid, $candidate);
        }
    }

    /**
     * @param AllProcessId $allProcessId
     * @return double[]
     */
    private function receiveCandidatesFrom(AllProcessId $allProcessIds): array
    {
        $candidates = [];
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $candidates[] = $this->queue->receive();
        }

        return $candidates;
    }

    /**
     * @param double[] $candidates
     * @return double
     */
    private function buildConsensus(array $candidates): float
    {
        $consensus = null;
        foreach ($candidates as $candidate) {
            if ($consensus === null || $consensus < $candidate) {
                $consensus = $candidate;
            }
        }

        return $consensus;
    }
}