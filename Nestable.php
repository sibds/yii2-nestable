<?php

namespace sibds\widgets;

use \Yii;
use kartik\icons\FontAwesomeAsset;
use yii\bootstrap5\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\Html;

use kartik\icons\Icon;
use yii\helpers\Url;

class Nestable extends \mazurva\nestable2\widgets\Nestable
{
    public $autoQuery = null;
    public $rootable = true;

    public $modelOptions = null;
    
    public $fontAwesome = true;

    public $columns = ['name' => 'name'];
    public $buttons = null;

    public $hideButtons = false;
    
    public $driveController = '';
    
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


        if(count($this->columns)==1&&!$this->hideButtons){
            $this->columns['url'] = function($data){
                return Url::toRoute([$this->driveController.'update', 'id' => $data->primaryKey]);
            };
        }


        if(is_null($this->buttons)){
            $model = new $this->query->modelClass;
            $this->buttons = [
                ['label' => Icon::show('pencil-alt', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'update', 'id'=>$data->primaryKey]);},
                    'options'=>['title'=>self::t('messages', 'Edit'), 'data-pjax' => 0]],
                /*
                ['label' => Icon::show('copy', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'duplicate', 'id'=>$data->primaryKey]);},
                    'options'=>['title'=>self::t('messages', 'Copy')],
                    'visible' => $model->hasMethod('duplicate')],
                */
                ['label' => Icon::show('lock', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'lock', 'id'=>$data->primaryKey]);},
                    'options'=>[
                        'title'=>self::t('messages', 'Lock'),
                        'data-method' => 'POST',
                        'data-pjax' => '0',
                    ],
                    'visible' => function($data){ return $data->hasAttribute('locked')&&!$data->locked;}],
                ['label' => Icon::show('unlock', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'unlock', 'id'=>$data->primaryKey]);},
                    'options'=>[
                        'title'=>self::t('messages', 'Unlock'),
                        'data-method' => 'POST',
                        'data-pjax' => '0',
                    ],
                    'visible' => function($data){ return $data->hasAttribute('locked')&&$data->locked;}],
                ['label' => Icon::show('trash', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'delete', 'id'=>$data->primaryKey]);},
                    'options'=>[
                        'title'=>self::t('messages', 'To trash'),
                        'data-method' => 'POST',
                        'data-pjax' => '0',
                        'data-confirm'=>self::t('messages', 'Do you really want to put this item in the trash?'),
                    ],
                    'visible' => function($data){ return $data->hasAttribute('removed')&&!$data->removed;}],
                ['label' => Icon::show('share-square', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'restore', 'id'=>$data->primaryKey]);},
                    'options'=>['title'=>self::t('messages', 'Restore')],
                    'visible' => function($data){ return $data->hasAttribute('removed')&&$data->removed;}],
                ['label' => Icon::show('times', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'delete', 'id'=>$data->primaryKey]);},
                    'options'=>[
                        'title'=>self::t('messages', 'Delete'),
                        'data-method' => 'POST',
                        'data-pjax' => '0',
                        'data-confirm'=>self::t('messages', 'Do you really want to delete this item?'),
                    ],
                    'visible' => function($data){return $data->hasAttribute('removed')&&$data->removed; }],
            ];
        }

        $this->options['class'] = 'nestable'.(isset($this->options['class'])?' '.$this->options['class']:'');

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
        if($this->fontAwesome) FontAwesomeAsset::register($view);
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
            $name = ArrayHelper::getValue($this->columns, 'url');
            if(is_callable($name)){
                $row = Html::a($content, call_user_func($name, $data), ['data-pjax'=>0]);
            }else{
                $row = Html::a($content,
                    $data->hasAttribute($name)?
                        $data->{$name}:
                        $name);
            }
        }

        if(!is_null($this->buttons)&&!$this->hideButtons){
            $template = 'div class="float-end" style="margin-top: -7px;">{buttons}</div>';
            $myButtons = $this->buttons;
            foreach($myButtons as $key => &$button){
                if(is_string($button))
                    continue;

                if(array_key_exists('visible', $button)){
                    $name = ArrayHelper::getValue($button, 'visible');
                    if(is_callable($name)){
                        $button['visible'] = call_user_func($name, $data);
                    }
                    if(!$button['visible']&&!is_null($key)){
                        unset($myButtons[$key]);
                        continue;
                    }
                }
                $label = $button['label'];
                $url = ArrayHelper::getValue($button, 'url', '#');
                unset($button['label']);
                if(isset($button['url']))
                    if(is_callable($url))
                        $url = call_user_func($url, $data);

                $options = $button['options'];
                $options['class'] = 'btn btn-default'.(isset($options['class'])?' '.$options['class']:'');

                $button = Html::a($label, $url, $options);
            }
            $row .= strtr($template, ['{buttons}' =>
                ButtonGroup::widget([
                    'encodeLabels'  => false,
                    'options' => ['class' => 'btn-group-sm'],
                    'buttons' => $myButtons])]);
        }

        return $row;
    }
}
