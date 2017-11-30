<?php
namespace Ackintosh\Race;

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

        $this->queue->send($this->coodinatorPid, 'ready');

        $allProcessIds = $this->queue->receive();

        $this->send($allProcessIds);
        $candidates = $this->receive($allProcessIds);

        $raceStartsAt = $this->buildConsensus($candidates);

        while (microtime(true) <= $raceStartsAt) {
            // wait until the time race should start
        }
    }

    /**
     * @param int[] $allProcessIds
     * @return void
     */
    private function send(array $allProcessIds)
    {
        $v = microtime(true) + 3;
        foreach ($allProcessIds as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $this->queue->send($pid, $v);
        }
    }

    /**
     * @param int[] $allProcessIds
     * @return double[]
     */
    private function receive(array $allProcessIds): array
    {
        $candidates = [];
        foreach ($allProcessIds as $pid) {
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