<?php
namespace Ackintosh\Race;


class Queue
{
    /**
     * @var int
     */
    private $key;

    public function __construct()
    {
        $this->key = ftok(__FILE__, 'Q');
    }

    /**
     * @param int $to destination Pid
     * @param $message
     */
    public function send($to, $message)
    {
        $resource = msg_get_queue($this->key);
        msg_send($resource, $to, $message);
    }

    public function receive()
    {
        $resource = msg_get_queue($this->key);
        $receivedMessageType = null;
        $message = null;
        msg_receive($resource, getmypid(), $receivedMessageType, 1000, $message);

        return $message;
    }
}