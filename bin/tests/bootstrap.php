<?php

require_once dirname(__FILE__) . '/../lib/SplClassLoader.php';

$pearClassLoader = new SplClassLoader(dirname(__FILE__) . '/../lib/vendors');
$pearClassLoader->registerAsPear();

$generalClassLoader = new SplClassLoader(dirname(__FILE__) . '/../lib');
$generalClassLoader->register();

$pluginClassLoader = new SplClassLoader(dirname(__FILE__) . '/../plugins');
$pluginClassLoader->register();
