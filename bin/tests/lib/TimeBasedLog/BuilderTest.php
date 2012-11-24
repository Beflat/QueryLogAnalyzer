<?php


class TimeBasedLog_BuilderTest extends PHPUnit_Framework_TestCase {

    public function testStatGeneration() {
        
        //時間の重なりのある2つのLogicalEntryからデータを生成出来ることを確認する。
        $entry1 = new LogicalEntry(1);
        $entry1->setFrom(1);
        $entry1->setTo(10);
        $entry2 = new LogicalEntry(2);
        $entry2->setFrom(5);
        $entry2->setTo(8);
        
        $statGenerator = new TimeBasedLog_Builder();
        $statGenerator->addEntry($entry1);
        $statGenerator->addEntry($entry2);
        $result = $statGenerator->getResult();
        
        $this->assertFalse(array_key_exists(0, $result));
        $this->assertTrue(array_key_exists(1, $result));
        $this->assertTrue(array_key_exists(10, $result));
        $this->assertFalse(array_key_exists(11, $result));
        $this->assertEquals(1, $result[1]);
        $this->assertEquals(1, $result[4]);
        $this->assertEquals(2, $result[5]);
        $this->assertEquals(2, $result[8]);
        $this->assertEquals(1, $result[9]);
        
        //時間の重なりのない2つのLogicalEntryからデータを生成出来ることを確認する。
        $entry1 = new LogicalEntry(1);
        $entry1->setFrom(1);
        $entry1->setTo(5);
        $entry2 = new LogicalEntry(2);
        $entry2->setFrom(8);
        $entry2->setTo(20);
        
        $statGenerator = new TimeBasedLog_Builder();
        $statGenerator->addEntry($entry1);
        $statGenerator->addEntry($entry2);
        $result = $statGenerator->getResult();
        
        $this->assertFalse(array_key_exists(0, $result));
        $this->assertTrue(array_key_exists(1, $result));
        $this->assertTrue(array_key_exists(20, $result));
        $this->assertFalse(array_key_exists(21, $result));
        $this->assertEquals(1, $result[5]);
        $this->assertEquals(0, $result[7]);
        $this->assertEquals(1, $result[8]);
        $this->assertEquals(1, $result[20]);
    }
    
    /**
     * しきい値を使用した場合、ElapsedTimeがしきい値以下のデータは無視されることを確認する。
     */
    public function testThreshold() {
        $entry1 = new LogicalEntry(1);
        $entry1->setFrom(1);
        $entry1->setTo(1);
        $entry1->setElapsed(0);
        $entry2 = new LogicalEntry(2);
        $entry2->setFrom(5);
        $entry2->setTo(8);
        $entry2->setElapsed(3);
        $entry3 = new LogicalEntry(3);
        $entry3->setFrom(5);
        $entry3->setTo(5);
        $entry3->setElapsed(0);
        
        
        $statGenerator = new TimeBasedLog_Builder();
        $statGenerator->init(1);
        $statGenerator->addEntry($entry1);
        $statGenerator->addEntry($entry2);
        $statGenerator->addEntry($entry3);
        $result = $statGenerator->getResult();
        
        $this->assertFalse(array_key_exists(1, $result));
        $this->assertFalse(array_key_exists(4, $result));
        $this->assertTrue(array_key_exists(5, $result));
        $this->assertTrue(array_key_exists(8, $result));
        $this->assertEquals(1, $result[5]);
        $this->assertEquals(1, $result[6]);
        $this->assertEquals(1, $result[8]);
    }
    
    /**
     * Callbackを使ったデータの生成が出来ることを確認する。
     */
    public function testCallBackFunction() {
        $entry1 = new LogicalEntry(1);
        $entry1->setFrom(1);
        $entry1->setTo(5);
        $entry1->setExtra('Query', 'SELECT AAA FROM bbb');
        
        $entry2 = new LogicalEntry(2);
        $entry2->setFrom(3);
        $entry2->setTo(10);
        $entry2->setExtra('Query', 'UPDATE AAA SET bbb=1');
        
        $statGenerator = new TimeBasedLog_Builder();
        $statGenerator->init();
        $statGenerator->registerCallback(array($this, 'sampleCallback'));
        $statGenerator->addEntry($entry1);
        $statGenerator->addEntry($entry2);
        $result = $statGenerator->getResult();
        
        $this->assertTrue(array_key_exists(1, $result));
        $this->assertTrue(array_key_exists(3, $result));
        $this->assertTrue(array_key_exists(10, $result));
        $this->assertEquals(0, $result[1]);
        $this->assertEquals(1, $result[3]);
        $this->assertEquals(1, $result[6]);
        $this->assertEquals(1, $result[10]);
    }

    
    /**
     * UPDATE文のみを対象に件数をカウントするためのコールバック関数。
     * @param LogicalEntry $entry
     * @param int $currentValue
     */
    public function sampleCallback($entry, $currentValue) {
        $query = $entry->getExtra('Query', '');
        if(!preg_match('/^UPDATE/i', $query)) {
            return $currentValue;
        }
        return $currentValue+1;
    }
    
    /**
     * 分計に直した結果や、min,max,avgで結果を取得できることを確認する。
     */
    public function testScaledResult() {
        
        //1分台で3件のデータ
        //2分台で2件のデータ
        //3分台で1件のデータ
        //を作る
        
        $entry1 = new LogicalEntry(1);
        $entry1->setFrom(1);
        $entry1->setTo(179);
        
        $entry2 = new LogicalEntry(2);
        $entry2->setFrom(10);
        $entry2->setTo(60);
        
        $entry3 = new LogicalEntry(3);
        $entry3->setFrom(30);
        $entry3->setTo(40);
        
        $entry4 = new LogicalEntry(4);
        $entry4->setFrom(61);
        $entry4->setTo(70);
        
        $statGenerator = new TimeBasedLog_Builder();
        $statGenerator->init();
        $statGenerator->addEntry($entry1);
        $statGenerator->addEntry($entry2);
        $statGenerator->addEntry($entry3);
        $statGenerator->addEntry($entry4);
        
        //標準のパラメータで呼び出し。分単位の件数の合計を取得。
        $result = $statGenerator->getScaledResult();
        $this->assertEquals(3, $result[0]);
        $this->assertEquals(2, $result[1]);
        $this->assertEquals(1, $result[2]);
        $this->assertFalse(array_key_exists(3, $result));
    }
}

