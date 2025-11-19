<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Statistics','StatisPurchases','purFeeStatis')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var purFeeStatis = "<?php echo ($purFeeStatis); ?>";
    var year = "<?php echo ($year); ?>";
</script>
<div class="layui-fluid" id="LAY-Statistics-StatisPurchases-purFeeStatis">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form" lay-filter="component-form-group">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">采购科室：</label>
                                <div class="layui-input-inline">
                                    <select name="departids" xm-select="purFeeStatisDepartment" xm-select-search="">
                                        <option value="">请选择采购科室</option>
                                        <?php if(is_array($departments)): $i = 0; $__LIST__ = $departments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["departid"]); ?>"><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">统计年份：</label>
                                    <div class="layui-input-inline">
                                        <input name="year" value="<?php echo ($year); ?>" readonly type="text" id="year" placeholder="请选择统计年份"  class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline" style="margin-left: 10px;">
                                    <div class="fl">
                                        <button class="layui-btn" type="button" lay-submit="" lay-filter="purFeeStatisSearch" id="purFeeStatisSearch">
                                            <i class="layui-icon">&#xe615;</i> 搜 索
                                        </button>
                                        <button type="reset" lay-submit="" lay-filter="reset" class="layui-btn layui-btn-primary">重置</button>
                                    </div>
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
                    <table id="purFeeStatisLists" lay-filter="purFeeStatisData"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use('statistics/statisPurchases/purFeeStatis', layui.factory('statistics/statisPurchases/purFeeStatis'));
</script>