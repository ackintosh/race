<?php
namespace Ackintosh\Race\Message;

class AllProcessIds implements Message
{
    private $processIds;

    public function __construct(array $processIds)
    {
        $this->processIds = $processIds;
    }

    public function body(): array
    {
        return $this->processIds;
    }
}
