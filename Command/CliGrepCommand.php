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

class CliGrepCommand extends ContainerAwareCommand {

    protected function configure() {
              $this
            ->setDefinition(array(
                new InputArgument('needle', InputArgument::REQUIRED, 'The text to search for'),
                new InputOption('piped', null, InputOption::VALUE_REQUIRED, "The piped Data", ''),
                new InputOption('case-sensitive', "i", InputOption::VALUE_NONE, "makes the search case sensitive"),
            ))
            ->setName('cli:grep')
            ->setDescription('shows only matching lines')
            ->setHelp(<<<EOF
The <info>grep</info> command greps from piped output:

  <info>php app/console grep foo</info>
EOF
            );
        $this
                ->setName('cli:grep')
                ->setDescription('shows only matching lines')
        ;
    }
 
     protected function execute(InputInterface $input, OutputInterface $output) {
         $needle = $input->getArgument("needle");
         $case = $input->getOption("case-sensitive");
         
         echo ($case);
         $messages=explode("\n", $input->getOption("piped"));
         foreach ($messages as $line){
                     if ($case == 1){
                         if (strstr($line, $needle)){
                            $output->writeln($line);
                         }
                     }else{
                         if (stristr($line, $needle)){
                            $output->writeln($line);
                         }
                     }
         }
     }
}

?>
