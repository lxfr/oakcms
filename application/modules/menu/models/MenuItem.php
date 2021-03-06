<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-beta.0.1
 */

namespace app\modules\menu\models;


use app\behaviors\NestedSetsBehavior;
use app\interfaces\model\ViewableInterface;
use dosamigos\transliterator\TransliteratorHelper;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * This is the model class for table "menu_item".
 *
 * @property integer    $id
 * @property integer    $menu_type_id
 * @property integer    $parent_id
 * @property integer    $translation_id
 * @property integer    $status
 * @property string     $language
 * @property string     $title
 * @property string     $alias
 * @property string     $path
 * @property string     $note
 * @property string     $link
 * @property integer    $link_weight
 * @property integer    $link_type
 * @property string     $link_params
 * @property string     $layout_path
 * @property string     $access_rule
 * @property string     $metatitle
 * @property string     $metakey
 * @property string     $metadesc
 * @property string     $robots
 * @property integer    $secure
 * @property integer    $created_at
 * @property integer    $updated_at
 * @property integer    $created_by
 * @property integer    $updated_by
 * @property integer    $lft
 * @property integer    $rgt
 * @property integer    $level
 * @property string     $ordering
 * @property string     $hits
 * @property string     $lock
 *
 * @property string     $linkTitle
 * @property array      $linkParams
 * @property integer    $context
 * @property MenuType   $menuType
 * @property MenuItem   $parent
 * @property MenuItem[] $translations
 * @property array      $layoutLabels
 */
class MenuItem extends \yii\db\ActiveRecord implements ViewableInterface
{
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_MAIN_PAGE = 2;

    const LINK_ROUTE = 1;   //MenuItem::link используется в качестве роута, MenuItem::path в качестве ссылки
    const LINK_HREF = 2;    //MenuItem::link используется в качестве ссылки, MenuItem::path не используется

    const CONTEXT_PROPER = 1;
    const CONTEXT_APPLICABLE = 2;
    /**
     * @var array
     */
    private static $_statuses = [
        self::STATUS_PUBLISHED   => 'Published',
        self::STATUS_UNPUBLISHED => 'Unpublished',
        self::STATUS_MAIN_PAGE   => 'Main Page',
    ];

