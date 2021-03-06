<?php


/**
 * AllLogの各行に対する解析関連の処理をpublicメソッドとして持つクラス。
 */
class AllLog_LineParser {
    
    /**
     * Allログ先頭の無関係な行をスキップする。
     * @param TriableStreamReader $fileReader
     * @return void
     */
    public function skipHeader($fileReader) {
        while(!$fileReader->isEof()) {
            //次の行を調べる(カーソルは進めない)
            $try = $fileReader->tryLine();
            if($this->isAllLogLine($try)) {
                //AllLogとして有効な行の場合は処理を抜ける
                break;
            }
            //次の行へ実際にカーソルを進める
            $fileReader->getLine();
        }
    }
    
    /**
     * 指定された時間まで読み飛ばす。
     * @param  [type] $startTime [description]
     * @return [type]            [description]
     */
    public function skipUntilTime($fileReader, $startTime) {
        while(!$fileReader->isEof()) {
            //次の行を調べる(カーソルは進めない)
            $try = $fileReader->tryLine();
            
            $matched = array();
            if(!preg_match('#^([0-9]+\s+[0-9:]+)#', $try, $matched)) {
                //時刻ではない行の場合は次の行へスキップ
                $fileReader->getLine();
                continue;
            }
            
            //時刻らしい行を見つけた
            $time = $this->convertLogDateToTimeStamp($matched[1]);
            if($time < $startTime) {
                //指定の時刻よりも前であれば次の行へスキップ。
                $fileReader->getLine();
                continue;
            }
            //指定時刻を過ぎた時点で処理を抜ける。
            return;
        }
    }
    
    
    /**
     * 渡された行がAllログのフォーマットとして有効かどうかを判定する
     * @param string $line
     * @return boolean 
     */
    public function isAllLogLine($line) {
        //日付を含むAllLogの行データか
//        if(preg_match('#^[0-9]+ [\s0-9:]+?\s+[0-9]+\s[\w ]+?\t#', $line)) {
        if(preg_match('#^[0-9]+\s+[0-9:]+\s*[0-9]+\s[\w ]+\t#', $line)) {
            return true;
        }
        //日付を含まないAllLogの行データか
        if(preg_match('#^\t\t\s*[0-9]+\s[\w ]+\t#', $line)) {
            return true;
        }
        return false;
    }
    
    /**
     * ログを1行解析し、結果を連想配列で返す。
     * @param TriableStreamReader $fileReader
     * @return array
     * 
     * 戻り値の例：
     * timeは、ログ中に表記がない場合は省略される。
     * array(
     *     'time'    => ログ時刻
     *     'id'      => セッションID
     *     'command' => Connect, Queryなどのコマンド
     *     'args'    => クエリなど
     * )
     */
    public function parseLine($fileReader) {
        $line = $fileReader->getLine();
        $result = array();
        //時刻を含む構文でマッチするか確認する。
        if(!preg_match('#^(?<time>[0-9]+\s+[0-9:]+)\s*(?<id>[0-9]+)\s(?<command>[\w ]+)\t(?<args>.*)$#', $line, $result)) {
            //上の構文でマッチしない場合、時刻なしバージョンでマッチするか確認する。
            if(!preg_match('#^\t\t\s*(?<id>[0-9]+)\s(?<command>[\w ]+)\t(?<args>.*)$#', $line, $result)) {
                //これでもマッチしない場合はエラー
                throw new UnexpectedValueException('無効なフォーマットです。Line:' . $fileReader->getLineNo()."\n" . $fileReader->getLine());
            }
        }
        
        if(isset($result['time'])) {
            //日付文字列をUnixタイムスタンプに変換
            $result['time'] = $this->convertLogDateToTimeStamp($result['time']);
        }
        
        if($fileReader->isEof()) {
            return $result;
        }
        
        //複数行クエリへの対応
        //非常に長いクエリの可能性もあるのでとりあえず10行まで
        $lineCount = 0;
        while(!$fileReader->isEof()) {
            //次の行を試しに調べる(カーソルは進めない)。
            $try = $fileReader->tryLine();
            if($this->isAllLogLine($try)) {
                //次の行がAllLogとして有効な行であった場合はそこまでで解析を中止する。
                return $result;
            }
            $append = $fileReader->getLine();
            if($lineCount < 10) {
                $result['args'] .= "\n" . $append;
            }
            $lineCount++;
        }
        return $result;
    }
    
    
    /**
     * AllLog上の日付をUnixタイムスタンプに変換する。
     * @param string $logDate
     * @return int
     */
    public function convertLogDateToTimestamp($logDate) {
        $year = substr($logDate, 0, 2);
        $mon  = substr($logDate, 2, 2);
        $day  = substr($logDate, 4, 2);
        $time = substr($logDate, 7, 8);
        
        return strtotime(sprintf('20%d-%02d-%02d %s', $year, $mon, $day, $time));
    }
}

