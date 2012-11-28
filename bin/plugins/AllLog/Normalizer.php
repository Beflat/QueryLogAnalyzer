<?php


/**
 * AllLogの内容をLogicalEntryへ変換するためのクラス。
 */
class AllLog_Normalizer extends Normalizer_Plugin {
    
    protected $options;
    
    protected $result;
    
    public function __construct($options) {
        $this->options = $options;
        $this->result = array();
    }
    
    public function getName() {
        return 'AllLog_Normalizer';
    }
    
    public function initPlugin($options) {
        super::initPlugin($options);
    }
    
    public function getCommandDefinition() {
        return array(
            'name' => 'all',
            'options' => array(
                'output' => array('long_name' => '--output', 'action' => 'StoreString', 'description' => "出力ファイル名。 default=標準出力"),
                'format' => array('long_name' => '--format', 'action' => 'StoreString', 'choices' => array('csv','json'), 'description' => "出力形式(csv,json)。default: json"),
                'from'   => array('long_name' => '--from', 'action' => 'StoreString', 'description' => "解析の開始時刻(strtotimeが解析可能な書式)。"),
                'to'     => array('long_name' => '--to',   'action' => 'StoreString', 'description' => "解析の終了時刻(strtotimeが解析可能な書式)。"),
            ),
            'arguments' => array(),
        );
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
    }
    
    public function normalize($files) {
        
        $options = $this->getCleanedOptions($this->options);
        
        foreach($files as $file) {
            //解析の実行。
            $from = ($options['from'] != '') ? strtotime($options['from']) : 0;
            $to = ($options['to']   != '') ? strtotime($options['to'])   : 0;
            $parser = new AllLog_Parser($file, $from, $to);
            $parser->parse();
            $this->result = array_merge($this->result, $parser->getResult());
        }
        
        //全ファイルの解析結果をマージし、"[開始時間]_[セッションID]"の昇順でソートする。
        //(これにより、複数のファイルのログを結合した結果が時系列順でソートされる)
        uasort($this->result, array($this, 'sortResultsCallback'));
    }
    
    
    public function getResult() {
        return $this->result;
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