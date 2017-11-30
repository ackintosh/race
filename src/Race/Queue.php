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
        $this->key = ftok(__FILE__, 'Q');
        $this->keys[Ready::class] = ftok(__FILE__, 'R');
        $this->keys[AllProcessId::class] = ftok(__FILE__, 'P');
        $this->keys[StartingTime::class] = ftok(__FILE__, 'T');
    }

    /**
     * @param int $to destination Pid
     * @param $message
     */
    public function send($to, $message)
    {
        if ($message instanceof Message) {
            if (!isset($this->keys[get_class($message)])) {
                throw new \LogicException();
            }
            $key = $this->keys[get_class($message)];
        } else {
            $key = $this->key;
        }
        $resource = msg_get_queue($key);
        msg_send($resource, $to, $message);
    }

    public function receive(?string $messageClass = null)
    {
        if ($messageClass) {
            if (!isset($this->keys[$messageClass])) {
                throw new \LogicException();
            }
            $key = $this->keys[$messageClass];
        } else {
            $key = $this->key;
        }
        $resource = msg_get_queue($key);
        $receivedMessageType = null;
        $message = null;
        msg_receive($resource, getmypid(), $receivedMessageType, 1000, $message);

        return $message;
    }
}