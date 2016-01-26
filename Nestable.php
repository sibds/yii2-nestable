<?php

namespace sibds\widgets;

use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;

class Nestable extends \slatiusa\nestable\Nestable
{
    public $autoQuery = null;
    public $rootable = true;

    public $modelOptions = null;

    public $columns = ['name' => 'name'];
    public $buttons = null;

    public function init(){
        if(!is_null($this->autoQuery)){
            $auto = $this->autoQuery->roots();
            if($this->rootable)
                $this->query = $auto;
            else
                $this->query = $auto->one()?$auto->one()->children(1):null;
        }

        if(is_null($this->modelOptions)){
            $this->modelOptions = ['name' => function($data){return $this->prepareRow($data);}];
        }

        parent::init();
    }

    /**
     * Register client assets
     */
    public function registerAssets() {
        $view = $this->getView();
        NestableAsset::register($view);
        parent::registerAssets();
    }

    private function prepareRow($data){

        $row = count($this->columns)<2?
            $data->{$this->columns['name']}:
            Html::a($data->{$this->columns['name']},
                $data->hasAttribute($this->columns['url'])?
                    $data->{$this->columns['name']}:
                    $this->columns['name']);

        if(!is_null($this->buttons)){
            $template = '<div class="pull-right" style="margin-top: -2px;">{buttons}</div>';
            $row .= strtr($template, ['{buttons}' =>
                ButtonGroup::widget([
                    'encodeLabels'  => false,
                    'options' => ['class' => 'btn-group-xs'],
                    'buttons' => $this->buttons])]);
        }

        return $row;
    }
}
