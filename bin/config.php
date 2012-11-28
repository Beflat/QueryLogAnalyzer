<?php

//auto loadの設定関連
require_once 'lib/SplClassLoader.php';

//PEARのauto load 設定
$pearClassLoader = new SplClassLoader(dirname(__FILE__) . '/lib/vendors');
$pearClassLoader->registerAsPear();

//その他のクラスのauto load 設定。
$generalClassLoader = new SplClassLoader(dirname(__FILE__) . '/lib');
$generalClassLoader->register();


//定数
define('VERSION', 0.1);

define('PLUGIN_DIR', dirname(__FILE__) . '/plugins.cfg');
