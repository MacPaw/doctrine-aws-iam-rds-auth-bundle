<?php

declare(strict_types=1);

use Nette\Neon\Neon;

require_once __DIR__ . '/../vendor/autoload.php';

$doctrineVersion = class_exists('Doctrine\DBAL\ServerVersionProvider') ? '3.0' : '2';

if ('3.0' === $doctrineVersion) {
    $skipPath = __DIR__ . '/../src/Doctrine/Driver/IamDecorator.php';
} else {
    $skipPath = __DIR__ . '/../src/Doctrine/Driver/IamDecoratorDoctrine30.php';
}

$neonFile = __DIR__ . '/../phpstan.neon';

$neonData = Neon::decodeFile($neonFile);
$neonData['parameters']['excludePaths'] = [
    realpath($skipPath),
];

file_put_contents($neonFile, Neon::encode($neonData, true));

$xmlFiles =[
    __DIR__ . '/../phpunit.xml',
    __DIR__ . '/../phpunit.xml.dist',
];

foreach ($xmlFiles as $xmlFile) {
    if (!file_exists($xmlFile)) {
        continue;
    }

    $content = file_get_contents($xmlFile);
    $content = preg_replace(
        '#<exclude>.+</exclude>#s',
        <<<EOL
<exclude>
            <file>$skipPath</file>
        </exclude>
EOL
        ,
        $content,
    );
    file_put_contents(
        $xmlFile,
        $content,
    );
}
