<?php

class Converter_Csv implements Converter_Interface {
    
    /**
     * 
     * @var string
     */
    protected $filePath;
    
    
    public function __construct($filePath=null) {
        $this->filePath = $filePath;
        if($this->filePath == null) {
            $this->filePath = 'php://output';
        }
    }
    
    public function convert($entries) {
        
        $fp = fopen($this->filePath, 'w');
        if(!$fp) {
            throw new RuntimeException('ファイルのオープンに失敗しました。: ' . $filePath);
        }
        
        $header = 'ID,FROM,TO,';
        
        $titles = array();
        foreach($entries as $entry) {
            //見出し行を作成するため、全データのExtraをスキャンする。
            $extraTitles = array_keys($entry->getAllExtra());
            foreach($extraTitles as $title) {
                $titles[$title] = $title;
            }
        }
        $header .= implode(',', $titles);
        
        //UTF-8で出力するためBOMを出力する。
        fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputs($fp, $header . "\r\n");
        
        foreach($entries as $entry) {
            
            $data = array(
                $entry->getId(),
                $entry->getFrom(),
                $entry->getTo(),
            );
            
            $extra = $entry->getAllExtra();
            foreach($titles as $title) {
                $value = '';
                if(isset($extra[$title])) {
                    $value = $extra[$title];
                }
                
                if(is_array($value)) {
                    $value = implode("\n", $value);
                }
                $data[] = $value;
            }
            
            fputs($fp, implode(',', $this->csvEscape($data)) . "\r\n");
        }
    }
    
    
    protected function csvEscape($values) {
        $escaped = array();
        foreach($values as $idx=>$value) {
            //対象データが改行を含んでいる場合だけクォートした方が良い。
            //対象データの中に"が含まれている分には、クォートは必要ない。
            $values[$idx] = '"' . $values[$idx] . '"';
        }
        return $values; 
    }
}

