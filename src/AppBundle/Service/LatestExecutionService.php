<?php

namespace AppBundle\Service;

use Kitpages\SemaphoreBundle\Manager\Manager;
use Symfony\Component\Yaml\Yaml;

class LatestExecutionService
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $updates = [];

    /**
     * @var array
     */
    private $executions = [];

    /**
     * @var Manager
     */
    private $semaphoreManager;

    /**
     * LatestExecutionService constructor.
     *
     * @param Manager $semaphoreManager
     * @param string $filename
     */
    public function __construct(Manager $semaphoreManager, string $filename)
    {
        $this->semaphoreManager = $semaphoreManager;
        $this->filename = $filename;

        $this->loadExecutions();
    }

    private function loadExecutions()
    {
        $this->executions = [];

        if (file_exists($this->filename)) {
            $yaml = file_get_contents($this->filename);

            if ($yaml !== false) {
                $executions = Yaml::parse($yaml);

                if (is_array($executions)) {
                    foreach ($executions as $task => $date) {
                        $executions[$task] = new \DateTime($executions[$task]);
                    }

                    $this->executions = $executions;
                }
            }
        }
    }

    private function saveExecutions()
    {
        $executions = array_merge($this->executions, $this->updates);

        foreach ($executions as $task => $date) {
            $executions[$task] = $executions[$task]->format(DATE_ISO8601);
        }

        file_put_contents($this->filename, Yaml::dump($executions));
    }

    public function getExecution(string $taskName): ?\DateTime
    {
        return array_merge($this->executions, $this->updates)[$taskName] ?? null;
    }

    public function saveExecution(string $taskName, \DateTime $time)
    {
        $this->updates[$taskName] = $time;
    }

    public function __destruct()
    {
        $this->semaphoreManager->aquire('executions');
        $this->loadExecutions();

        $this->saveExecutions();
        $this->semaphoreManager->release('executions');
    }
}
