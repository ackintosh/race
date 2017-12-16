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
     * @var bool
     */
    private $behaveAsFailureProcess = false;

    /**
     * @var int
     */
    private $numberOfCandidateListSent = 0;

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
        $myCandidateList->setMyCandidate(
            new StartingTime(microtime(true) + ($numberOfProcess * 5) + 3)
        );

        $term = new Term();
        $address = new Address($term);
        // allows failure process (N - 1)
        for ($i = 0; $i < ($numberOfProcess - 1); $i++) {
            $this->sendCandidateList($address, $allProcessIds, $myCandidateList);
            $candidateLists = $this->receiveCandidateLists($address, $allProcessIds);
            var_dump('[' . getmypid() . '][' . $term->current() . ']', $candidateLists);
            foreach ($candidateLists as $receivedList) {
                $myCandidateList->merge($receivedList);
            }

            $term->next();
        }

        $raceStartsAt = $this->buildConsensus($myCandidateList);

        while (microtime(true) <= $raceStartsAt) {
            // wait until the time race should start
        }
    }

    public function behaveAsFailureProcess()
    {
        $this->behaveAsFailureProcess = true;
    }

    /**
     * @param Address $destination
     * @param AllProcessIds $allProcessIds
     * @param CandidateList $candidateList
     * @return void
     */
    private function sendCandidateList(Address $destination, AllProcessIds $allProcessIds, CandidateList $candidateList)
    {
        foreach ($allProcessIds->body() as $pid) {
            if ($pid === $this->pid) {
                continue;
            }

            if ($this->behaveAsFailureProcess && $this->numberOfCandidateListSent >= 1) {
                var_dump('[' . getmypid() . '] die.');
                die();
            }

            $this->queue->send($destination->to($pid), $candidateList);
            $this->numberOfCandidateListSent++;
        }
    }

    /**
     * @param AllProcessIds $allProcessIds
     * @return CandidateList[]
     */
    private function receiveCandidateLists(Address $address, AllProcessIds $allProcessIds): array
    {
        $t = time();
        $candidateLists = [];
        while (time() < ($t + 5)) {
            if (count($candidateLists) === (count($allProcessIds->body()) - 1)) {
                continue;
            }
            if (
                MSG_ENOMSG !== ($message = $this->queue->receive(CandidateList::class, $address->from(), true))
                && $message instanceof \Ackintosh\Race\Message\Message
            ) {
                $candidateLists[] = $message;
            }
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
            if ($startingTime === null) {
                // The process had stopped from the beginning
                continue;
            }

            if ($consensus === null || $consensus < $startingTime->body()) {
                $consensus = $startingTime->body();
            }
        }

        return $consensus;
    }
}