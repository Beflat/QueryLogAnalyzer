<?php



class SlowLog_LineParserTest extends PHPUnit_Framework_TestCase {
    
    
    public function testIsSlowLogLine() {
        $lineParser = new SlowLog_LineParser();
        
        $this->assertTrue($lineParser->isSlowLogLine('# Time: 121130  2:30:02'));
        $this->assertTrue($lineParser->isSlowLogLine('# Time: 121130 02:30:02'));
        $this->assertTrue($lineParser->isSlowLogLine('# User@Host: spadmin[spadmin] @  [172.16.8.21]'));
        $this->assertTrue($lineParser->isSlowLogLine('# User@Host: user123[user123] @  [test-server.com]'));
        $this->assertFalse($lineParser->isSlowLogLine('Time                 Id Command    Argument'));
        $this->assertFalse($lineParser->isSlowLogLine(''));
        
    }
    
    public function testSkipHeader() {
        
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testSkipHeader.txt', 'r'));
        
        $lineParser = new SlowLog_LineParser();
        $lineParser->skipHeader($fileReader);
        
        $this->assertEquals(4, $fileReader->getLineNo());
    }
    
    
    public function testSkipUntilTime() {
        $lineParser = new SlowLog_LineParser();
        
        //指定した時間を超過した最初のログまでスキップする。
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testSkipUntilTime.txt', 'r'));
        $lineParser->skipHeader($fileReader);
        $lineParser->skipUntilTime($fileReader, strtotime('2012-11-24 02:15:58'));
        $this->assertEquals(19, $fileReader->getLineNo());
        
        //指定時間ちょうどのログがある場合はその行で止まる。
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testSkipUntilTime.txt', 'r'));
        $lineParser->skipUntilTime($fileReader, strtotime('2012-11-24 02:16:00'));
        $this->assertEquals(19, $fileReader->getLineNo());
        
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testSkipUntilTime.txt', 'r'));
        $lineParser->skipUntilTime($fileReader, strtotime('2012-11-24 02:16:01'));
        $this->assertEquals(32, $fileReader->getLineNo());
    }
    
    
    public function testConvertLogDateToTimeStamp() {
        
        $lineParser = new SlowLog_LineParser();
        $timeStamp = $lineParser->convertLogTimeToTimeStamp('121201  1:00:00');
        $this->assertEquals('2012-12-01 01:00:00', date('Y-m-d H:i:s', $timeStamp));
        
        $timeStamp = $lineParser->convertLogTimeToTimeStamp('120112 15:10:05');
        $this->assertEquals('2012-01-12 15:10:05', date('Y-m-d H:i:s', $timeStamp));
    }
    
    public function testParse1Line() {
        $lineParser = new SlowLog_LineParser();
        
        //指定した時間を超過した最初のログまでスキップする。
        $fileReader = new TriableStreamReader(fopen(dirname(__FILE__) . '/testParse1Line.txt', 'r'));
        $lineParser->skipHeader($fileReader);
        
        $parsedData = $lineParser->parseLine($fileReader);
        
        $this->assertEquals('2012-11-24 02:15:49', date('Y-m-d H:i:s', $parsedData['from']));
        $this->assertEquals('2012-11-24 02:15:53', date('Y-m-d H:i:s', $parsedData['to']));
        $this->assertEquals('root[root]', $parsedData['user']);
        $this->assertEquals('localhost []', $parsedData['host']);
        $this->assertEquals('3.070642', $parsedData['elapsed']);
        $this->assertEquals('0.000150', $parsedData['lock_time']);
        $this->assertEquals('1', $parsedData['rows_sent']);
        $this->assertEquals('10', $parsedData['rows_examined']);
        $this->assertEquals("use mysqlslap;\nSET timestamp=1353690953;\nCREATE TABLE `t1` \n", $parsedData['query']);
    }
    
}


