<?php


interface Plugin_Interface {
    
    
    /**
     * プラグイン名を返す。
     * @return string
     */
    public function getName();
    
    
    /**
     * プラグインの種類を返す。
     * @return string
     */
    public function getType();
    
}