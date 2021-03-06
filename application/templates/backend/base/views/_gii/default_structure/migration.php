<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-beta.0.1
 */

/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/** @var $migrationName string the new migration class name
 * @var                                        $tableAlias string table_name
 * @var                                        $tableName  string table_name
 * @var array                                  $tableColumns
 * @var array                                  $tableIndexes
 * @var array                                  $tablePk
 * @var insolita\migrik\gii\StructureGenerator $generator
 */
$namespace = str_replace(['@', '/'], ['', '\\'], $generator->migrationPath);
echo "<?php\n";
?>
namespace <?= $namespace; ?>;

use yii\db\Schema;
use yii\db\Migration;

class <?= $migrationName ?> extends Migration
{

public function init()
{
$this->db = '<?= $generator->db ?>';
parent::init();
}

public function safeUp()
{
$tableOptions = '<?= $generator->tableOptions ?>';

$this->createTable(
'<?= ($generator->usePrefix) ? $tableAlias : $tableName ?>',
[
<?php foreach ($tableColumns as $name => $data) : ?>
    '<?= $name ?>'=> <?= $data; ?>,
<?php endforeach; ?>
],$tableOptions
);
<?php if (!empty($tableIndexes) && is_array($tableIndexes)) : ?>
    <?php foreach ($tableIndexes as $name => $data) : ?>
        <?php if ($name != 'PRIMARY') : ?>
            $this->createIndex('<?= $name ?>','<?= $tableAlias ?>','<?= implode(",", array_values($data['cols'])) ?>',<?= $data['isuniq'] ? 'true' : 'false' ?>);
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif ?>
}

public function safeDown()
{
<?php if (!empty($tableIndexes) && is_array($tableIndexes)) : ?>
    <?php foreach ($tableIndexes as $name => $data) : ?>
        <?php if ($name != 'PRIMARY') : ?>
            $this->dropIndex('<?= $name ?>', '<?= $tableAlias ?>');
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif ?>
$this->dropTable('<?= ($generator->usePrefix) ? $tableAlias : $tableName ?>');
}
}
