<?php
/**
 * Copyright (c) 2015 - 2016. Hryvinskyi Volodymyr
 */


namespace app\modules\shop\models;

use Yii;
use app\modules\shop\models\category\CategoryQuery;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_category}}".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $sort
 * @property string $text
 * @property string $code
 * @property string $name
 * @property string $slug
 */

class Category extends \yii\db\ActiveRecord
{
    function behaviors()
    {
        return [
            'images' => [
                'class' => 'app\modules\gallery\behaviors\AttachImages',
                'mode' => 'single',
            ],
            'slug' => [
                'class' => 'Zelenin\yii\behaviors\Slug',
            ],
            'field' => [
                'class' => 'app\modules\field\behaviors\AttachFields',
            ],
        ];
    }

    public static function tableName()
    {
        return '{{%shop_category}}';
    }

    static function find()
    {
        return new CategoryQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['parent_id', 'sort'], 'integer'],
            [['name'], 'required'],
            [['text', 'code'], 'string'],
            [['name', 'code', 'slug'], 'string', 'max' => 55],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Родительская категория',
            'name' => 'Имя категории',
            'slug' => 'Сео имя',
            'text' => 'Описание',
            'image' => 'Картинка',
            'sort' => 'Сортировка',
            'description' => 'Описание',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFrontendViewLink()
    {
        return ['/shop/category/view', 'id' => $this->id, 'alias' => $this->slug];
    }


    public static function buldTree($parent_id = null)
    {
        $return = [];

        if(empty($parent_id)) {
            $categories = Category::find()->where('parent_id = 0 OR parent_id is null')->orderBy('sort DESC')->asArray()->all();
        } else {
            $categories = Category::find()->where(['parent_id' => $parent_id])->orderBy('sort DESC')->asArray()->all();
        }

        foreach($categories as $level1) {
            $return[$level1['id']] = $level1;
            $return[$level1['id']]['childs'] = self::buldTree($level1['id']);
        }

        return $return;
    }

    public static function buildTextTree($id = null, $level = 1, $ban = [])
    {
        $return = [];

        $prefix = str_repeat('--', $level);
        $level++;

        if(empty($id)) {
            $categories = Category::find()->where('parent_id = 0 OR parent_id is null')->orderBy('sort DESC')->asArray()->all();
        } else {
            $categories = Category::find()->where(['parent_id' => $id])->orderBy('sort DESC')->asArray()->all();
        }

        foreach($categories as $category) {
            if(!in_array($category['id'], $ban)) {
                $return[$category['id']] = "$prefix {$category['name']}";
                $return = $return + self::buildTextTree($category['id'], $level, $ban);
            }
        }

        return $return;
    }

    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'product_id'])
             ->viaTable('{{%shop_product_to_category}}', ['category_id' => 'id'])->available();
    }

    public function getChilds()
    {
        return $this->hasMany(Category::className(), ['parent_id' => 'id']);
    }

    public function getParent()
    {
        return $this->hasOne(Category::className(), ['id' => 'parent_id']);
    }

    public function getLink()
    {
        return Url::toRoute([yii::$app->getModule('shop')->categoryUrlPrefix, 'slug' => $this->slug]);
    }
}
