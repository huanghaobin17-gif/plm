<?php
/**
 * 关于巡查模块用到的状态码
 */
return[
    //巡查保养级别设置
    'PATROL_LEVEL_DC'                  => 1,
    'PATROL_LEVEL_RC'                  => 2,
    'PATROL_LEVEL_PM'                  => 3,
    'PATROL_LEVEL_DC_ALIAS_NAME'       =>'DC',
    'PATROL_LEVEL_RC_ALIAS_NAME'       =>'RC',
    'PATROL_LEVEL_PM_ALIAS_NAME'       =>'PM',
    'PATROL_LEVEL_NAME_DC'             => '日常保养(DC)',
    'PATROL_LEVEL_NAME_RC'             => '巡查保养(RC)',
    'PATROL_LEVEL_NAME_PM'             => '预防性维护(PM)',
    //周期计划状态 sb_patrol_plan_cycle
    'PLAN_CYCLE_STANDBY'               =>'0',
    'PLAN_CYCLE_EXECUTION'             =>'1',
    'PLAN_CYCLE_COMPLETE'              =>'2',
    'PLAN_CYCLE_CHECK'                 =>'3',
    'PLAN_CYCLE_OVERDUE'               =>'4',
    'PLAN_CYCLE_STANDBY_NAME'          =>'待执行',
    'PLAN_CYCLE_EXECUTION_NAME'        =>'执行中',
    'PLAN_CYCLE_COMPLETE_NAME'         =>'待验收',
    'PLAN_CYCLE_CHECK_ACCEPTANCE_NAME' =>'验收中',
    'PLAN_CYCLE_CHECK_NAME'            =>'已完成',
    'PLAN_CYCLE_END_NAME'              =>'已结束',
    'PLAN_CYCLE_OVERDUE_NAME'          =>'逾期',
    //周期计划发布状态 sb_patrol_plan_cycle
    'PLAN_NOT_RELEASE'                 =>'0',
    'PLAN_IS_RELEASE'                  =>'1',
    'PLAN_NOT_RELEASE_NAME'            =>'未发布',
    'PLAN_IS_RELEASE_NAME'             =>'已发布',
    //设备是否转至报修 sb_patrol_execute
    'ASSETS_TO_REPAIR'                 =>1,
    'ASSETS_NOT_REPAIR'                =>0,
    'ASSETS_TO_REPAIR_NAME'            =>'是',
    'ASSETS_NOT_REPAIR_NAME'           =>'否',
    //巡查保养状态 sb_patrol_execute
    'MAINTAIN_EXECUTION'               =>'0',
    'MAINTAIN_PATROL'                  =>'1',
    'MAINTAIN_COMPLETE'                =>'2',
    'MAINTAIN_EXECUTION_NAME'          =>'待巡查',
    'MAINTAIN_PATROL_NAME'             =>'巡查中',
    'MAINTAIN_COMPLETE_NAME'           =>'完成',
    //巡查保养检查状态patrol_examine_all AND patrol_examine_one
    'CYCLE_STANDBY'                    =>0,
    'CYCLE_COMPLETE'                   =>1,
    'CYCLE_STANDBY_NAME'               =>'待验收',
    'CYCLE_EXECUTION_NAME'             =>'验收中',
    'CYCLE_COMPLETE_NAME'              =>'已验收'
];