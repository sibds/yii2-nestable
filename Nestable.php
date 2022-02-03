<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 27.01.16
 * Time: 0:24
 */

namespace sibds\widgets;


class NestableAsset extends \kartik\base\AssetBundle {
    public function init() {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->_setupAssets('css', ['css/nestable']);
        parent::init();
    }

    /**
     * Set up CSS and JS asset arrays based on the base-file names
     *
     * @param string $type whether 'css' or 'js'
     * @param array $files the list of 'css' or 'js' basefile names
     */
    protected function _setupAssets($type, $files = [])
    {
        if ($this->$type === self::KRAJEE_ASSET) {
            $srcFiles = [];
            $minFiles = [];
            foreach ($files as $file) {
                $srcFiles[] = "{$file}.{$type}";
            }
            $this->$type = $srcFiles;
        } elseif ($this->$type === self::EMPTY_ASSET) {
            $this->$type = [];
        }
    }
}
