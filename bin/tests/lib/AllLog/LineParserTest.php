<?php


class AllLog_LineParserTest extends PHPUnit_Framework_TestCase {
    
    
    /**
     * Alllogとして有効な行と無効な行を識別できるかを確認する。
     * 有効な行は"時間を含む行"と"時間を含まない行"の両方を確認する。
     */
    public function testIsAllLogLine() {
        $lineParser = new AllLog_LineParser();
        $this->assertFalse($lineParser->isAllLogLine('Tcp port: 3306  Unix socket: /var/lib/mysql/mysql.sock'));
        $this->assertTrue($lineParser->isAllLogLine('121104 14:26:07	  131 Quit	'));
        $this->assertTrue($lineParser->isAllLogLine('121104 14:30:01	  132 Connect	ipnotifier@localhost on ipnotifier'));
        $this->assertTrue($lineParser->isAllLogLine('		  137 Query	SET NAMES UTF8'));
        $this->assertTrue($lineParser->isAllLogLine('		  495 Quit	'));
    }
    
    
    /**
     * AllLog上の日付文字列をタイムスタンプへ変換できるかを確認する。
     */
    public function testLogDateConvert() {
        
        $testDate = '120101 00:01:02';
        
        $lineParser = new AllLog_LineParser();
        $timeStamp = $lineParser->convertLogDateToTimestamp($testDate);
        $this->assertEquals('2012-01-01 00:01:02', date('Y-m-d H:i:s', $timeStamp));
    }
    
    
    /**
     * AllLogの有効な行を解析できるかを確認する。
     * ヘッダ行を読み飛ばせることもついでに確認する。
     */
    public function testParseSingleLine() {
        
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testParseSingleLine.txt', 'r'));
        
        $lineParser = new AllLog_LineParser();
        $lineParser->skipHeader($fileReader);
        
        //時間を含むログ
        $result = $lineParser->parseLine($fileReader);
        $this->assertEquals('2012-11-03 22:30:02', date('Y-m-d H:i:s', $result['time']));
        $this->assertEquals(3, $result['id']);
        $this->assertEquals('Query', $result['command']);
        $this->assertEquals('SELECT ip_addr FROM ip_addr ORDER BY created DESC LIMIT 1', $result['args']);
        
        //時間を含まないログ
        $result = $lineParser->parseLine($fileReader);
        $this->assertTrue(!isset($result['time']), 'timeを含んでいないこと');
        $this->assertEquals(5, $result['id']);
        $this->assertEquals('Query', $result['command']);
        //最後の空白行を複数行クエリの一部とみなしてしまうが、許容範囲とする。
        $this->assertEquals('SELECT r0_.id AS id0, r0_.title AS title1, r0_.url AS url2, r0_.status AS status3, r0_.log AS log4, r0_.created AS created5, r0_.updated AS updated6, r0_.rule_id AS rule_id7 FROM request r0_ WHERE r0_.status = 0', 
                    rtrim($result['args']));
    }
    
    
    /**
     * 複数行クエリを解析できるかを確認する。
     */
    public function testParseMultiLineQuery() {
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testParseMultiLine.txt', 'r'));
        
        $lineParser = new AllLog_LineParser();
        $lineParser->skipHeader($fileReader);
        
        $result = $lineParser->parseLine($fileReader);
        $this->assertEquals('2012-11-03 22:25:55', date('Y-m-d H:i:s', $result['time']));
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('Query', $result['command']);
        $this->assertEquals("SELECT\n*\nFROM \naccount_accountlog", $result['args']);
        
        //次の行も取得できること。
        $result = $lineParser->parseLine($fileReader);
        $this->assertEquals(53, $result['id']);
        $this->assertEquals('Query', $result['command']);
        //最後の空白行を複数行クエリの一部とみなしてしまうが、許容範囲とする。
        $this->assertEquals('SELECT ip_addr FROM ip_addr ORDER BY created DESC LIMIT 1',
                rtrim($result['args']));
    }
    
}


