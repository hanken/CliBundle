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
use ReflectionClass;
use ReflectionMethod;
use Reflection;
use FRNK\CliBundle\Output\BufferedOutput;

class CliExplainCommand extends ContainerAwareCommand {

    protected function execute(InputInterface $input, OutputInterface $output) {

        $id = $input->getArgument('service');
        $templateVars = array();
        $templateVars["service"] = $id;
        $service = $this->getContainer()->get($id);
        $class = get_class($service);
        $refl = new ReflectionClass($class);
        $templateVars["className"] = $class;
        $templateVars["methods"] = array();
        $o = new BufferedOutput($output, true);
        
        ob_start();
        $this->getApplication()->doRun(new StringInput("container:debug " . $id), $o);
        $templateVars["debug"] = array();
        $messages = $o->getMessages();
        for ($i = 3; $i< count($messages); $i++){
             $templateVars["debug"][] = $messages[$i];
        }
        ob_clean();

        foreach ($refl->getMethods() as $method) {
            $methodStr = implode(' ', Reflection::getModifierNames($method->getModifiers())) . " function ";
            if (strstr($methodStr, "public")) {
                $methodStr.=$method->getName() . "(";
                $first=true;
                foreach ($method->getParameters() as $param) {
                    if (!($first)){
                        $methodStr.=", ";
                    }
                    $first=false;
                    if ($param->getClass()) {
                        $methodStr .=$param->getClass()->getName() . " ";
                    }

                    if ($param->isOptional()) {
                        $methodStr.="[";
                    }
                    if ($param->isPassedByReference()) {
                        $methodStr.="&";
                    }
                    $methodStr.="$".$param->getName();
                    if ($param->isDefaultValueAvailable() && $param->getDefaultValue()) {
                        $methodStr.=" = " . $param->getDefaultValue() ."";
                    }

                    if ($param->isOptional()) {
                        $methodStr.="]";
                    }
                }
                $methodStr.=");";


                $templateVars["methods"][] = $methodStr;
            }
        }


        $output->writeln($this->getContainer()->get("templating")->render("FRNKCliBundle::explain.output.cli.twig", $templateVars));
    }

    private function getContainerBuilder() {
        if (!$this->getApplication()->getKernel()->isDebug()) {
            throw new \LogicException(sprintf('Debug information about the container is only available in debug mode.'));
        }

        if (!file_exists($cachedFile = $this->getContainer()->getParameter('debug.container.dump'))) {
            throw new \LogicException(sprintf('Debug information about the container could not be found. Please clear the cache and try again.'));
        }

        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($cachedFile);

        return $container;
    }

    protected function configure() {
        $this
                ->setName("cli:explain")
                ->setDefinition(array(
                    new InputArgument('service', InputArgument::REQUIRED, 'The service id'),
                ))
                ->setDescription('Explains a service')
                ->setHelp(<<<EOF
The <info>explain</info> command explains a service:

  <info>php app/console cli:explain</info>

You can also explain any other valid class:

  <info>php app/console cli:explain FOO\BarBundle\Model\FooBarModel</info>
EOF
        );
        ;
    }

}

?>
