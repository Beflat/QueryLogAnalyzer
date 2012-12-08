<?php

class LogicalEntry_Converter_Json implements LogicalEntry_Converter_Interface {
    
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
        
        fputs($fp, "[\n");
        
        
        end($entries);
        $lastEntryIndex = key($entries);
        reset($entries);
        foreach($entries as $idx=>$entry) {
            $first = false;
            $data = array(
                'id'      => $entry->getId(),
                'from'    => $entry->getFrom(),
                'to'      => $entry->getTo(),
                'elapsed' => $entry->getElapsed(),
                'extra'   => $entry->getAllExtra(),
            );
            $tail = ($idx == $lastEntryIndex) ? "\n" : ",\n";
            fputs($fp, json_encode($data) . $tail);
        }
        
        fputs($fp, "]\n");
    }
    
    
}

