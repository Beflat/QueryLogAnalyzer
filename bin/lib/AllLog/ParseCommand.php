<?php



class AllLog_ParseCommand {
    
    protected $options;
    
    protected $files;
    
    
    public function __construct($files, $options) {
        $this->files = $files;
        $this->options = $options;
    }
    
    /**
     * 入力パラメータの妥当性をチェックする。
     */
    public function validateParams() {
        //TODO:時間や出力形式のオプションは他の入力フォーマットの場合も共通。
        if(isset($this->options['from']) && !strtotime($this->options['from'])) {
            throw new InvalidArgumentException('パラメータ"from"の日付の書式が無効です。: ' . $this->options['from']);
        }
        if(isset($this->options['to']) && !strtotime($this->options['to'])) {
            throw new InvalidArgumentException('パラメータ"to"の日付の書式が無効です。: ' . $this->options['to']);
        }
        
        if(isset($this->options['format']) && !in_array($this->options['format'], array('csv', 'json'))) {
            throw new InvalidArgumentException('パラメータ"format"の値が無効です。: ' . $this->options['format']);
        }
        
        if(!is_array($this->files) || count($this->files) == 0) {
            throw new InvalidArgumentException('ファイルが指定されていません。');
        }
    }
    
    public function run() {
        
        $options = $this->getCleanedOptions($this->options);
        
        $parseResult = array();
        try {
        
            foreach($this->files as $file) {
                //解析の実行。
                $from = ($options['from'] != '') ? strtotime($options['from']) : 0;
                $to = ($options['to']   != '') ? strtotime($options['to'])   : 0;
                $parser = new AllLog_Parser($file, $from, $to);
                $parser->parse();
                $parseResult = array_merge($parseResult, $parser->getResult());
            }
            
            //全ファイルの解析結果をマージした状態のデータをソートする
            uasort($parseResult, array($this, 'sortResultsCallback'));
            
            //ソートした結果を指定されたフォーマットで出力する。
            if($options['format'] == 'csv') {
                $converter = new Converter_Csv($options['output']);
            } else if($options['format'] == 'json') {
                $converter = new Converter_Json($options['output']);
            }
            $converter->convert($parseResult);
            
        } catch(Exception $e) {
            echo "Error:\n";
            echo $e->getMessage()."\n";
            echo "\n\n";
        }
        
    }
    
    
    /**
     * オプションの各値を必要に応じてデフォルト値などで初期化して返す。
     * @param array $options 初期化前のオプション(validateは通っている前提)
     * @return array 初期化済みのオプション 
     */
    protected function getCleanedOptions($options) {
        
        //TODO:各入力フォーマット共通
        if(!isset($options['from'])) {
            $options['from'] = '';
        }
        if(!isset($options['to'])) {
            $options['to'] = '';
        }
        if(!isset($options['format'])) {
            $options['format'] = 'json';
        }
        
        if(!isset($options['output'])) {
            $options['output'] = 'php://output';
        }
        
        return $options;
    }
    
    
    public function sortResultsCallback($a, $b) {
        
        $keyA = $a->getFrom() . '_' . $a->getId();
        $keyB = $b->getFrom() . '_' . $b->getId();
        
        if($keyA === $keyB) {
            return 0;
        }
        return ($keyA < $keyB) ? -1 : 1;
    }
}