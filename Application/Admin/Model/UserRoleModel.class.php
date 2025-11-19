<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/3/27
 * Time: 16:19
 */
namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class UserRoleModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'user_role';



    public function getAssBreakDown($repid)
    {
        return $this->DB_get_one('repair','assid,breakdown',array('repid'=>$repid));
    }

}
