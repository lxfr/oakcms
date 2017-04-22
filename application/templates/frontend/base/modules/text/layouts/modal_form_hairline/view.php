<?php
/**
 * Created by Vladimir Hryvinskyy.
 * Site: http://codice.in.ua/
 * Date: 16.09.2016
 * Project: osnovasite
 * File name: view.php
 *
 * @var $model \app\modules\text\models\Text;
 */

use yii\helpers\Url;


$isHome = (Yii::$app->request->baseUrl.'/index' == Url::to([''])) ? true : false;
?>


<div class="block_button_consalt block_button_consalt_border">
    <a href="#<?= $model->getSetting('form') ?>" class="button open_modal">
        <?= $model->getSetting('buttonName') ?>
    </a>
</div>
