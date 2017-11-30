<?php
namespace Ackintosh\Race\Message;

class AllProcessId implements Message
{
    private $allProcessId;

    public function __construct(array $allProcessId)
    {
        $this->allProcessId = $allProcessId;
    }

    public function body(): array
    {
        return $this->allProcessId;
    }
}
