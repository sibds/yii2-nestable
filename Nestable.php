<?php

namespace sibds\widgets;

class Nestable extends \slatiusa\nestable\Nestable
{
    public $autoQuery = null;
    public $rootable = true;

    public $modelOptions = ['name' => 'name'];

    public function init(){
        if(!is_null($this->autoQuery)){
            $auto = $this->autoQuery->roots();
            if($this->rootable)
                $this->query = $auto;
            else
                $this->query = $auto->one()?$auto->one()->children(1):null;
        }


        parent::init();
    }
}
