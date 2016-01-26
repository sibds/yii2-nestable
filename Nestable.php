<?php

namespace sibds\widgets;

use kartik\icons\FontAwesomeAsset;
use yii\bootstrap\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

use kartik\icons\Icon;

class Nestable extends \slatiusa\nestable\Nestable
{
    public $autoQuery = null;
    public $rootable = true;

    public $modelOptions = null;

    public $columns = ['name' => 'name'];
    public $buttons = null;

    public $hideButtons = false;

    public function init(){
        $this->registerTranslations();

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

        if(is_null($this->buttons)){
            $this->buttons = [
                ['label' => Icon::show('pencil', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Edit')]],
                ['label' => Icon::show('lock', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Lock')]],
                ['label' => Icon::show('unlock', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Unlock')]],
                ['label' => Icon::show('trash', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'To trash')]],
                ['label' => Icon::show('share-square-o', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Restore')]],
                ['label' => Icon::show('remove', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Delete')]],
            ];
        }

        parent::init();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['widgets/nestable/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendors/sibds/widgets/messages',
            'fileMap' => [
                'widgets/nestable/messages' => 'messages.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('widgets/nestable/' . $category, $message, $params, $language);
    }

    /**
     * Register client assets
     */
    public function registerAssets() {
        $view = $this->getView();
        FontAwesomeAsset::register($view);
        NestableAsset::register($view);
        parent::registerAssets();
    }

    private function prepareRow($data){

        $row = '';

        $name = ArrayHelper::getValue($this->columns, 'name', 'name');
        $content = (is_callable($name) ? call_user_func($name, $data) : $data->{$name});

        if(count($this->columns)<2){
            $row = $content;
        }else{
            $name = ArrayHelper::getValue($this->columns, 'url', 'url');
            if(is_string($name)){
                $row = Html::a($content,
                    $data->hasAttribute($name)?
                        $data->{$name}:
                        $name);
            }else if(is_callable($name)) {
                $row = Html::a($content, call_user_func($name, $data));
            }

        }


        if(!is_null($this->buttons)&&!$this->hideButtons){
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
