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

class CliMoreCommand extends ContainerAwareCommand {

    protected function configure() {
              $this
            ->setDefinition(array(
                new InputArgument('count', InputArgument::OPTIONAL, 'how many lines per view'),
                                new InputOption('piped', null, InputOption::VALUE_REQUIRED, "The piped Data", ''),

            ))
            ->setName('cli:more')
            ->setDescription('shows only n lines per page (default 10)')
            ->setHelp(<<<EOF
The <info>grep</info> command greps from piped output:

  <info>php app/console grep foo</info>
EOF
            );
    }
 
     protected function execute(InputInterface $input, OutputInterface $output) {
         $count = $input->getArgument("count");
         if (!$count){
             $count = 10;
         }
         $messages=explode("\n", $input->getOption("piped"));
         $current = 0;
         
         $dialog = $this->getHelperSet()->get('dialog');

         $total = count($messages);
        do {
         for ($i = $current; ($i< $total && $i < $current+$count);$i++ ){
              $output->writeln($messages[$i]);
         }
         
         
         if (!($current+$count >= $total)){
                      $userCommand = $dialog->ask($output, "", "y ");
         }
         $current +=$count;
        }while (!($current >= $total) && $userCommand != "q");
         
         
     }
}

?>
