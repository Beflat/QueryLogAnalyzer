<?php



class LogicalEntry_ReaderTest extends PHPUnit_Framework_TestCase {
    
    public function testDecode() {
        
        $reader = new LogicalEntry_Reader(dirname(__FILE__) . '/decode.json');
        
        $entry = $reader->getEntry();
        
        $this->assertEquals('325411857', $entry->getId());
        $this->assertEquals('1351484100', $entry->getFrom());
        $this->assertEquals('1351484701', $entry->getTo());
        
        $extra = $entry->getAllExtra();
        
        $this->assertEquals('SHOW SLAVE STATUS', $extra['Query'][0]);
    }
    
    public function testEof() {
        
        $reader = new LogicalEntry_Reader(dirname(__FILE__) . '/decode.json');
        
        $entry = $reader->getEntry();
        $this->assertFalse($reader->isEof());
        $entry = $reader->getEntry();
        $this->assertFalse($reader->isEof());
        $entry = $reader->getEntry();
        $this->assertTrue($reader->isEof());
    }
    
}

