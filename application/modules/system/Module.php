<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-beta.0.1
 */

namespace app\modules\system;

/**
 * system module definition class
 */
class Module extends \app\components\module\Module
{

    const VERSION = 'v0.0.1-beta.0.1';

    public $activeModules;

    public $settings = [];

    public static $urlRulesFrontend = [];
    public static $installConfig = [];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\system\controllers\frontend';

    /**
     * Функція яка повертає варсію системи:
     *
     * @return string
     **/
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Повертає копірайт:
     *
     * @return string
     **/
    public static function powered()
    {
        return \Yii::t('system', 'Powered by {OakCMS}', [
            'OakCMS' => '<a href="http://www.oakcms.com/" rel="external">' . \Yii::t('yii', 'OakCMS') . '</a>'
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

    }
}
