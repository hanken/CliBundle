<?php

namespace FRNK\CliBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use FRNK\CliBundle\Output\BufferedOutput;
use Exception;

class CliCommand extends ContainerAwareCommand {

    protected $prompt = "-->";
    private $history;
    private $commands;

    protected function configure() {
        $this
                ->setName('cli')
                ->setDescription('Starts the Symfony2 cli')
        ;
    }

    protected function get($id) {
        return $this->getContainer()->get($id);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->prompt = "<info>" . $this->getApplication()->getName() . "#</info> ";
        $this->commands = $this->buildInCommands();
        $this->getApplication()->setCatchExceptions(true);

        $output->writeln($this->getHeader());

        $dialog = $this->getHelperSet()->get('dialog');


        do {
            $userCommand = $dialog->ask($output, $this->prompt, "?");
            $run = false;
            $userCommands = explode("|", $userCommand);
            $pipesToGo = count($userCommands) - 1;
            foreach ($userCommands as $userCommand) {
                $userCommand = trim($userCommand);
                $pipeContent = "";
                $piped = $run;
                $run = false;

                if ($piped) {
                    $pipe = $bufferedOutput;
                    $pipeContent = implode('\n', $pipe->getMessages());
                    $pipesToGo--;
                }

                $bufferedOutput = new BufferedOutput($output, ($pipesToGo > 0));


                if ($this->isInternal($userCommand)) {
                    if ($piped) {

                        $userCommand = $userCommand . " --piped '" . $pipeContent . "'";
                    }
                    $this->runBuiltInCommand($userCommand, $bufferedOutput);
                    $run = true;
                }

                if ((!$run) && $this->isShellCommand($userCommand)) {
                    $this->runShellCommand($userCommand, $bufferedOutput);
                    $run = true;
                }

                if ($piped) {
                    $userCommand = '$pipe = $pipeContent;$lines=explode(\'\n\', $pipe);' . $userCommand;
                }
                if ((!$run) && $this->validatePhpCode($userCommand)) {
                    try {
                        ob_start();
                        $eval = eval($userCommand);
                        $result = ob_get_contents();
                        $bufferedOutput->writeln($result);
                        ob_clean();
                    } catch (Exception $ex) {
                        $this->writeException($ex, $bufferedOutput);
                    }
                    $run = true;
                } else if (!$run) {

                    $force = $dialog->ask($output, "<question>Does not apear to be valid php code.\nrun anyway? (y/n):</question>", "n");

                    if ($force == "yes" || $force == "y") {
                        try {
                            ob_start();
                            $eval = eval($userCommand);
                            $result = ob_get_contents();
                            $bufferedOutput->writeln($result);
                            ob_clean();
                        } catch (Exception $ex) {
                            $this->writeException($ex, $bufferedOutput);
                        }
                        $run = true;
                    }
                }
            }
//            foreach ($bufferedOutput->getMessages() as $m) {
//                $output->writeln($m);
//            }
        } while ($userCommand != "quit" && $userCommand != "q");

        $output->writeln("So long and thank you for all the fish!");
    }

    protected function runBuiltInCommand($userCommand, $output) {

        try {
            $shellCommand = false;
            foreach ($this->commands as $command) {
                $userCommandParts = explode(" ", $userCommand);
                if ($command["name"] == $userCommandParts[0]) {
                    $userCommandParts[0] = $command["command"];
                    $shellCommand = implode(" ", $userCommandParts);
                }
            }
            if ($shellCommand) {
                if (0 == $this->getApplication()->doRun(new StringInput($shellCommand), $output)) {
                    return true;
                }
            }
            return false;
        } catch (Exception $ex) {
            $this->writeException($ex, $output);
            return false;
        }
    }

    protected function writeException($ex, &$output) {
        $output->writeln("<error>" . $ex->getMessage() . "</error>");
    }

    protected function runShellCommand($command, &$output) {

        try {
            if (0 == $this->getApplication()->doRun(new StringInput($command), $output)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {

            $this->writeException($ex, $output);
            return false;
        }
    }

    protected function validatePhpCode($code) {
        return @eval('return true;' . $code);
    }

    protected function isInternal($userCommand) {
        foreach ($this->commands as $command) {
            $userCommandParts = explode(" ", $userCommand);
            if ($command["name"] == $userCommandParts[0]) {
                return true;
            }
        }
        return false;
    }

    protected function isShellCommand($command) {
        $userCommandParts = explode(' ', $command);
        foreach ($this->getApplication()->all() as $name => $shellCommand) {
            if ($name == $userCommandParts[0]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the shell header.
     *
     * @return string The header string
     */
    protected function getHeader() {
        $params = array("applicationName" => $this->getApplication()->getName(),
            "applicationVersion" => $this->getApplication()->getVersion());
        return $this->getContainer()->get("templating")->render("FRNKCliBundle::header.output.cli.twig", $params);
    }

    private function buildInCommands() {
        $commands = array();
        $commands[] = array("name" => "list", "command" => "list", "parameters" => array());
        $commands[] = array("name" => "?", "command" => "cli:help", "parameters" => array());
        $commands[] = array("name" => "q", "command" => null, "parameters" => array());
        $commands[] = array("name" => "quit", "command" => null, "parameters" => array());
        $commands[] = array("name" => "explain", "command" => "cli:explain");
        $commands[] = array("name" => "more", "command" => "cli:more");
        $commands[] = array("name" => "grep", "command" => "cli:grep");
        $commands[] = array("name" => "containers", "command" => "container:debug");

        return $commands;
    }

}

?>
