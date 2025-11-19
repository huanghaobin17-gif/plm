<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Statistics','StatisRepair','repairFeeStatis')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var repairFeeStatis = "<?php echo ($repairFeeStatis); ?>";
</script>
<div class="layui-fluid" id="LAY-Statistics-StatisRepair-repairFeeStatis">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form" lay-filter="component-form-group">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">所属科室：</label>
                                <div class="layui-input-inline">
                                    <select name="departid" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($departments)): $i = 0; $__LIST__ = $departments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><optgroup label="<?php echo ($vo["hospital_name"]); ?>">
                                                <?php if(is_array($vo["list"])): $i = 0; $__LIST__ = $vo["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["departid"]); ?>"><?php echo ($v["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                            </optgroup><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">维修类型：</label>
                                <div class="layui-input-inline">
                                    <select name="repair_type" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($repair_type)): $i = 0; $__LIST__ = $repair_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">故障类型：</label>
                                <div class="layui-input-inline">
                                    <select name="fault_type" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($fault_type)): $i = 0; $__LIST__ = $fault_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备分类：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="hidden" name="catid" value=""/>
                                        <input type="text" class="form-control bsSuggest" id="repairFeeStatisCat" placeholder="请输入设备分类">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">工程师：</label>
                                <div class="layui-input-inline">
                                    <select name="engineer" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["username"]); ?>"><?php echo ($vo["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">完成日期：</label>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input" placeholder="开始日期" readonly value="<?php echo ($start_date); ?>" style="cursor: pointer;" name="startDate" id="repairFeeS" lay-verify="startDate">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input" placeholder="结束日期" readonly value="<?php echo ($end_date); ?>" style="cursor: pointer;" name="endDate" id="repairFeeE" lay-verify="endDate">
                                </div>
                            </div>
                            <div class="layui-inline" style="margin-left: 10px;">
                                <div class="fl">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="repairFeeStatisSearch" id="repairFeeStatisSearch">
                                        <i class="layui-icon">&#xe615;</i> 搜 索
                                    </button>
                                    <button type="reset" lay-submit="" lay-filter="reset" class="layui-btn layui-btn-primary">重置</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row" style="margin-top: 15px">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <div class="fl">
                        <i class="layui-icon">&#xe62d;</i> 列表
                    </div>
                </div>
                <div class="layui-card-body">
                    <table id="repairFeeStatisLists" lay-filter="repairFeeStatisData"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use('statistics/statisRepair/repairFeeStatis', layui.factory('statistics/statisRepair/repairFeeStatis'));
</script>