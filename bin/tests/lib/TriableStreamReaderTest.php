<?php


class TriableStreamReaderTest extends PHPUnit_Framework_TestCase {
    
    /**
     * ・3行のテキストファイルが読み込めること
     * ・3行目を読んだ後で読み込み終了となること
     * ・各行の末尾の改行は除去されること
     * @test 
     */
    public function read3Lines() {
        $reader = new TriableStreamReader(fopen(dirname(__FILE__) . '/read3Lines.txt', 'r'));
        $read = $reader->getLine();
        $this->assertEquals('a', $read);
        $this->assertFalse($reader->isEof());
        $read = $reader->getLine();
        $this->assertEquals('b', $read);
        $this->assertFalse($reader->isEof());
        $read = $reader->getLine();
        $this->assertEquals('c', $read);
        $this->assertTrue($reader->isEof());
    }
    
    /**
     * ・3行のテキストファイルが読み込めること
     * ・3行目を読んだ後で読み込み終了となること
     * ・各行の末尾の改行は除去されること
     * ・tryLine()を使用すると次の行を取得しながらも、
     *  カーソルは進んでいないような振る舞いをすることを確認する。
     * @test 
     */
    public function read3LinesWithTry() {
        $reader = new TriableStreamReader(fopen(dirname(__FILE__) . '/read3Lines.txt', 'r'));
        $read = $reader->getLine();
        $this->assertEquals('a', $read);
        $this->assertFalse($reader->isEof());
        $read = $reader->getLine();
        $this->assertEquals('b', $read);
        $this->assertFalse($reader->isEof());
        $read = $reader->tryLine();
        $this->assertEquals('c', $read);
        $this->assertFalse($reader->isEof());
        $read = $reader->getLine();
        $this->assertEquals('c', $read);
        $this->assertTrue($reader->isEof());
    }
}
