<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Authentication;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends Command
{
    private $entity_manager;

    public function __construct(EntityManagerInterface $entity_manager)
    {
        parent::__construct('app:add-user');

        $this->entity_manager = $entity_manager;
    }

    protected function configure()
    {
        $this->addArgument('username', InputArgument::REQUIRED);
        $this->addArgument('password', InputArgument::REQUIRED);
        $this->addArgument('housenr', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        [$house_number, $house_number_addition] = array_pad(explode(" ", $input->getArgument("housenr")), 2, '');

        $user = new User(
            $input->getArgument('username'),
            $house_number,
            $house_number_addition,
            new Authentication($input->getArgument('password'))
        );

        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

        $output->writeln("User added");
    }
}