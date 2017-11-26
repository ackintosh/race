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

    public function getPid(): int
    {
        return $this->pid;
    }

    public function ready()
    {
        $resource = msg_get_queue($this->pid);
        msg_send($resource, 1, 'ready');

        $receivedMessageType = null;
        $message = null;
        msg_receive($resource, 2, $receivedMessageType, 100, $message);
    }
}