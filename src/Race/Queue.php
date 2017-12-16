<?php
namespace Ackintosh\Race;

use Ackintosh\Race\Message\AllProcessIds;
use Ackintosh\Race\Message\CandidateList;
use Ackintosh\Race\Message\Message;
use Ackintosh\Race\Message\Ready;

class Queue
{
    /**
     * @var int
     */
    private $key;

    private $keys = [];

    /**
     * @var resource[]
     */
    private $resources = [];

    public function __construct()
    {
        $this->keys[Ready::class] = ftok(__FILE__, 'R');
        $this->keys[AllProcessIds::class] = ftok(__FILE__, 'P');
        $this->keys[CandidateList::class] = ftok(__FILE__, 'C');
    }

    /**
     * @param int $to destination Pid
     * @param $message
     */
    public function send($to, Message $message)
    {
        if (!isset($this->keys[get_class($message)])) {
            throw new \LogicException();
        }

        $resource = msg_get_queue($this->keys[get_class($message)]);
        msg_send($resource, $to, $message);
    }

    public function receive(string $messageClass, int $from = null, bool $nowait = false)
    {
        if (!isset($this->keys[$messageClass])) {
            throw new \LogicException();
        }

        if (!$from) {
            $from = getmypid();
        }

        $resource = $this->resource($messageClass);
        $receivedMessageType = null;
        $message = null;

        if ($nowait) {
            msg_receive($resource, $from, $receivedMessageType, 1000, $message, true, MSG_IPC_NOWAIT);
        } else {
            msg_receive($resource, $from, $receivedMessageType, 1000, $message);
        }

        return $message;
    }

    /**
     * @param string $messageClass
     * @return resource
     */
    private function resource(string $messageClass)
    {
        if (!isset($this->resources[$messageClass])) {
            $this->resources[$messageClass] = msg_get_queue($this->keys[$messageClass]);
        }

        return $this->resources[$messageClass];
    }

    /**
     * @return void
     */
    public function cleanup()
    {
        foreach ($this->keys as $k) {
            msg_remove_queue(msg_get_queue($k));
        }
    }
}