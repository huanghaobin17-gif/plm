<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/25
 * Time: 9:44
 */

namespace Admin\Model;
use Think\Model;
use Think\Model\RelationModel;

class RepairOfferModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'repair_offer';



    public function getAssBreakDown($repid)
    {
        return $this->DB_get_one('repair','assid,breakdown',array('repid'=>$repid));
    }


}