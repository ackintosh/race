<?php
namespace Ackintosh\Race;

class Agent
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @param int $pid
     */
    public function __construct($pid)
    {
        $this->pid = $pid;
    }
}