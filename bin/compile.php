<?php

$pharPath = 'package.phar';
$extractPath = './extract';
$files = array(
    'index.php',
    'config.php',
    'lib',
);


try {

    $phar = new Phar($pharPath, 0, basename($pharPath));

    if($argv[1] == '-d') {

        $phar->extractTo($extractPath);

    } else {
        foreach($files as $file) {
            if(is_dir($file)) {
                $phar->buildFromIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file)), $file);
            } else {
                $phar[$file] = file_get_contents($file);
            }
        }
        
    }

    echo "Done.\n";


} catch(Exception $e) {
    echo "Error:\n";
    echo $e->getMessage()."\n";
}

