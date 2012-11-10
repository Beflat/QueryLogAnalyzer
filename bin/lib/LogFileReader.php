<?php



class LogFileReader {
    
    protected $filePath;
    
    protected $fp;
    
    protected $tryBuffer;
    
    public function __construct($filePath=null) {
        $this->filePath = null;
        $this->fp = null;
        $this->tryBuffer = null;
        if($filePath != null) {
            $this->init($filePath);
        }
    }
    
    
    public function init($filePath) {
        $this->filePath = $filePath;
        $this->fp = fopen($filePath, 'r');
        if(!$this->fp) {
            throw new RuntimeException('ファイルのオープンに失敗しました。: ' . $this->filePath);
        }
    }
    
    public function getLine() {
        if($this->tryBuffer !== null) {
            $buffer = $this->tryBuffer;
            $this->tryBuffer = null;
            return $buffer;
        }
        return rtrim(fgets($this->fp, 4096));
    }
    
    /**
     * 次の行を読み込んで返す。カーソルの位置は進めないように振る舞う
     * @return string
     */
    public function tryLine() {
        if($this->tryBuffer !== null) {
            return $this->tryBuffer;
        }
        $this->tryBuffer = rtrim(fgets($this->fp, 4096));
        return $this->tryBuffer;
    }
    
    public function isEof() {
        if($this->tryBuffer !== null) {
            return false;
        }
        return feof($this->fp);
    }
    
}