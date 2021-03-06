<?php
/**
 * @link https://github.com/gromver/yii2-platform-basic.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-platform-basic/blob/master/LICENSE
 * @package yii2-platform-basic
 * @version 1.0.0
 */

namespace app\modules\menu\widgets;


use app\modules\language\models\Language;
use kartik\icons\Icon;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use app\interfaces\model\TranslatableInterface;

/**
 * Class TranslationsBackend
 * TranslationsBackend используется в CRUD контроллерах для отображения списка локализаций указанной модели.
 * Модель должна поддерживать app\interfaces\model\TranslatableInterface
 * В список попадают все локализации, относящиеся к модели, а также те локализации,
 * которые поддерживает приложение, но не попали в список.
 * Список сортируется по алфавиту.
 * Существующие локализации ведут к path/to/controller/update?id=1
 * Не существующие локализации ведут к path/to/controller/create?sourceId=1&language=en
 *
 */
class TranslationsBackend extends \yii\base\Widget
{
    /**
     * @var ActiveRecord | TranslatableInterface
     */
    public $model;

    public function run()
    {
        $buttons = [];

        $translations = $this->model->translations;
        foreach ($translations as $translationModel) {
            /** @var ActiveRecord | TranslatableInterface $translationModel */
            // if ($translationModel->equals($this->model)) continue;
            $lang = $translationModel->language;
            $buttons[$lang] = Html::a(Icon::show(strtolower(substr($lang, 3, 2)), [], Icon::FI).$lang, ['update', 'id' => $translationModel->getPrimaryKey()], ['class' => 'btn btn-block btn-xs' . ($this->model->language == $lang ? ' btn-primary' : ' btn-default'), 'data-pjax' => '0']);
        }

        $unsupportedLanguages = array_diff(Language::getAllLang(), array_keys($buttons));
        foreach ($unsupportedLanguages as $lang) {
            $buttons[$lang] = Html::a(Icon::show(strtolower(substr($lang, 3, 2)), [], Icon::FI).$lang,
                [
                    'create',
                    'language' => $lang,
                    'sourceId' => $this->model->getPrimaryKey()
                ],
                ['class' => 'btn btn-block btn-danger btn-xs', 'data-pjax' => '0']);
        }

        ksort($buttons);

        return implode(' ', $buttons);
    }
}