    private static $_linkTypes = [
        self::LINK_ROUTE => 'Component route',
        self::LINK_HREF  => 'Link as is',
    ];
    /**
     * @var integer
     */
    private $_context;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu_item}}';
    }

    /**
     * @return array
     */
    public static function statusLabels()
    {
        return array_map(function ($label) {
            return Yii::t('menu', $label);
        }, self::$_statuses);
    }

    /**
     * Список типов ссылки
     * @return array
     */
    public static function linkTypeLabels()
    {
        return array_map(function ($label) {
            return Yii::t('menu', $label);
        }, self::$_linkTypes);
    }

    /**
     * @param string|array $route
     * @param null         $params
     *
     * @return mixed|null|string
     */
    public static function toRoute($route, $params = null)
    {
        if (is_array($route)) {
            $_route = $route;
            $route = ArrayHelper::remove($_route, 0);
            $params = array_merge($_route, (array)$params);
        }

        return !empty($params) ? $route . '?' . http_build_query($params) : $route;
    }

    /**
     * @inheritdoc
     */
    public static function frontendViewLink($model)
    {
        if ($model['link_type'] == self::LINK_ROUTE) {
            return ['/' . $model['path']];
        } else {
            return $model['link'];
        }
    }

    /**
     * @inheritdoc
     */
    public static function backendViewLink($model)
    {
        return ['/admin/menu/item/view', 'id' => $model['id']];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menu_type_id', 'parent_id', 'status', 'link_type', 'link_weight', 'secure', 'created_at', 'updated_at', 'created_by', 'updated_by', 'lft', 'rgt', 'level', 'ordering', 'hits', 'lock'], 'integer'],
            [['menu_type_id'], 'required'],
            [['menu_type_id'], 'exist', 'targetAttribute' => 'id', 'targetClass' => MenuType::className()],
            [['language'], 'required'],
            [['language'], 'string', 'max' => 7],
            [['language'], function ($attribute) {
                if (($parent = self::findOne($this->parent_id)) && !$parent->isRoot() && $parent->language != $this->language) {
                    $this->addError($attribute, Yii::t('menu', 'Language has to match with the parental.'));
                }
            }],
            [['layout_path'], 'filter', 'filter' => function ($value) {
                // если во вьюхе используется select2, отфильтровываем значение из массива [0 => 'значение'] -> 'значение'
                return is_array($value) ? reset($value) : $value;
            }],
            [['title', 'link', 'layout_path'], 'string', 'max' => 1024],
            [['alias', 'note', 'metakey', 'metatitle'], 'string', 'max' => 255],
            [['metadesc'], 'string', 'max' => 2048],
            [['access_rule', 'robots'], 'string', 'max' => 50],

            [['parent_id'], function ($attribute) {
                if (($parent = self::findOne($this->parent_id)) && !$parent->isRoot() && $parent->menu_type_id != $this->menu_type_id) {
                    $this->addError($attribute, Yii::t('menu', 'Parental point of the menu doesn\'t correspond to the chosen menu type.'));
                }
            }],
            [['status'], function ($attribute) {
                if ($this->status == self::STATUS_MAIN_PAGE && $this->link_type == self::LINK_HREF) {
                    $this->addError($attribute, Yii::t('menu', 'Alias of the menu item can\'t be a main page.'));
                }
            }],
            [['alias'], 'filter', 'filter' => 'trim'],
            [['alias'], 'filter', 'filter' => function ($value) {
                if (empty($value)) {
                    return Inflector::slug(TransliteratorHelper::process($this->title));
                } else {
                    return Inflector::slug($value);
                }
            }],
            [['alias'], 'unique', 'filter' => function ($query) {
                /** @var $query \yii\db\ActiveQuery */
                if ($parent = self::findOne($this->parent_id)) {
                    $query->andWhere('lft>=:lft AND rgt<=:rgt AND level=:level AND language=:language', [
                        'lft'      => $parent->lft,
                        'rgt'      => $parent->rgt,
                        'level'    => $parent->level + 1,
                        'language' => $this->language,
                    ]);
                }
            }],
            [['alias'], 'string', 'max' => 255],
            [['alias'], 'required', 'enableClientValidation' => false],
            [['translation_id'], 'unique', 'filter' => function ($query) {
                /** @var $query \yii\db\ActiveQuery */
                $query->andWhere(['language' => $this->language]);
            }, 'message'                            => Yii::t('menu', 'Localization ({language}) for item (ID: {id}) already exists.', ['language' => $this->language, 'id' => $this->translation_id])],
            [['title', 'link', 'status'], 'required'],
            [['ordering'], 'filter', 'filter' => 'intVal'], //for proper $changedAttributes
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => Yii::t('menu', 'ID'),
            'menu_type_id'   => Yii::t('menu', 'Menu Type ID'),
            'parent_id'      => Yii::t('menu', 'Parent ID'),
            'translation_id' => Yii::t('menu', 'Translation ID'),
            'status'         => Yii::t('menu', 'Status'),
            'language'       => Yii::t('menu', 'Language'),
            'title'          => Yii::t('menu', 'Title'),
            'alias'          => Yii::t('menu', 'Alias'),
            'path'           => Yii::t('menu', 'Path'),
            'note'           => Yii::t('menu', 'Note'),
            'link'           => Yii::t('menu', 'Link'),
            'link_type'      => Yii::t('menu', 'Link Type'),
            'link_weight'    => Yii::t('menu', 'Link Weight'),
            'link_params'    => Yii::t('menu', 'Link Params'),
            'layout_path'    => Yii::t('menu', 'Layout Path'),
            'access_rule'    => Yii::t('menu', 'Access Rule'),
            'metatitle'      => Yii::t('menu', 'Meta Title'),
            'metakey'        => Yii::t('menu', 'Meta keywords'),
            'metadesc'       => Yii::t('menu', 'Meta description'),
            'robots'         => Yii::t('menu', 'Robots'),
            'secure'         => Yii::t('menu', 'Secure'),
            'created_at'     => Yii::t('menu', 'Created At'),
            'updated_at'     => Yii::t('menu', 'Updated At'),
            'created_by'     => Yii::t('menu', 'Created By'),
            'updated_by'     => Yii::t('menu', 'Updated By'),
            'lft'            => Yii::t('menu', 'Lft'),
            'rgt'            => Yii::t('menu', 'Rgt'),
            'level'          => Yii::t('menu', 'Level'),
            'ordering'       => Yii::t('menu', 'Ordering'),
            'hits'           => Yii::t('menu', 'Hits'),
            'lock'           => Yii::t('menu', 'Lock'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
            NestedSetsBehavior::className(),
            'sortable' => [
                'class' => \kotchuprik\sortable\behaviors\Sortable::className(),
                'query' => self::find(),
                'orderAttribute' => 'ordering'
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenuType()
    {
        return $this->hasOne(MenuType::className(), ['id' => 'menu_type_id']);
    }

    /**
     * @return MenuItemQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     * @inheritdoc
     * @return MenuItemQuery
     */
    public static function find()
    {
        return new MenuItemQuery(get_called_class());
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function optimisticLock()
    {
        return 'lock';
    }

    public function saveNode($runValidation = true, $attributes = null)
    {
        if ($this->getIsNewRecord()) {
            // если parent_id не задан, то ищем корневой элемент
            if ($parent = $this->parent_id ? self::findOne($this->parent_id) : self::find()->roots()->one()) {
                $this->parent_id = $parent->id;

                return $this->appendTo($parent, $runValidation, $attributes);
            } else {
                // если рутового элемента не существует, то сохраняем модель как корневую
                return $this->makeRoot($runValidation, $attributes);
            }
        }

        // модель перемещена в другую модель
        if ($this->getOldAttribute('parent_id') != $this->parent_id && $newParent = $this->parent_id ? self::findOne($this->parent_id) : self::find()->roots()->one()) {
            $this->parent_id = $newParent->id;

            return $this->appendTo($newParent, $runValidation, $attributes);
        }

        // просто апдейт
        return $this->save($runValidation, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // устанавливаем translation_id по умолчанию
        if ($insert && $this->translation_id === null) {
            $this->updateAttributes([
                'translation_id' => $this->id,
            ]);
        }

        //Если изменен тип меню или язык, смена языка возможна только для корневых элементов
        if (array_key_exists('menu_type_id', $changedAttributes) || array_key_exists('language', $changedAttributes)) {
            $this->normalizeLanguage();
        }

        if (array_key_exists('status', $changedAttributes)) {
            $this->normalizeStatus();
        }

        $oldPath = $this->getOldAttribute('path');
        // нормализуем контексты виджетов и пути подэлементов для текущего элемента при его перемещении, либо изменении псевдонима
        if (array_key_exists('parent_id', $changedAttributes) || array_key_exists('alias', $changedAttributes)) {
            $this->refresh();
            $this->normalizePath();
        }

        // ранжируем элементы если нужно
        if (array_key_exists('ordering', $changedAttributes)) {
            $this->ordering ? $this->parent->reorderNode('ordering') : $this->parent->reorderNode('lft');
        }
    }

    public function normalizeLanguage()
    {
        $ids = $this->children()->select('id')->column();
        self::updateAll(['menu_type_id' => $this->menu_type_id, 'language' => $this->language], ['id' => $ids]);
    }

    public function normalizeStatus()
    {
        if ($this->status == self::STATUS_MAIN_PAGE) {
            self::updateAll(['status' => self::STATUS_PUBLISHED], 'status=:status AND language=:language AND id!=:id', [':status' => self::STATUS_MAIN_PAGE, ':id' => $this->id, ':language' => $this->language]);
        }
    }

    public function normalizePath($parentPath = null)
    {
        if ($parentPath === null) {
            $path = $this->calculatePath();
        } else {
            $path = $parentPath . '/' . $this->alias;
        }

        $this->updateAttributes(['path' => $path]);

        $children = $this->children(1)->all();
        foreach ($children as $child) {
            /** @var self $child */
            $child->normalizePath($path);
        }
    }

    /**
     * @return string
     */
    private function calculatePath()
    {
        $aliases = $this->parents()->excludeRoots()->select('alias')->column();

        return empty($aliases) ? $this->alias : implode('/', $aliases) . '/' . $this->alias;
    }

    /**
     * @param array $value
     */
    public function setLinkParams($value)
    {
        $this->link_params = Json::encode($value);
    }

    /**
     * Тайтл для ссылок в меню
     * @return string
     */
    public function getLinkTitle()
    {
        $linkParams = $this->getLinkParams();

        return empty($linkParams['title']) ? $this->title : $linkParams['title'];
    }

    /**
     * @return array
     */
    public function getLinkParams()
    {
        return Json::decode($this->link_params);
    }

    /**
     * @param null|integer $status
     *
     * @return string
     */
    public function getStatusLabel($status = null)
    {
        if ($status === null) {
            return Yii::t('menu', self::$_statuses[$this->status]);
        }

        return Yii::t('menu', self::$_statuses[$status]);
    }

    /**
     * Возвращает локализованную метку
     *
     * @param null|integer $type
     *
     * @return string
     */
    public function getLinkTypeLabel($type = null)
    {
        if ($type === null) {
            return Yii::t('menu', self::$_linkTypes[$this->link_type]);
        }

        return Yii::t('menu', self::$_linkTypes[$type]);
    }

    /**
     * @return array    [path, params]
     */
    public function parseUrl()
    {
        $arUrl = parse_url($this->link);
        parse_str(@$arUrl['query'], $params);
        if (!empty($arUrl['fragment']))
            $params['#'] = $arUrl['fragment'];

        return [trim($arUrl['path'], '/'), $params];
    }

    /**
     * @return integer
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * @param integer $value
     */
    public function setContext($value)
    {
        $this->_context = $value;
    }


    // ViewableInterface

    /**
     * @return bool
     */
    public function isProperContext()
    {
        return $this->_context === self::CONTEXT_PROPER;
    }

    /**
     * @return bool
     */
    public function isApplicableContext()
    {
        return $this->_context === self::CONTEXT_APPLICABLE;
    }

    //TranslatableInterface
    /**
     * @inheritdoc
     */
    public function getTranslations()
    {
        return self::hasMany(self::className(), ['translation_id' => 'translation_id'])->indexBy('language');
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @inheritdoc
     */
    public function getBackendViewLink()
    {
        return ['/admin/menu/item/view', 'id' => $this->id];
    }

    public function getBreadcrumbs($includeSelf = false)
    {
        if ($this->isRoot()) {
            return [
                'label' => $this->title,
                'url'   => $this->getFrontendViewLink(),
            ];
        } else {
            $path = $this->parents()->excludeRoots()->all();
            if ($includeSelf) {
                $path[] = $this;
            }
            return array_map(function ($item) {
                /** @var self $item */
                return [
                    'label' => $item->title,
                    'url'   => $item->getFrontendViewLink(),
                ];
            }, $path);
        }
    }

    /**
     * @inheritdoc
     */
    public function getFrontendViewLink()
    {
        if ($this->link_type == self::LINK_ROUTE) {
            if ($this->status == self::STATUS_MAIN_PAGE) {
                return '';
            }

            return ['/' . $this->path];
        } else {
            return $this->link;
        }
    }
}
