<?php


class TimeBasedLog_Converter {
    protected $options;
    
    protected $file;
    
    
    public function __construct($file, $options) {
        $this->file = $file;
        $this->options = $options;
    }
    
    /**
     * 入力パラメータの妥当性をチェックする。
     */
    public function validateParams() {
        if(isset($this->options['format']) && !in_array($this->options['format'], array('csv', 'json'))) {
            throw new InvalidArgumentException('パラメータ"format"の値が無効です。: ' . $this->options['format']);
        }
        
        if(trim($this->file) == '' ) {
            throw new InvalidArgumentException('ファイルが指定されていません。');
        }
    }
    
    public function convert() {
        
        $options = $this->getCleanedOptions($this->options);
        
        $result = array();
        $reader = new LogicalEntry_Reader($this->file);
        $builder = new TimeBasedLog_Builder();
        
        $builder->init($options['threshold']);
        while(!$reader->isEof()) {
            $entry = $reader->getEntry();
            $builder->addEntry($entry);
        }
        
        //TODO: 保存関連の処理はこのクラスの外へ処理を移動する
        $result = $builder->getScaledResult($options['scale'], $options['scale_mode']);
        $convertedResult = $this->convertResult($result, $options['format']);
        
        if(!file_put_contents($options['output'], $convertedResult)) {
            throw new RuntimeException('ファイルの保存に失敗しました。: ' . $options['output']);
        }
    }
    
    
    /**
     * 結果データをJSON,CSVに変換してファイルに保存する。
     * フォーマットが増える場合やメモリの消費が気になる場合は別のクラスに移動して実行する。
     * @param unknown_type $mode
     */
    public function convertResult($input, $mode) {
        
        $buffer = '';
        switch($mode) {
        case 'json':
            $data = array();
            foreach($input as $sec=>$value) {
                $data[] = array('time' => $sec, 'value'=> $value);
            }
            $buffer = json_encode($data);
            break;
        case 'csv':
            foreach($input as $sec=>$value) {
                $buffer .= sprintf('%s,%s,%s', $sec, date('Y-m-d H:i:s', $sec), $value)."\r\n";
            }
            break;
        }
        return $buffer;
    }
    
    
    /**
     * オプションの各値を必要に応じてデフォルト値などで初期化して返す。
     * @param array $options 初期化前のオプション(validateは通っている前提)
     * @return array 初期化済みのオプション 
     */
    protected function getCleanedOptions($options) {
        
        if(!isset($options['format'])) {
            $options['format'] = 'json';
        }
        
        if(!isset($options['output'])) {
            $options['output'] = 'php://output';
        }
        if(!isset($options['threshold'])) {
            $options['threshold'] = 0;
        }
        if(!isset($options['scale'])) {
            $options['scale'] = 1;
        }
        if(!isset($options['scale_mode'])) {
            $options['scale_mode'] = TimeBasedLog_Builder::SCALE_MODE_MAX;
        }
        
        return $options;
    }
    
    
}
