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
    
    $meta_data = new InstanceMetaData();
    $instance_id = $meta_data->getInstanceId();
    $region = $meta_data->getRegion();
    
    $ec2 = new Ec2($options->getOption("profile"), $region);
    $name = $ec2->getName($instance_id);
    if ($name === null) {
        $name = $instance_id;
    }
    $name = "$name-" . date("Ymd-His");
    $ec2->createImage($instance_id, $name);
    //$this->deleteOldImages($vol["id"], $count);
    //$backup->run($instance_id, $options->getOption("rotate"));
}

main($_SERVER["argv"]);
