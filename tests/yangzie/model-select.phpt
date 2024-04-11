--TEST--
YZE_SQL 测试（各种查询）
--FILE--
<?php
namespace  yangzie;
chdir(dirname(dirname(dirname(__FILE__)))."/app/public_html");
include "init.php";

class TestModel extends YZE_Model{
	const TABLE= "tests";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\TestModel';

	const F_TITLE = "title";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'title'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
}
class TestItemModel extends YZE_Model{
	const TABLE= "test_item";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\TestItemModel';

	const F_TITLE = "title";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'title'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'test_id'      => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
    protected $unique_key = array (
      'id' => 'PRIMARY',
      'test_id' => 'fk_test1_idx'
    );
}

$query = TestModel::from();
$query->select();
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->select();
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->where("id = :id")->select([':id'=>1]);
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->count('id',[],'',true);
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->count('id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->count('*');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->max('id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->min('id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from();
$query->sum('id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->left_join(TestItemModel::class,'ti','t.id=ti.test_id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->right_join(TestItemModel::class,'ti','t.id=ti.test_id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->join(TestItemModel::class,'ti','t.id=ti.test_id');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->join(TestItemModel::class,'ti','t.id=ti.test_id','_2024');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->limit(1,10);
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->group_By('title','t');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->order_By('id','desc','t');
$sql = $query->get_sql();
echo $sql,"\r\n";

$query = TestModel::from('t');
$query->where("id > 0 or title is not null")->order_By('id','asc','t')->group_By('id','t')->limit(0,10);
$sql = $query->get_sql();
echo $sql,"\r\n";

$query->clean()->find_all();
$sql = $query->get_sql();
echo $sql,"\r\n";


?>
--EXPECT--
SELECT m.id AS m_id,m.title AS m_title,m.created_on AS m_created_on,m.modified_on AS m_modified_on FROM `tests` AS m
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t
SELECT m.id AS m_id,m.title AS m_title,m.created_on AS m_created_on,m.modified_on AS m_modified_on FROM `tests` AS m WHERE id = :id
SELECT count(distinct m.id) AS m_COUNT_ALIAS FROM `tests` AS m LIMIT 1
SELECT count( m.id) AS m_COUNT_ALIAS FROM `tests` AS m LIMIT 1
SELECT count( *) AS m_COUNT_ALIAS FROM `tests` AS m LIMIT 1
SELECT max(m.id) AS m_MAX_ALIAS FROM `tests` AS m LIMIT 1
SELECT min(m.id) AS m_MIN_ALIAS FROM `tests` AS m LIMIT 1
SELECT sum(m.id) AS m_SUM_ALIAS FROM `tests` AS m LIMIT 1
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on,ti.id AS ti_id,ti.title AS ti_title,ti.test_id AS ti_test_id,ti.created_on AS ti_created_on,ti.modified_on AS ti_modified_on FROM `tests` AS t LEFT JOIN `test_item` AS ti ON t.id=ti.test_id
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on,ti.id AS ti_id,ti.title AS ti_title,ti.test_id AS ti_test_id,ti.created_on AS ti_created_on,ti.modified_on AS ti_modified_on FROM `tests` AS t RIGHT JOIN `test_item` AS ti ON t.id=ti.test_id
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on,ti.id AS ti_id,ti.title AS ti_title,ti.test_id AS ti_test_id,ti.created_on AS ti_created_on,ti.modified_on AS ti_modified_on FROM `tests` AS t INNER JOIN `test_item` AS ti ON t.id=ti.test_id
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on,ti.id AS ti_id,ti.title AS ti_title,ti.test_id AS ti_test_id,ti.created_on AS ti_created_on,ti.modified_on AS ti_modified_on FROM `tests` AS t INNER JOIN `test_item_2024` AS ti ON t.id=ti.test_id
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t LIMIT 1 , 10
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t GROUP BY t.title
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t ORDER BY t.id DESC
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t WHERE id > 0 or title is not null GROUP BY t.id ORDER BY t.id ASC LIMIT 0 , 10
SELECT * FROM
