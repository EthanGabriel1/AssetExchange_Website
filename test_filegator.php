<?php

require __DIR__ . "/filegator/vendor/autoload.php";

define('APP_PUBLIC_PATH', __DIR__. '/filegator/dist');
define('APP_PUBLIC_DIR', __DIR__. '/filegator/repository/user');
define('APP_VERSION', '7.10.1');
define('APP_ENV', 'production');

use Filegator\Config\Config;
use Filegator\Container\Container;
use Filegator\Kernel\Request;
use Filegator\Kernel\Response;
use Filegator\Kernel\StreamedResponse;

// use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
// use Filegator\Services\Storage\Filesystem;
use League\Flysystem\Exception\FilesystemException;

$adapter = new Local(__DIR__.'/filegator/repository');

$filesystem = new Filesystem($adapter);

if (!$filesystem) {
    die("a");
}

try {
    $listing = $filesystem->listContents("", false);
    
    foreach ($listing as $object) {
        echo $object['basename'] . ' is located at '. $object['path'] . ' and is a ' . $object['type'] . '<br>';
    }
} catch (FilesystemException $exception) {
    echo $exception.getMessage();
}

?>