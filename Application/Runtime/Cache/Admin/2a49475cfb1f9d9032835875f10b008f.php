<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Statistics','StatisRepair','repairFeeTrend')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var repairFeeTrend = "<?php echo ($repairFeeTrend); ?>";
</script>
<div class="layui-fluid" id="LAY-Statistics-StatisRepair-repairFeeTrend">
    <div class="layui-row" style="margin-top: 15px">
        <div class="layui-col-md12">
            <div class="layui-card">
                <form class="layui-form" action="">
                    <div class="layui-input-inline chart-sel" style="margin: 20px 10px 5px 20px;width: 100px;">
                        <select name="count_type" lay-search="" lay-filter="change_count_type" >
                            <option value="free" selected>维修费用</option>
                            <option value="times">维修次数</option>
                            <option value="hours">维修工时</option>
                        </select>
                    </div>
                    <div class="layui-input-inline chart-sel" style="margin: 20px 10px 5px 20px;width: 90px;">
                        <select name="show_type" lay-search="" lay-filter="change_show_type" >
                            <option value="line" selected>折线图</option>
                            <option value="bar">柱状图</option>
                        </select>
                    </div>
                </form>
                <div class="layui-row">
                    <div class="layui-col-xs6 layui-col-md12 tb-padding" style="padding-right: 15px;">
                        <div class="grid-demo grid-demo-bg2">
                            <div class="grid-demo grid-demo-bg1">
                                <div class="hidden-contant">暂无相关数据</div>
                                <div id="repair_trend" class="div-chart-show-workjob"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row">

    </div>
    <div class="layui-row" style="margin-top: 15px">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form" lay-filter="component-form-group">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">统计年份：</label>
                                <div class="layui-input-inline">
                                    <input name="year" value="<?php echo ($year); ?>" readonly type="text" id="year_trend" placeholder="请选择统计年份"  class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">选择科室：</label>
                                <div class="layui-input-inline">
                                    <select name="departids" xm-select="repairTrendDepartment" xm-select-search="">
                                        <option value="">请选择科室</option>
                                        <?php if(is_array($departments)): $i = 0; $__LIST__ = $departments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["departid"]); ?>"><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="margin-left: 10px;">
                                <div class="fl">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="repairFeeTrendSearch" id="repairFeeTrendSearch">
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
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <div class="fl">
                        <i class="layui-icon">&#xe62d;</i> 列表
                    </div>
                </div>
                <div class="layui-card-body">
                    <table id="repairFeeTrendLists" lay-filter="repairFeeTrendData"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use('statistics/statisRepair/repairFeeTrend', layui.factory('statistics/statisRepair/repairFeeTrend'));
</script>