<?php
namespace Ackintosh\Race;

class Address
{
    public function to(int $pid): int
    {
        return $pid;
    }

    public function from(): int
    {
        return getmypid();
    }
}
