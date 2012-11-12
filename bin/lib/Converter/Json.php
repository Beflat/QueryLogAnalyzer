<?php

class Converter_Json implements Converter_Interface {
    
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
        
        fputs($fp, "{\n");
        
        foreach($entries as $entry) {
            $data = array(
                'id'    => $entry->getId(),
                'from'  => $entry->getFrom(),
                'to'    => $entry->getTo(),
                'extra' => $entry->getAllExtra(),
            );
            fputs($fp, json_encode($data) . ",\n");
        }
        fputs($fp, "}\n");
    }
    
    
}

