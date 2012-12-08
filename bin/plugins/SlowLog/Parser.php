<?php


/**
 * 1つのスロークエリログファイルの内容を、クエリ単位で解析するクラス。
 */
class SlowLog_Parser {
    
    protected $filePath;
    
    /**
     * 
     * @var TriableStreamReader
     */
    protected $fileReader;
    
    protected $from;
    
    
    protected $to;
    
    /**
     * 解析結果の配列。
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
        
        $lineParser = new SlowLog_LineParser();
        //最初のヘッダー部分を読み飛ばす
        $lineParser->skipHeader($this->fileReader);
        $lineParser->skipUntilTime($this->fileReader, $this->from);
        
        $currentTime = 0;
        $this->results = array();
        while(!$this->fileReader->isEof()) {
            $parsedLine = $lineParser->parseLine($this->fileReader);
            if(isset($parsedLine['to'])) {
                $currentTime = $parsedLine['to'];
            }
            
            if($this->from > 0 && $currentTime < $this->from) {
                continue;
            }
            if($this->to > 0 && $currentTime > $this->to) {
                break;
            }
            
            if(!isset($parsedLine['to'])) {
                $parsedLine['to'] = $currentTime;
                $parsedLine['from'] = $parsedLine['to'] - $parsedLine['elapsed'];
            }
            
            $entry = new LogicalEntry();
            $entry->setFrom((int)$parsedLine['from']);
            $entry->setTo($parsedLine['to']);
            $entry->setElapsed($parsedLine['elapsed']);
            
            $entry->setExtra('host', $parsedLine['host']);
            $entry->setExtra('user', $parsedLine['user']);
            $entry->setExtra('lock_time', $parsedLine['lock_time']);
            $entry->setExtra('rows_sent', $parsedLine['rows_sent']);
            $entry->setExtra('rows_examined', $parsedLine['rows_examined']);
            $entry->setExtra('query', $parsedLine['query']);
            
            $this->results[] = $entry;
        }
    }
    
    public function getResult() {
        return $this->results;
    }
}

