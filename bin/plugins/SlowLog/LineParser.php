<?php

class SlowLog_LineParser {
    
    public function skipHeader(TriableStreamReader $fileReader) {
        
        while(!$fileReader->isEof()) {
            $try = $fileReader->tryLine();
            if($this->isSlowLogLine($try)) {
                break;
            }
            //カーソルを進める。
            $fileReader->getLine();
        }
    }
    
    
    public function skipUntilTime(TriableStreamReader $fileReader, $startTime) {
        while(!$fileReader->isEof()) {
            $try = $fileReader->tryLine();
            $matched = array();
            if(!preg_match('/^# Time: ([0-9]+\s+[0-9:]+)/', $try, $matched)) {
                $fileReader->getLine();
                continue;
            }
            
            $timeStamp = $this->convertLogTimeToTimeStamp($matched[1]);
            if($timeStamp >= $startTime) {
                return;
            }
            
            //カーソルを進める。
            $fileReader->getLine();
        }
    }
    
    public function isSlowLogLine($line) {
        if(preg_match('/^# Time: /', $line)) {
            return true;
        }
        
        if(preg_match('/# User@Host: /', $line)) {
            return true;
        }
        
        return false;
    }
    
    
    public function parseLine(TriableStreamReader $reader) {
        $result = array();
        while(!$reader->isEof()) {
            $line = $reader->getLine();
            
            $matched = array();
            if(preg_match('/^# Time: (?<time>[0-9]+\s+[0-9:]+)/', $line, $matched)) {
                $result['to'] = $this->convertLogTimeToTimeStamp($matched['time']);
            } else if(preg_match('/^# User@Host: (?<user>[a-z0-9\._\-]+\[[a-z0-9\._\-]*\]) @ (?<host>[a-z0-9\._\-]+ \[[a-z0-9\._\-]*\])/i', $line, $matched)) {
                $result['user'] = $matched['user'];
                $result['host'] = $matched['host'];
            } else if(preg_match('/^# Query_time: (?<elapsed>[0-9\.]+)  Lock_time: (?<lock_time>[0-9\.]+) Rows_sent: (?<rows_sent>[0-9]+)  Rows_examined: (?<rows_examined>[0-9]+)/i', $line, $matched)) {
                $result['elapsed'] = $matched['elapsed'];
                $result['lock_time'] = $matched['lock_time'];
                $result['rows_sent'] = $matched['rows_sent'];
                $result['rows_examined'] = $matched['rows_examined'];
            } else {
                //ここまで来たということは、クエリの行に来たと判断する。
                break;
            }
        }
        
        //ヘッダの解析が終わっていれば、開始時刻が分かるはず。
        if(isset($result['to'])) {
            $result['from'] = $result['to'] - $result['elapsed'];
        }
        
        //クエリ部分の解析。
        $result['query'] = $line;
        while(!$reader->isEof()) {
            //試しに次の行を読んでみる。
            $try = $reader->tryLine();
            if($this->isSlowLogLine($try)) {
                //Slowログの行であった場合は、解析終了。
                break;
            }
            $result['query'] .= "\n" . $try;
            $reader->getLine();
        }
        return $result;
    }
    
    public function convertLogTimeToTimeStamp($logTime) {
        $year = substr($logTime, 0, 2);
        $mon  = substr($logTime, 2, 2);
        $day  = substr($logTime, 4, 2);
        $time = substr($logTime, 7, 8);
        
        return strtotime(sprintf('20%02d-%02d-%02d %s', $year, $mon, $day, $time));
    }
}

