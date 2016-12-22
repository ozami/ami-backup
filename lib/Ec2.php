<?php

class Ec2
{
    public $ec2;
    
    public function __construct($profile, $region)
    {
        $this->ec2 = Aws\Ec2\Ec2Client::factory([
            "profile" => $profile,
            "region" => $region,
        ]);
    }
    
    public function getName($resouce_id)
    {
        $reply = $this->ec2->describeTags([
            "Filters" => [
                ["Name" => "key", "Values" => ["Name"]],
                ["Name" => "resource-id", "Values" => [$resouce_id]],
            ],
        ]);
        if (!$reply["Tags"]) {
            return null;
        }
        return $reply["Tags"][0]["Value"];
    }
    
    public function createImage($instance_id, $name)
    {
        $reply = $this->ec2->createImage([
            "InstanceId" => $instance_id,
            "Name" => $name,
            "Description" => "Created by ami-backup.php",
            "NoReboot" => true,
        ]);
        $ami_id = $reply["ImageId"];
        
        // Add tags
        $this->ec2->createTags([
            "Resources" => [$ami_id],
            "Tags" => [
                ["Key" => "Name", "Value" => $name],
                ["Key" => "AmiBackupAutoDelete", "Value" => "yes"],
            ]
        ]);
    }
}
