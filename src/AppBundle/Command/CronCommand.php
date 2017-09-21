<?php

namespace AppBundle\Command;

use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// TODO Refactoring
class CronCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:execute')
            ->setDescription('Execute cron commands');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $latestExecutions = $this->getContainer()->get('app.latest_execution_service');
        $time = new \DateTime();

        $tasksFile = $this->getContainer()->getParameter('tasks_file');

        if (file_exists($tasksFile)) {
            $tasks = file($tasksFile);
        } else {
            $tasks = [];
        }

        foreach ($tasks as $task) {
            $command = $this->getApplication()->find($task);

            $execution = $latestExecutions->getExecution($task);

            if ($execution !== null) {
                $execution = $execution->format(DATE_ISO8601);
            } else {
                $execution = null;
            }

            $arguments = [
                'command' => $task,
                'execution' => $execution,
            ];

            $taskInput = new ArrayInput($arguments);

            try {
                $command->run($taskInput, $output);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }

            $latestExecutions->saveExecution($task, $time);
        }
    }
}