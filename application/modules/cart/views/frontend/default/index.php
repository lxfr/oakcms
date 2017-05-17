<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-beta.0.1
 */

use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\filter\models\FilterVariant;

/**
 * @var $this \app\components\CoreView
 * @var $elements \app\modules\cart\events\CartElement[]
 * @var $cart app\modules\cart\Cart
 */
$this->title = Yii::t('cart', 'Cart');
$this->setSeoData($this->title);
$cart = Yii::$app->cart;

$this->params['breadcrumb'][] = "Корзина";

?>

<div class="korzina">
    <div class="title text-center">
        <span>Корзина</span>
    </div>

    <?php if(count($elements) > 0): ?>
        <div class="title_decr">
            Доставляем мебель быстро,вся мебель на складе! Даем дополнительные гарантии на комплектность и качество товара.
        </div>
        <div class="product_title hidden-xs">

            <div class="col-md-7 text-left no_padding_left">
                <span>Описание товара</span>
            </div>
            <div class="col-md-1">
                <span>Цена</span>
            </div>
            <div class="col-md-2 text-center">
                <span>Количество</span>
            </div>
            <div class="col-md-2">
                <span class="sum">Общая сума</span>
            </div>

        </div>
        <?php foreach($elements as $element): ?>
            <?php
            /** @var \app\modules\shop\models\Product $model */
            $model = $element->getModel();
            $filter_variants = \yii\helpers\Json::decode($element->options);
            $modification = $model->getModificationByOptions($filter_variants);
            ?>
        <div class="product_decr">
            <div class="row">
                <div class="col-xs-12 col-md-2 col-sm-12 no_padding_left text-center">
                    <?php echo Html::a(
                        Html::img($model->getImage()->getUrl('200x125')),
                        $model->getFrontendViewLink()
                    ); ?>
                </div>
                <div class="col-md-5 col-xs-12 col-sm-12">
                    <ul class=" list-unstyled product_decr_list">
                        <li><?= $model->name ?></li>
                        <li>Размер: </li>
                        <?php foreach ($filter_variants as $k => $variant): ?>
                            <?php
                            $filter_data = FilterVariant::getVariantValue($variant, $k);
                            echo ArrayHelper::getValue($filter_data, 'name', '');
                            echo ArrayHelper::getValue($filter_data, 'value', '');
                            ?>
                        <li>Цвет: </li>
                        <?php endforeach;?>
                    </ul>
                </div>
                <div class="col-xs-4 col-xs-12 col-md-1 col-sm-2">

                    <div class="prise_total hidden-xs hidden-sm">
                        11 300P<br>
                        <span>11 300P</span>
                    </div>
                </div>
                <div class="col-xs-12 col-md-2 col-sm-12">
                    <div class="total">

                        <button class="minus" style="line-height: 21px;
                                font-size: 80px;
                                padding-bottom: 18px;">
                            -
                        </button>

                        <input id="count" type="text" value="1">

                        <button class="plus">
                            +
                        </button>
                    </div>

                </div>
                <div class="col-xs-12 col-md-1 ">

                    <div class="prise_total ">
                        11 300P<br>
                        <span>11 300P</span>
                    </div>

                </div>
                <div class="col-xs-12  col-md-1  text-center">
                    <?= Html::button('', [
                        'data' => [
                            'line-selector' => '.product_decr',
                            'id' => $element->id,
                            'url' => Url::to(['/cart/element/delete'])
                        ],
                        'class' => 'oakcms-cart-delete-button'
                    ]) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="total_prise text-right ">
            <ul class="list-unstyled">
                <li>Итого: <span><?//=  ?>P</span></li>
                <li>Предварительная дата доставки 20 сентября 2016р</li>
                <li>Общая сумма: 32 500Р</li>
                <li>Сумма скидки: 6 000P</li>
            </ul>
            <button>
                Оформить заказ
            </button>
        </div>
        <div class="details text-left hidden-sm hidden-xs">
            <img src="img/details.png" alt="">
            Подробные условия:
        </div>
        <div class="specification row hidden-sm hidden-xs">
            <div class="col-md-3">
                <span>Доставка</span>
                <ul class="list-unstyled">
                    <li><span class="bold_text">Доставка по Москве</span> - безплатно</li>
                    <li><span class="bold_text">Доставка по МО за МКАД</span> - 100p/км</li>
                    <li><span class="bold_text">Доставка по России</span> - до</li>
                    <li>транспортной компании - безплатно</li>
                </ul>
            </div>
            <div class="col-md-3">
                <span>Ожидаемое время доставки</span>
                <ul class="list-unstyled">
                    <li><span class="bold_text">Москва и область</span>- 1-2 дня</li>
                    <li><span class="bold_text">По России</span>- 1-2 дней</li>
                </ul>
            </div>
            <div class="col-md-3">
                <span>Оплата </span>
                <ul class="list-unstyled">
                    <li><span class="bold_text">Москва и МО</span> наличными при</li>
                    <li>получении, безналичными</li>
                    <li><span class="bold_text">Россия</span>- безналичный расчет</li>
                </ul>
            </div>
            <div class="col-md-3">
                <span>Гарантия 12 месяцев</span>
                <ul class="list-unstyled">
                    <li><span class="bold_text">12 месяцев </span> официальной гарантии</li>
                    <li>от производтеля</li>
                    <li>Обмен/возврат товара в течении</li>
                    <li>14 дней, при невскритой упаковке</li>
                </ul>
            </div>
        </div>
    <?else:?>
        <div class="cart-empty">
            Корзина Пустая.
        </div>
    <?php endif; ?>
</div>
