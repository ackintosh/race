<?php
namespace Ackintosh\Race\Message;


class StartingTime implements Message
{
    /**
     * @var float
     */
    private $time;

    public function __construct(float $time)
    {
        $this->time = $time;
    }

    public function body(): float
    {
        return $this->time;
    }
}