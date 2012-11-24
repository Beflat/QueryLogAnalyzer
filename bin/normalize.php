<?php

require_once dirname(__FILE__) . '/config.php';

$parser = new Console_CommandLine();
$parser->description = '';
$parser->version = VERSION;
$parser->addArgument('type', array('description' => "all: alllog, slow: スロークエリログ, req: リクエストログCSV"));
$parser->addArgument('files', array('multiple' => true, 'description' => "入力ファイル一覧"));
$parser->addOption('output', array('long_name' => '--output', 'action' => 'StoreString', 'description' => "出力ファイル名。 default=標準出力"));
$parser->addOption('format', array('long_name' => '--format', 'action' => 'StoreString', 'choices' => array('csv','json'), 'description' => "出力形式(csv,json)。default: json"));
$parser->addOption('from', array('long_name' => '--from', 'action' => 'StoreString', 'description' => "解析の開始時刻(strtotimeが解析可能な書式)。"));
$parser->addOption('to',   array('long_name' => '--to',   'action' => 'StoreString', 'description' => "解析の終了時刻(strtotimeが解析可能な書式)。"));


try {
    $parsedParams = $parser->parse();
    
    
    switch($parsedParams->args['type']) {
        case 'all':
            $normalizer = new AllLog_Normalizer($parsedParams->args['files'], $parsedParams->options);
            $normalizer->validateParams();
            $normalizer->normalize();
            break;
        default:
            throw new UnexpectedValueException('typeの指定が実装されていない、または無効です。: ' . $parsedParams->args['type']);
    }
    
    //ソートした結果を指定されたフォーマットで出力する。
    $format = isset($parsedParams->options['format']) ? $parsedParams->options['format'] : 'json';
    $output = isset($parsedParams->options['output']) ? $parsedParams->options['output'] : 'php://output';
    switch($format) {
        case 'csv':
            $converter = new LogicalEntry_Converter_Csv($parsedParams->options['output']);
            break;
        default:
            //無効な指定を含め、デフォルトはJSON形式とする。
            $converter = new LogicalEntry_Converter_Json($parsedParams->options['output']);
            break;
    }
    $converter->convert($normalizer->getResult());
    
} catch(Exception $e) {
    echo "\n---------------------------------------------------\n";
    echo "Error: \n";
    echo $e->getMessage() . "\n";
    echo "\n---------------------------------------------------\n";
    $parser->displayUsage();
}


