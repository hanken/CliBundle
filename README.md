##
##   This is old! check http://github.com/hanken/FRNK-ShellExtensionBundle
##

# CliBundle

The FRNKClieBundle extends the symfony 2 console with a php cli and some additional commands. 

## Installation

### Add to deps file

	[CliBundle]
         git=http://github.com/hanken/CliBundle.git
         target=/bundles/FRNK/CliBundle
### Run vendors
   run
	 php bin/vendors install

   to download and install the CliBundle

### edit autoload

   add the following to your app/autoload.php

     # .../Symfony/app/autoload.php
	$loader->registerNamespaces(array(
	//....
   	 'FRNK' => __DIR__.'/../vendor/bundles',
   	//... 
	));     


### Application Kernel

Add CliBundle to the `registerBundles()` method of your application kernel:

    public function registerBundles()
    {
        return array(
		//....
            new FRNK\CliBundle\FRNKCliBundle(),
        );
    }

## Configuration

nothing to configure
