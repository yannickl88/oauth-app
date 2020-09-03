<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Client;
use App\Entity\User;
use App\Security\Oauth\Scope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddClientCommand extends Command
{
    private $entity_manager;

    public function __construct(EntityManagerInterface $entity_manager)
    {
        parent::__construct('app:add-client');

        $this->entity_manager = $entity_manager;
    }

    protected function configure()
    {
        $this->addArgument('identifier', InputArgument::REQUIRED);
        $this->addArgument('name', InputArgument::REQUIRED);
        $this->addArgument('redirect_uri', InputArgument::REQUIRED);
        $this->addArgument('secret', InputArgument::REQUIRED);
        $this->addArgument('scopes', InputArgument::OPTIONAL);
        $this->addOption('confidential', 'c', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scopes = array_map(function (string $scope) {
            return new Scope(trim($scope));
        }, explode(',', $input->getArgument('scopes')));

        $user = new Client(
            $input->getArgument('identifier'),
            $input->getArgument('name'),
            $input->getArgument('redirect_uri'),
            $input->getOption('confidential'),
            $input->getArgument('secret'),
            $scopes
        );

        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

        $output->writeln("Client added");
    }
}