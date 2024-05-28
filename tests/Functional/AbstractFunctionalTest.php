<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\Functional;

use Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractFunctionalTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
