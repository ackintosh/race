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
        $agent = new Agent(($pid === 0) ? getmygid() : $pid);

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
}