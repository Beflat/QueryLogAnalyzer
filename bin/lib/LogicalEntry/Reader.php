<?php


/**
 * ファイルに記録されたLogicalEntryをロードするクラス。
 * ファイルの形式は今のところJSONだけを想定。
 */
class LogicalEntry_Reader {
    
    protected $file;
    
    /**
     * ファイルから読み込んだ、JSONデータを配列化した状態のデータ。
     */
    protected $data;
    
    protected $cursor;
    
    protected $length;
    
    public function __construct($file) {
        $this->file = $file;
        $this->data = null;
        $this->length = 0;
    }
    
    /**
     * @return LogicalEntry 
     */
    public function getEntry() {
        if($this->data === null) {
            $this->loadData();
        }
        
        $entry = new LogicalEntry($this->data[$this->cursor]['id']);
        $entry->setFrom($this->data[$this->cursor]['from']);
        $entry->setTo($this->data[$this->cursor]['to']);
        
        $entry->initExtraWithArray($this->data[$this->cursor]['extra']);
        $this->cursor++;
        return $entry;
    }
    
    public function isEof() {
        return ($this->cursor > $this->length-1);
    }
    
    
    protected function loadData() {
        $contents = file_get_contents($this->file);
        if(!$contents) {
            throw new RuntimeException('ファイルの読み込みに失敗しました。: ' . $this->file);
        }
        
        $this->data = json_decode($contents, true);
        if(!$this->data) {
            throw new RuntimeException('ファイルの解析に失敗しました。: ' . $this->file);
        }
        
        $this->length = count($this->data);
        $this->cursor = 0;
    }
    
}



