<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests;

use PHPUnit\Framework\TestCase;

abstract class AbstractDoctrineTestCase extends TestCase
{
    protected function isDoctrine40(): bool
    {
        if (!function_exists('interface_exists')) {
            return class_exists('Doctrine\DBAL\ServerVersionProvider');
        }

        return interface_exists('Doctrine\DBAL\ServerVersionProvider');
    }
}
