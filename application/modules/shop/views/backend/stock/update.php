<?php
/**
 * Copyright (c) 2015 - 2016. Hryvinskyi Volodymyr
 */

use yii\helpers\Html;

$this->title = 'Обновить склад: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Склады', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Обновить';
\app\modules\shop\assets\BackendAsset::register($this);
?>
<div class="producer-update">

    <?= $this->render('_form', [
        'model' => $model,
        'activeStaffers' => $activeStaffers,
    ]) ?>

    <?php if($fieldPanel = \app\modules\field\widgets\Choice::widget(['model' => $model])) { ?>
        <div class="block">
            <h2>Прочее</h2>
            <?=$fieldPanel;?>
        </div>
    <?php } ?>

</div>
