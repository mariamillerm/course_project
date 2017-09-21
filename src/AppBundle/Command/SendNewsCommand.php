<?php

namespace AppBundle\Command;

use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendNewsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        // TODO Change description
        $this
            ->setName('news:send')
            ->setDescription('Send news')
            ->addArgument('execution', InputArgument::OPTIONAL,'Latest execution');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $execution = new \DateTime($input->getArgument('execution'));

        $postRepository = $this->getContainer()->get('doctrine')->getRepository(Post::class);
        $userRepository = $this->getContainer()->get('doctrine')->getRepository(User::class);

        /**
         * @var User[] $users
         * @var Post[] $posts
         */
        $posts = $postRepository->findNewPosts($execution);
        $users = $userRepository->findSubscribers();

        foreach ($users as $user) {
            $this->getContainer()->get('app.email_support')->sendNewsEmail($user, $posts);
        }
    }
}