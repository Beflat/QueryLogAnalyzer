<?php


class SlowLog_ParserTest extends PHPUnit_Framework_TestCase {
    
    /**
     * 特に期間等を指定しない通常の解析が実行できることを確認する。
     */
    public function testParse() {
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testParse.txt');
        $parser->parse();
        
        $results = $parser->getResult();
        
        $this->assertCount(2, $results);
        $this->assertEquals('LogicalEntry', get_class($results[0]));
        $this->assertEquals('2012-11-24 02:15:49', date('Y-m-d H:i:s', $results[0]->getFrom()));
        $this->assertEquals('2012-11-24 02:15:53', date('Y-m-d H:i:s', $results[0]->getTo()));
    }
    
    
    /**
     * from(開始時刻)を指定して解析が実行できることを確認する。
     */
    public function testFromParameter() {
        
        //fromで指定した時刻以降のログが存在しない場合、結果は空の配列になる。
        $from = strtotime('2012-11-24 02:16:05');
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testFrom.txt', $from);
        $parser->parse();
        
        $results = $parser->getResult();
        $this->assertCount(0, $results);

        //fromで指定した時刻ちょうどのログが存在する場合、その行を含めた後続の行が解析の対象になることを確認する。
        $from = strtotime('2012-11-24 02:16:04');
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testFrom.txt', $from);
        $parser->parse();
        
        $results = $parser->getResult();
        
        $this->assertEquals('2012-11-24 02:16:04', date('Y-m-d H:i:s', $results[0]->getTo()));
        
        //fromで指定した時刻ちょうどでなくても、指定時刻以降の行を含めた結果が取得できることを確認する。
        $from = strtotime('2012-11-24 02:16:03');
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testFrom.txt', $from);
        $parser->parse();
        
        $results = $parser->getResult();
        
        $this->assertEquals('2012-11-24 02:16:04', date('Y-m-d H:i:s', $results[0]->getTo()));
        
    }
    
    
    /**
     * to(終了時刻)を指定して解析が実行できることを確認する。
     */
    public function testToParameter() {
        //toで指定した時刻以前のログが存在しない場合、結果は空の配列になる。
        $to = strtotime('2012-11-24 02:15:59');
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testTo.txt', 0, $to);
        $parser->parse();
        
        $results = $parser->getResult();
        $this->assertCount(0, $results);

        //toで指定した時刻ちょうどのログが存在する場合、その時間までのログが対象になることを確認する。
        $to = strtotime('2012-11-24 02:16:00');
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testTo.txt', 0, $to);
        $parser->parse();
        $results = $parser->getResult();
        
        $this->assertCount(4, $results);
        $this->assertEquals('2012-11-24 02:16:00', date('Y-m-d H:i:s', $results[0]->getTo()));
        $this->assertEquals('2012-11-24 02:16:00', date('Y-m-d H:i:s', $results[3]->getTo()));
        
        //toで指定した時刻ちょうどでなくても、指定時刻までの行を含めた結果が取得できることを確認する。
        $to = strtotime('2012-11-24 02:16:05');
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testTo.txt', 0, $to);
        $parser->parse();
        
        $results = $parser->getResult();
        
        $this->assertCount(6, $results);
        $this->assertEquals('2012-11-24 02:16:00', date('Y-m-d H:i:s', $results[0]->getTo()));
        $this->assertEquals('2012-11-24 02:16:04', date('Y-m-d H:i:s', $results[5]->getTo()));
    }
    
    /**
     * "time"のログが含まれないログが正しく解析されることを確認する。
     * timeのログが存在しない場合は、直前の行timeの値が引き継がれる。
     */
    public function testLogTime() {
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testLogTime.txt');
        $parser->parse();
        
        $results = $parser->getResult();
        
        $this->assertEquals('2012-11-24 02:15:49', date('Y-m-d H:i:s', $results[0]->getFrom()));
        $this->assertEquals('2012-11-24 02:15:53', date('Y-m-d H:i:s', $results[0]->getTo()));
        $this->assertEquals('2012-11-24 02:15:51', date('Y-m-d H:i:s', $results[1]->getFrom()));
        $this->assertEquals('2012-11-24 02:15:53', date('Y-m-d H:i:s', $results[1]->getTo()));
    }
    
    
    /**
     * getResultで取得した結果に、必要なデータが全て格納されていることを確認する。
     */
    public function testGetResult() {
        $parser = new SlowLog_Parser(dirname(__FILE__) . '/ParserTest_testGetResult.txt');
        $parser->parse();
        
        $results = $parser->getResult();
        
        $this->assertEquals('LogicalEntry', get_class($results[0]));
        $this->assertEquals('2012-11-24 02:15:51', date('Y-m-d H:i:s', $results[0]->getFrom()));
        $this->assertEquals('2012-11-24 02:15:53', date('Y-m-d H:i:s', $results[0]->getTo()));
        $this->assertEquals('1.419554', $results[0]->getElapsed());
        $this->assertEquals('0.000160', $results[0]->getExtra('lock_time'));
        $this->assertEquals('10', $results[0]->getExtra('rows_sent'));
        $this->assertEquals('20', $results[0]->getExtra('rows_examined'));
        $this->assertEquals('root[root]', $results[0]->getExtra('user'));
        $this->assertEquals('localhost []', $results[0]->getExtra('host'));
        $this->assertEquals("SET timestamp=1353690957;\nINSERT INTO t1 VALUES (NULL,uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),uuid(),364531492,'qMa5SuKo4M5OM7ldvisSc6WK9rsG9E8sSixocHdgfa5uiiNTGFxkDJ4EAwWC2e4NL1BpAgWiFRcp1zIH6F1BayPdmwphatwnmzdwgzWnQ6SRxmcvtd6JRYwEKdvuWr');\n", $results[0]->getExtra('query'));
    }
}
