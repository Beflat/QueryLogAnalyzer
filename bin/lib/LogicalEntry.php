<?php


/**
 * ログの中間表現
 */
class LogicalEntry {
    
    /**
     * ID
     * @var string
     */
    protected $id;
    
    /**
     * ログの開始時間(Unitタイムスタンプ、またはマイクロ秒など、単位は利用方法により異なる)
     * @param int
     */
    protected $from;
    
    
    /**
     * ログの終了時間(Unitタイムスタンプ、またはマイクロ秒など、単位は利用方法により異なる)
     * @param int
     */
    protected $to;
    
    /**
     * 経過時間(単位は秒やマイクロ秒など、利用方法により異なる)
     * @param float
     */
    protected $elapsed;
    
    /**
     * 利用用途毎に格納されるオプションのデータ
     * @param array
     */
    protected $extraData;
    
    /**
     * ソートするに使用する値。秒単位のログの場合は"時間_セッションID"など。
     * @var string
     */
    protected $sortKey;
    
    
    public function __construct($id) {
        $this->id = $id;
        $this->from = 0;
        $this->to = 0;
        $this->elapsed = 0;
        $this->extraData = array();
        $this->sortKey = '';
    }
    
    public function setFrom($from) {
        $this->from = $from;
    }
    
    
    public function setTo($to) {
        $this->to = $to;
    }
    
    public function setElapsed($elapsed) {
        $this->elapsed = $elapsed;
    }
    
    public function setExtra($key, $value) {
        if(isset($this->extraData[$key])) {
            if(is_array($this->extraData[$key])) {
                //TODO: 大量のレコード追加を防ぐために、ここで防止する処理が必要かもしれない。
                $this->extraData[$key][] = $value;
            } else {
                $this->extraData[$key] = array($this->extraData[$key], $value);
            }
            return;
        }
        $this->extraData[$key] = $value;
    }
    
    public function initExtraWithArray($newArray) {
        $this->extraData = $newArray;
    }
    
    
    
    public function getId() {
        return $this->id;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    
    public function getTo() {
        return $this->to;
    }
    
    public function getElapsed() {
        return $this->elapsed;
    }
    
    public function getAllExtra() {
        return $this->extraData;
    }
    
    public function getExtra($key, $default='') {
        if(!isset($this->extraData[$key])) {
            return $default;
        }
        return $this->extraData[$key];
    }
    
    
}

