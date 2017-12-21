<?php
namespace Ackintosh\Race;

class Term
{
    private $current = 1;
    private $max;

    public function __construct(int $max)
    {
        if ($max < 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$max must be 1 or more. %d was passed.',
                    $max
                )
            );
        }

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
