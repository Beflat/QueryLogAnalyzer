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
    
    /**
     * このプラグインがどの拡張ポイントでどの処理を実行するかの情報を返す。
     * @return array 拡張ポイントとコールバック関数の対応付けを定義した連想配列
     */
    public function getExtensionPoints();
    
}