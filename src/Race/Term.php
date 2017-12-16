<?php
namespace Ackintosh\Race;

class Term
{
    private $current = 1;

    public function next()
    {
        $this->current++;
    }

    public function current(): int
    {
        return $this->current;
    }
}
