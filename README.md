# ami-backup
Backup AWS EC2 instance as AMI. Requires PHP 5.4+


## IAM Policy

### For Backup Only

```
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "Stmt1482394651000",
            "Effect": "Allow",
            "Action": [
                "ec2:CreateImage",
                "ec2:CreateSnapshot",
                "ec2:CreateTags"
            ],
            "Resource": [
                "*"
            ]
        }
    ]
}
```

### For Backup and Rotation

```
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "Stmt1482394651000",
            "Effect": "Allow",
            "Action": [
                "ec2:CreateImage",
                "ec2:CreateSnapshot",
                "ec2:CreateTags",
                "ec2:DeleteSnapshot",
                "ec2:DeregisterImage",
                "ec2:DescribeImages",
                "ec2:DescribeTags"
            ],
            "Resource": [
                "*"
            ]
        }
    ]
}
```
