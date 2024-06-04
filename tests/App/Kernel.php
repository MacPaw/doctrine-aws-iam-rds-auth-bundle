<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\App;

use Macpaw\DoctrineAwsIamRdsAuthBundle\DependencyInjection\DoctrineAwsIamRdsAuthExtension;
use Macpaw\DoctrineAwsIamRdsAuthBundle\DoctrineAwsIamRdsAuthBundle;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\App\dependencyInjection\PublicCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function getContainerBuilder(): ContainerBuilder
    {
        $builder = parent::getContainerBuilder();
        $builder->addCompilerPass(new PublicCompilerPass());
//        $builder->registerExtension(new DoctrineAwsIamRdsAuthExtension());

        return $builder;
    }
}
