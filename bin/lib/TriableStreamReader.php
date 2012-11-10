<?php


/**
 * ストリーム上の"次の行"を読むことをサポートするクラス。
 * TODO: ファイルディスクリプタではなくStreamReaderInterfaceを実装したクラスを入力として受け取るようにする。
 */
class TriableStreamReader {
    
    /**
     * ファイルディスクリプタ
     * @var resource
     */
    protected $fd;
    
    /**
     * tryLine()が読み込んだ"次の行"の内容を格納しておく変数
     * @var string
     */
    protected $tryBuffer;
    
    /**
     * 
     * @param resource $fd
     * @throws RuntimeException
     */
    public function __construct($fd) {
        
        if(!$fd) {
            throw new RuntimeException('無効なファイルディスクリプタが指定されました。');
        }
        
        $this->fd = $fd;
        $this->tryBuffer = null;
    }
    
    /**
     * 現在行を読み込んで返す。カーソルを進める。
     * @return string
     */
    public function getLine() {
        if($this->tryBuffer !== null) {
            $buffer = $this->tryBuffer;
            $this->tryBuffer = null;
            return $buffer;
        }
        return rtrim(fgets($this->fd, 4096));
    }
    
    /**
     * 次の行を読み込んで返す。カーソルの位置は進めないように振る舞う
     * @return string
     */
    public function tryLine() {
        if($this->tryBuffer !== null) {
            return $this->tryBuffer;
        }
        $this->tryBuffer = rtrim(fgets($this->fd, 4096));
        return $this->tryBuffer;
    }
    
    /**
     * ファイルが終端まで来たかどうかを返す。
     * @return boolean
     */
    public function isEof() {
        if($this->tryBuffer !== null) {
            return false;
        }
        return feof($this->fd);
    }
    
}