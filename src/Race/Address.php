<?php
namespace Ackintosh\Race;

class Address
{
    /**
     * @var Term
     */
    public $term;

    public function __construct(Term $term)
    {
        $this->term = $term;
    }

    public function to(int $pid): int
    {
        return $this->decorate($pid);
    }

    public function from(): int
    {
        return $this->decorate(getmypid());
    }

    private function decorate(int $pid): int
    {
        return $pid * 10 + ($this->term->current() - 1);
    }
}
