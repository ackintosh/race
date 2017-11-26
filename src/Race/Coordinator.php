<?php
namespace Ackintosh\Race;

class Coordinator
{
    /**
     * @var Agent[]
     */
    private $agents = [];

    /**
     * @param \Closure $job
     * @return void
     */
    public function fork(\Closure $job)
    {
        $pid = pcntl_fork();
        $agent = new Agent(($pid === 0) ? getmypid() : $pid);

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
            $receivedMessageType = null;
            $message = null;
            $resource = msg_get_queue($agent->getPid());
            msg_receive($resource, 1, $receivedMessageType, 100, $message);
        }
    }

    private function notifyAll()
    {
        foreach ($this->agents as $agent) {
            $resource = msg_get_queue($agent->getPid());
            msg_send($resource, 2, 'notify');
        }
    }
}