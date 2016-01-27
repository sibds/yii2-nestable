<?php

namespace sibds\widgets;

use \Yii;
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
            $model = new $this->query->modelClass;
            $this->buttons = [
                ['label' => Icon::show('pencil', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Edit')]],
                ['label' => Icon::show('copy', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Copy')],
                    'visible' => $model->hasMethod('duplicate')],
                ['label' => Icon::show('lock', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Lock')],
                    'visible' => function($data){ return $data->hasAttribute('locked')&&!$data->locked;}],
                ['label' => Icon::show('unlock', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Unlock')],
                    'visible' => function($data){ return $data->hasAttribute('locked')&&$data->locked;}],
                ['label' => Icon::show('trash', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'To trash')],
                    'visible' => function($data){ return $data->hasAttribute('removed')&&!$data->removed;}],
                ['label' => Icon::show('share-square-o', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Restore')],
                    'visible' => function($data){ return $data->hasAttribute('removed')&&$data->removed;}],
                ['label' => Icon::show('remove', [], Icon::FA), 'options'=>['title'=>self::t('messages', 'Delete')],
                    'visible' => function($data){
                        if($data->hasAttribute('removed')){
                            if(is_bool($data->removed))
                                return $data->removed;

                            return !is_null($data->removed);
                        }
                        return true;
                    }],
            ];
        }

        parent::init();
    }

    public function registerTranslations()
    {
        $i18n = \Yii::$app->i18n;
        $i18n->translations['sibds/widgets/nestable/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/sibds/yii2-nestable/messages',
            'fileMap' => [
                'sibds/widgets/nestable/messages' => 'messages.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('sibds/widgets/nestable/' . $category, $message, $params, $language);
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
            foreach($this->buttons as &$button){
                if(array_key_exists('visible', $button)){
                    $name = ArrayHelper::getValue($button, 'visible');
                    if(is_callable($name)){
                        $button['visible'] = call_user_func($name, $data);
                    }
                }
            }
            $row .= strtr($template, ['{buttons}' =>
                ButtonGroup::widget([
                    'encodeLabels'  => false,
                    'options' => ['class' => 'btn-group-xs'],
                    'buttons' => $this->buttons])]);
        }

        return $row;
    }
}
