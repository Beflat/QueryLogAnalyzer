<?php


/**
 * AllLogを解析してコネクション別にまとめた
 */
class AllLog_Parser {
    
    protected $filePath;
    
    /**
     * 
     * @var TriableStreamReader
     */
    protected $fileReader;
    
    protected $from;
    
    
    protected $to;
    
    /**
     * 解析結果の配列。キーはAlllogのセッションID。
     * @var array
     */
    protected $results;
    
    
    public function __construct($filePath, $from=0, $to=0) {
        $this->filePath = $filePath;
        $this->from = $from;
        $this->to = $to;
        $this->fileReader = new TriableStreamReader(fopen($this->filePath, 'r'));
    }
    
    
    public function parse() {
        
        $lineParser = new AllLog_LineParser();
        //最初のヘッダー部分を読み飛ばす
        $lineParser->skipHeader($this->fileReader);
        
        $currentTime = 0;
        
        while(!$this->fileReader->isEof()) {
            $parsedLine = $lineParser->parseLine($this->fileReader);
            if(isset($parsedLine['time'])) {
                $currentTime = $parsedLine['time'];
            }
            
            if($this->from > 0 && $currentTime < $this->from) {
                continue;
            }
            if($this->to > 0 && $currentTime > $this->to) {
                break;
            }
            
            $sessionId = $parsedLine['id'];
            if(!isset($this->results[$sessionId])) {
                $this->results[$sessionId] = new LogicalEntry($sessionId);
                $this->results[$sessionId]->setFrom($currentTime);
                $this->results[$sessionId]->setSortKey($currentTime . '_' . $sessionId);
            }
            
            $this->results[$sessionId]->setExtra($parsedLine['command'], $parsedLine['args']);
            if($parsedLine['command'] == 'Quit') {
                //コネクションの終了時刻を記録する。
                $this->results[$sessionId]->setTo($currentTime);
            }
        }
        
        //全ての走査が終わった後で、この時点で終了時刻が記録されていないログに
        //終了時刻を記録する。
        foreach($this->results as $id=>$unused) {
            if($this->results[$id]->getTo() != 0) {
                continue;
            }
            $this->results[$id]->setTo($currentTime);
        }
    }
    
    public function getResult() {
        return $this->results;
    }
}

