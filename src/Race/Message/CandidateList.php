<?php
namespace Ackintosh\Race\Message;

class CandidateList implements Message
{
    /**
     * @var array
     */
    private $list;

    public function __construct(AllProcessIds $allProcessIds)
    {
        foreach ($allProcessIds->body() as $pid) {
            $this->list[$pid] = null;
        }
    }

    public function setMyCandidate(StartingTime $startingTime)
    {
        $this->list[getmypid()] = $startingTime;
    }

    public function merge(CandidateList $anotherList)
    {
        $another = $anotherList->body();
        foreach ($this->list as $pid => $startingTime) {
            if ($startingTime !== null || $another[$pid] === null) {
                continue;
            }

            $this->list[$pid] = $another[$pid];
        }
    }

    public function body(): array
    {
        return $this->list;
    }
}
