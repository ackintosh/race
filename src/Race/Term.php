<?php
namespace Ackintosh\Race;

class Term
{
    private $current = 1;
    private $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    public function next()
    {
        $this->current++;
    }

    public function current(): int
    {
        return $this->current;
    }

    public function isInProgress(): bool
    {
        return $this->current() <= $this->max;
    }
}
