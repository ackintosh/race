<?php
namespace Ackintosh\Race;

use Ackintosh\Race\Message\AllProcessId;
use Ackintosh\Race\Message\Ready;

class Coordinator
{
    /**
     * @var Agent[]
     */
    private $agents = [];

    /**
     * @var Queue
     */
    private $queue;

    public function __construct()
    {
        $this->queue = new Queue();
    }

    /**
     * @param \Closure $job
     * @return void
     */
    public function fork(\Closure $job)
    {
        $coodinatorPid = getmypid();
        $pid = pcntl_fork();
        $agent = new Agent(
            ($pid === 0) ? getmypid() : $pid,
            $coodinatorPid
        );

        if ($pid === -1) {
            throw new \RuntimeException('Failed to fork.');
        } elseif ($pid === 0) {
            // child
            $job->call(new Context(), $agent);
            exit;
        }

        // parent
        $this->agents[] = $agent;
    }

    public function run()
    {
        if (empty($this->agents)) {
            return;
        }

        $this->waitUntilReady();
        $this->notifyAll();

        foreach ($this->agents as $agent) {
            $status = null;
            pcntl_waitpid($agent->getPid(), $status);
        }
    }

    private function waitUntilReady()
    {
        foreach ($this->agents as $agent) {
            $this->queue->receive(Ready::class);
        }
    }

    private function notifyAll()
    {
        $allProcessId = $this->allProcessId();
        foreach ($this->agents as $agent) {
            $this->queue->send($agent->getPid(), $allProcessId);
        }
    }

    /**
     * @return AllProcessId
     */
    private function allProcessId(): AllProcessId
    {
        return new AllProcessId(array_map(
            function (Agent $agent) {
                return $agent->getPid();
            },
            $this->agents
        ));
    }
}