#!/usr/bin/php -dphar.readonly=0
<?php
$buildRoot = realpath(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($buildRoot, \FilesystemIterator::SKIP_DOTS),
    \RecursiveIteratorIterator::LEAVES_ONLY
);

echo "Build Config-manager tool" . PHP_EOL;
$phar = new Phar($buildRoot.'/config-diff.phar');
$phar->buildFromIterator($iterator, $buildRoot);
$phar->setStub("#!/usr/bin/env php" . PHP_EOL .$phar->createDefaultStub("run.php"));
exit();