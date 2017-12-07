<?php
namespace Ackintosh\Race;

use Ackintosh\Race\Message\AllProcessIds;
use Ackintosh\Race\Message\CandidateList;
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

        $numberOfProcess = count($allProcessIds->body());

        $myCandidateList = new CandidateList($allProcessIds);
        $myCandidateList->setMyCandidate(new StartingTime(microtime(true) + 3));

        // allows failure process (N - 1)
        for ($i = 0; $i < ($numberOfProcess - 1); $i++) {
            $this->sendCandidateList($allProcessIds, $myCandidateList);
            $candidateLists = $this->receiveCandidateLists($allProcessIds);
            foreach ($candidateLists as $receivedList) {
                $myCandidateList->merge($receivedList);
            }
        }

        $raceStartsAt = $this->buildConsensus($myCandidateList);

        while (microtime(true) <= $raceStartsAt) {
            // wait until the time race should start
        }
    }

    /**
     * @param AllProcessIds $allProcessIds
     * @param CandidateList $candidateList
     * @return void
     */
    private function sendCandidateList(AllProcessIds $allProcessIds, CandidateList $candidateList)
    {
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $this->queue->send($pid, $candidateList);
        }
    }

    /**
     * @param AllProcessIds $allProcessIds
     * @return CandidateList[]
     */
    private function receiveCandidateLists(AllProcessIds $allProcessIds): array
    {
        $candidateLists = [];
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            $candidateLists[] = $this->queue->receive(CandidateList::class);
        }

        return $candidateLists;
    }

    /**
     * @param CandidateList $candidateList
     * @return double
     */
    private function buildConsensus(CandidateList $candidateList): float
    {
        $consensus = null;
        foreach ($candidateList->body() as $startingTime) {
            if ($consensus === null || $consensus < $startingTime->body()) {
                $consensus = $startingTime->body();
            }
        }

        return $consensus;
    }
}