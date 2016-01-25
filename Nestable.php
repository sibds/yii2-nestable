<?php

namespace sibds\widgets;

class Nestable extends \slatiusa\nestable\Nestable
{
    public $autoQuery = null;
    public $rootable = true;

    public $modelOptions = ['name' => 'name'];

    public function init(){
        if(!is_null($this->autoQuery)){
            if($this->rootable)
                $this->query = $this->autoQuery->roots();
            else
                $this->query = $this->autoQuery->roots()->one()?$this->autoQuery->roots()->one()->children(1):null;
        }


        parent::init();
    }
}
