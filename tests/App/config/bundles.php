<?php

declare(strict_types=1);

use Macpaw\DoctrineAwsIamRdsAuthBundle\DoctrineAwsIamRdsAuthBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    FrameworkBundle::class => ['all' => true],
    DoctrineAwsIamRdsAuthBundle::class => ['all' => true],
];
