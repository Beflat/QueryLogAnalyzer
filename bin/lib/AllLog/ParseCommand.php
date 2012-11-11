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
        if(isset($this->options['from']) && !strtotime($this->options['from'])) {
            throw new InvalidArgumentException('パラメータ"from"の日付の書式が無効です。: ' . $this->options['from']);
        }
        if(isset($this->options['to']) && !strtotime($this->options['to'])) {
            throw new InvalidArgumentException('パラメータ"to"の日付の書式が無効です。: ' . $this->options['to']);
        }
        
        if(!is_array($this->files) || count($this->files) == 0) {
            throw new InvalidArgumentException('ファイルが指定されていません。');
        }
    }
    
    public function run() {
        
        foreach($this->files as $file) {
        }
    }
    
    
    
}