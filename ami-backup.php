<?php

function error($message, $status)
{
    file_put_contents("php://stderr", $message . "\n");
    exit($status);
}

function main($args)
{
    require_once __DIR__ . "/vendor/autoload.php";
    
    date_default_timezone_set(@date_default_timezone_get());
    try {
        $options = new Options();
        $options->parse(); // TODO: use $args after fixing Ulrichsg\Getopt
    }
    catch (UnexpectedValueException $e) {
        error($options->getHelpText(), 1);
    }
    if ($options->getOption("help")) {
        error($options->getHelpText(), 0);
    }
    
    $rotate = $options->getOption("rotate");
    if ($rotate !== null && $rotate < 1) {
        error("Rotation count must be greater than 0.", 1);
    }
    
    $meta_data = new InstanceMetaData();
    $instance_id = $meta_data->getInstanceId();
    $region = $meta_data->getRegion();
    
    $ec2 = new Ec2($options->getOption("profile"), $region);
    $instance_name = $ec2->getName($instance_id);
    if ($instance_name === null) {
        error("This instance doesn't have a Name tag.", 1);
    }
    
    if ($rotate) {
        $ec2->deleteOldImages($instance_name, $rotate - 1);
    }
    
    $ec2->createImage($instance_id, $instance_name);
}

main($_SERVER["argv"]);
