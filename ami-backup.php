<?php

function main($args)
{
    require_once __DIR__ . "/vendor/autoload.php";
    
    date_default_timezone_set(@date_default_timezone_get());
    try {
        $options = new Options();
        $options->parse(); // TODO: use $args after fixing Ulrichsg\Getopt
    }
    catch (UnexpectedValueException $e) {
        file_put_contents("php://stderr", $options->getHelpText());
        exit(1);
    }
    if ($options->getOption("help")) {
        file_put_contents("php://stderr", $options->getHelpText());
        exit(0);
    }
}

main($_SERVER["argv"]);
