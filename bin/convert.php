<?php

require_once dirname(__FILE__) . '/config.php';

$parser = new Console_CommandLine();
$parser->description = '';
$parser->version = VERSION;
$parser->addArgument('type', array('description' => "all = alllog, slow = スロークエリログ, req = リクエストログCSV"));
$parser->addArgument('files', array('multiple' => true, 'description' => "入力ファイル一覧"));
$parser->addOption('from', array('long_name' => '--from', 'action' => 'StoreString', 'description' => "解析の開始時刻(strtotimeが解析可能な書式)。"));
$parser->addOption('to',   array('long_name' => '--to',   'action' => 'StoreString', 'description' => "解析の終了時刻(strtotimeが解析可能な書式)。"));


try {
    $parsedParams = $parser->parse();
    
    echo "options:\n";
    var_export($parsedParams->options);
    echo "args:\n";
    var_export($parsedParams->args);
    
} catch(Exception $e) {
    $parser->displayError($e->getMessage());
}


