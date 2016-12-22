<?php

class Ec2
{
    const AUTO_DELETE_TAG_KEY = "AmiBackupAutoDelete";
    
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
        $name = "$name-" . date("Ymd-His");
        $reply = $this->ec2->createImage([
            "InstanceId" => $instance_id,
            "Name" => $name,
            "Description" => "Backup of $instance_id",
            "NoReboot" => true,
        ]);
        $ami_id = $reply["ImageId"];
        
        // Add tags
        $this->ec2->createTags([
            "Resources" => [$ami_id],
            "Tags" => [
                ["Key" => "Name", "Value" => $name],
                ["Key" => self::AUTO_DELETE_TAG_KEY, "Value" => "yes"],
            ]
        ]);
        return $ami_id;
    }
    
    public function deleteOldImages($instance_name, $count)
    {
        $reply = $this->ec2->describeImages([
            "Filters" => [
                ["Name" => "tag:" . self::AUTO_DELETE_TAG_KEY, "Values" => ["yes"]],
                ["Name" => "state", "Values" => ["available"]],
            ],
        ]);
        $images = $reply["Images"];
        
        // filter with image name
        $images = array_filter($images, function($image) use ($instance_name) {
            if (!preg_match("/(.+)-[0-9]{8}-[0-9]{6}$/", $image["Name"], $m)) {
                return false;
            }
            return $m[1] == $instance_name;
        });
        
        // 作成日が新しい順
        usort($images, function($a, $b) {
            if ($a["CreationDate"] > $b["CreationDate"]) {
                return -1;
            }
            if ($a["CreationDate"] < $b["CreationDate"]) {
                return 1;
            }
            return 0;
        });
        
        $images = array_slice($images, $count);
        
        foreach ($images as $image) {
            // deregister AMI
            $this->ec2->deregisterImage([
                "ImageId" => $image["ImageId"],
            ]);
            // delete snapshots
            foreach ($image["BlockDeviceMappings"] as $device) {
                $this->ec2->deleteSnapshot([
                    "SnapshotId" => $device["Ebs"]["SnapshotId"],
                ]);
            }
        }
    }
    
    public function getSnapshotsOfImage($ami_id)
    {
        $reply = $this->ec2->describeImages([
            "ImageIds" => [$ami_id],
        ]);
        var_dump($reply);
    }
}
