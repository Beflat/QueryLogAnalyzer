<?php

require_once dirname(__FILE__) . '/config.php';

$parser = new Console_CommandLine();
$parser->description = '';
$parser->version = VERSION;
$parser->addArgument('file', array('description' => "入力ファイル(normalize.phpの出力結果)"));
$parser->addOption('threshold', array('long_name' => '--threshold', 'action' => 'StoreInt', 'description' => "何秒以上のログを対象とするか。 default: 0"));
$parser->addOption('scale', array('long_name' => '--scale', 'action' => 'StoreInt', 'description' => "集計の粒度(単位：秒)。60=1分単位。default: 1"));
$parser->addOption('scale_mode', array('long_name' => '--scale-mode', 'action' => 'StoreString', 'description' => "集計の方法(sum|min|max|avg)。default: sum"));
$parser->addOption('output', array('long_name' => '--output', 'action' => 'StoreString', 'description' => "出力ファイル名。 default: 標準出力"));
$parser->addOption('format', array('long_name' => '--format', 'action' => 'StoreString', 'description' => "出力形式(csv,json)。default: csv"));

if(!isset($argv[1]) || trim($argv[1]) == '') {
    $parser->displayUsage();
    exit;
}

try {
    $parsedParams = $parser->parse();
    
    
    $converter = new TimeBasedLog_Converter($parsedParams->args['file'], $parsedParams->options);
    $converter->validateParams();
    $converter->convert();
    
} catch(Exception $e) {
    echo "Error: \n";
    echo $e->getMessage() . "\n";
}

