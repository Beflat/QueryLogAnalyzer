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
        $line = $reader->getLine();
        
        
    }
    
    public function convertLogTimeToTimeStamp($logTime) {
        $year = substr($logTime, 0, 2);
        $mon  = substr($logTime, 2, 2);
        $day  = substr($logTime, 4, 2);
        $time = substr($logTime, 7, 8);
        
        return strtotime(sprintf('20%02d-%02d-%02d %s', $year, $mon, $day, $time));
    }
}

