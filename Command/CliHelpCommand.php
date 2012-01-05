<?php
namespace FRNK\CliBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Exception;

class CliHelpCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('cli:help')
                ->setDescription('Shows a list of built in commands for the Symfony2 cli')
        ;
    }
 
     protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln($this->getContainer()->get("templating")->render("FRNKCliBundle::help.output.cli.twig"));
     }
    
}

?>
