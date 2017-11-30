<?php
namespace Ackintosh\Race;


use Ackintosh\Race\Message\AllProcessId;
use Ackintosh\Race\Message\Message;
use Ackintosh\Race\Message\Ready;
use Ackintosh\Race\Message\StartingTime;

class Queue
{
    /**
     * @var int
     */
    private $key;

    private $keys = [];

    public function __construct()
    {
        $this->keys[Ready::class] = ftok(__FILE__, 'R');
        $this->keys[AllProcessId::class] = ftok(__FILE__, 'P');
        $this->keys[StartingTime::class] = ftok(__FILE__, 'T');
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

    public function receive(string $messageClass)
    {
        if (!isset($this->keys[$messageClass])) {
            throw new \LogicException();
        }

        $resource = msg_get_queue($this->keys[$messageClass]);
        $receivedMessageType = null;
        $message = null;
        msg_receive($resource, getmypid(), $receivedMessageType, 1000, $message);

        return $message;
    }
}