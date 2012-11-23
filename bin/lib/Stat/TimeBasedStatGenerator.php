<?php




class Stat_TimeBasedStatGenerator {
    
    private $result;
    
    private $callback;
    
    private $threshold;
    
    private $registeredCount;
    
    
    const SCALE_MODE_SUM = 'sum';
    const SCALE_MODE_AVG = 'avg';
    const SCALE_MODE_MIN = 'min';
    const SCALE_MODE_MAX = 'max';
    
    public function __construct() {
        $this->init();
    }
    
    
    public function init($threshold=0) {
        $this->threshold = $threshold;
        $this->registeredCount = 0;
        $this->result = array();
        $this->callback = null;
    }
    
    public function addEntry(LogicalEntry $entry) {
        
        for($i = $entry->getFrom(); $i<=$entry->getTo(); $i++) {
            
            if($entry->getElapsed() < $this->threshold) {
                continue;
            }
            
            if(!isset($this->result[$i])) {
                $this->result[$i] = 0;
            }
            
            if($this->callback == null) {
                //カウント方法が指定されていなければ標準の動作。
                //ただ単に件数を+1する。
                $this->result[$i]++;
            } else {
                //カウント方法が指定されている場合は、その動作に合わせる。
                $this->result[$i] = call_user_func_array($this->callback, array($entry, $this->result[$i]));
            }
        }
        $this->registeredCount++;
        
    }
    
    
    public function registerCallback($callable) {
        $this->callback = $callable;
    }
    
    
    public function getRegisteredCount() {
        return $this->registeredCount;
    }
    
    public function getResult() {
        $this->fillResultArray();
        return $this->result;
    }
    
    
    /**
     * 結果データを秒単位ではなく5秒単位や分単位に変換した結果を返す。
     * @param unknown_type $scale
     * @param unknown_type $mode
     */
    public function getScaledResult($scale=60, $mode=self::SCALE_MODE_MAX) {
        if($scale == 1) {
            return $this->getResult();
        }
        
        $converted = array();
        $appliedCount = array();
        foreach($this->getResult() as $sec=>$value) {
            $key = (int)($sec / $scale);
            if(!isset($converted[$key])) {
                $converted[$key] = 0;
            }
            if(!isset($appliedCount[$key])) {
                $appliedCount[$key] = 0;
            }
            
            switch($mode) {
                case self::SCALE_MODE_SUM:
                case self::SCALE_MODE_AVG:
                    $converted[$key] += $value;
                    break;
                case self::SCALE_MODE_MIN:
                    $converted[$key] = min($converted[$key], $value);
                    break;
                case self::SCALE_MODE_MAX:
                    $converted[$key] = max($converted[$key], $value);
                    break;
            }
        }
        if($mode == self::SCALE_MODE_AVG) {
            foreach($this->result as $sec=>$value) {
                $converted[$key] /= $appliedCount[$key];
            }
        }
        return $converted;
    }
    
    
    protected function fillResultArray() {
        ksort($this->result);
        reset($this->result);
        $start = key($this->result);
        end($this->result);
        $end = key($this->result);
        reset($this->result);
        
        for($i=$start; $i<=$end; $i++) {
            if(!isset($this->result[$i])) {
                $this->result[$i] = 0;
            }
        }
        ksort($this->result);
    }
}