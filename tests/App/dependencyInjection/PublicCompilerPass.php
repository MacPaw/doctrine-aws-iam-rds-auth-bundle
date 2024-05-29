<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\App\dependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final readonly class PublicCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var Definition[] $definitions */
        $definitions = $container->getDefinitions();

        foreach ($definitions as $name => $definition) {
            $class = $definition->getClass();

            if (!str_starts_with(strtolower($class ?? $name), 'macpaw\\')) {
                continue;
            }

            $definition->setPublic(true);
        }
    }
}
