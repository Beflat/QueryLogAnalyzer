<?php

require_once dirname(__FILE__) . '/config.php';

$parser = new Console_CommandLine();
$parser->description = '';
$parser->version = VERSION;
$parser->addArgument('type', array('description' => "all: alllog, slow: スロークエリログ, req: リクエストログCSV"));
$parser->addArgument('files', array('multiple' => true, 'description' => "入力ファイル一覧"));
$parser->addOption('output', array('long_name' => '--output', 'action' => 'StoreString', 'description' => "出力ファイル名。 default=標準出力"));
$parser->addOption('format', array('long_name' => '--format', 'action' => 'StoreString', 'description' => "出力形式(csv,json)。default: json"));
$parser->addOption('from', array('long_name' => '--from', 'action' => 'StoreString', 'description' => "解析の開始時刻(strtotimeが解析可能な書式)。"));
$parser->addOption('to',   array('long_name' => '--to',   'action' => 'StoreString', 'description' => "解析の終了時刻(strtotimeが解析可能な書式)。"));


try {
    $parsedParams = $parser->parse();
    
    
    switch($parsedParams->args['type']) {
        case 'all':
            $command = new AllLog_ParseCommand($parsedParams->args['files'], $parsedParams->options);
            $command->validateParams();
            $command->run();
            break;
        default:
            throw new UnexpectedValueException('typeの指定が実装されていない、または無効です。: ' . $parsedParams->args['type']);
    }
    
} catch(Exception $e) {
    echo "\n---------------------------------------------------\n";
    echo "Error: \n";
    echo $e->getMessage() . "\n";
    echo "\n---------------------------------------------------\n";
    $parser->displayUsage();
}


