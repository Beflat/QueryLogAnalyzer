<?php


class AllLog_ParserTest extends PHPUnit_Framework_TestCase {
    
    
    /**
     * 2行のログをパースして結果を取得できることを確認
     */
    public function testParse2Lines() {
        
        $parser = new AllLog_Parser(dirname(__FILE__) . '/testParse2Lines.txt');
        $parser->parse();
        
        $result = $parser->getResult();
        
        $this->assertEquals(132, $result[132]->getId());
        $this->assertEquals('2012-11-04 14:30:01', date('Y-m-d H:i:s', $result[132]->getFrom()));
        $this->assertEquals('2012-11-04 14:30:01', date('Y-m-d H:i:s', $result[132]->getTo()));
        $this->assertCount(3, $result[132]->getExtra('Query', array()));
        
        $this->assertEquals(133, $result[133]->getId());
        $this->assertEquals('2012-11-04 14:40:01', date('Y-m-d H:i:s', $result[133]->getFrom()));
        $this->assertEquals('2012-11-04 14:40:01', date('Y-m-d H:i:s', $result[133]->getTo()));
        $this->assertCount(0, $result[133]->getExtra('Query', array()));
    }
    
    
    /**
     * fromオプションが機能しているかを確認
     */
    public function testIsFromOptionWork() {
        $from = strtotime('2012-11-04 14:40:01');
        $parser = new AllLog_Parser(dirname(__FILE__) . '/testIsFromToOptionWork.txt', $from);
        $parser->parse();
        
        $result = $parser->getResult();
        $this->assertTrue(!isset($result[131]));
        $this->assertTrue(!isset($result[132]));
        $this->assertTrue(isset($result[133]));
        $this->assertTrue(isset($result[134]));
    }
    
    /**
     * toオプションが機能しているかを確認
     */
    public function testIsToOptionWork() {
        $to = strtotime('2012-11-04 14:50:06');
        $parser = new AllLog_Parser(dirname(__FILE__) . '/testIsFromToOptionWork.txt', 0, $to);
        $parser->parse();
        
        $result = $parser->getResult();
        $this->assertTrue(isset($result[134]));
        $this->assertTrue(!isset($result[135]));
        $this->assertTrue(!isset($result[136]));
        $this->assertTrue(!isset($result[137]));
        
    }
    
    
    /**
     * 入れ子になったセッションのログを解析できることを確認
     */
    public function testParseCrossedSession() {
        $parser = new AllLog_Parser(dirname(__FILE__) . '/testParseCrossedSession.txt');
        $parser->parse();
        
        $result = $parser->getResult();
        
        $this->assertEquals(137, $result[137]->getId());
        $this->assertEquals('2012-11-04 15:15:02', date('Y-m-d H:i:s', $result[137]->getFrom()));
        $this->assertEquals('2012-11-04 15:15:10', date('Y-m-d H:i:s', $result[137]->getTo()));
        $this->assertCount(2, $result[137]->getExtra('Query'));
        
        $this->assertEquals(138, $result[138]->getId());
        $this->assertEquals('2012-11-04 15:15:02', date('Y-m-d H:i:s', $result[138]->getFrom()));
        $this->assertEquals('2012-11-04 15:15:10', date('Y-m-d H:i:s', $result[138]->getTo()));
        $this->assertEquals("SELECT ip_addr FROM ip_addr ORDER BY created DESC LIMIT 1", $result[138]->getExtra('Query', array()));
        
    }
    
    
    /**
     * 閉じていない(Quitのない)ログが最後で閉じられることを確認
     */
    public function testEntryWillCloseWhenParsingEnded() {
        $parser = new AllLog_Parser(dirname(__FILE__) . '/testEntryWillCloseWhenParsingEnded.txt');
        $parser->parse();
        
        $result = $parser->getResult();
        
        $this->assertEquals('2012-11-04 15:15:10', date('Y-m-d H:i:s', $result[137]->getTo()));
        $this->assertEquals('2012-11-04 15:15:10', date('Y-m-d H:i:s', $result[138]->getTo()));
    }
    
    
}


