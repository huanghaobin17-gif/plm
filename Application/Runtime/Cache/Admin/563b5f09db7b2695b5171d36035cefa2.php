<?php if (!defined('THINK_PATH')) exit();?><script>
    var getMeteringResult = "<?php echo ($getMeteringResult); ?>";
</script>
<?php if($menuData = get_menu_name('Metering','Metering','getMeteringResult')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<div class="layui-fluid" id="LAY-Metering-Metering-getMeteringResult">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">所属科室：</label>
                                <div class="layui-input-inline">
                                    <select name="departid" xm-select="mer_res_department" xm-select-search="">
                                        <option value="">请选择所属科室</option>
                                        <?php if(is_array($departmentInfo)): $i = 0; $__LIST__ = $departmentInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["departid"]); ?>"><?php echo ($v["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备名称：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="getMeteringResultAssetsName" placeholder="请输入设备名称" name="assetsName">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-form-label" style="width: 100px;">设备编码：</div>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" placeholder="请输入设备编码" name="assnum">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-form-label" style="width: 100px;">产品序列号：</div>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" placeholder="请输入产品序列号" name="productid">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">计量分类：</label>
                                <div class="layui-input-inline">
                                    <select name="categorys" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($categorys)): $i = 0; $__LIST__ = $categorys;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["mcid"]); ?>"><?php echo ($vo["mcategory"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="layui-inline">
                                <label class="layui-form-label" style="width: 100px;">下次待检日期：</label>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input class="layui-input" placeholder="开始日期" readonly style="cursor: pointer;"
                                           name="startDate" id="startDate">
                                </div>
                                <div class="layui-form-mid">~</div>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input class="layui-input" placeholder="结束日期" readonly style="cursor: pointer;"
                                           name="endDate" id="endDate">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="width: 100px;">提前提醒天数：</label>
                                <div class="layui-input-inline" style="width: 50px;">
                                    <input type="text" name="day_min" class="layui-input">
                                </div>
                                <div class="layui-form-mid">~</div>
                                <div class="layui-input-inline" style="width: 50px;">
                                    <input type="text" name="day_max" class="layui-input">
                                </div>
                            </div>


                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="checkbox" name="getMeteringResult_is_metering" value="1" lay-filter="metering" lay-skin="primary" title="计量设备" checked>
                            </div>
                            <div class="layui-inline">
                                <button class="layui-btn" type="button" lay-submit="" lay-filter="getMeteringResultSearch" id="getMeteringResultSearch">
                                    <i class="layui-icon">&#xe615;</i> 搜 索
                                </button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div><!--layui-card-->
        </div>
    </div>

    <div class="layui-row table-list">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <div class="fl">
                        <i class="layui-icon">&#xe62d;</i> 检定结果列表
                    </div>
                </div>
                <div class="layui-card-body">
                    <table id="getMeteringResult" lay-filter="getMeteringResultData"></table>
                </div>
            </div>
        </div>
    </div>


</div>
<script>
    var userid="<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    layui.use('metering/metering/getMeteringResult', layui.factory('metering/metering/getMeteringResult'));
</script>
<script type="text/html" id="LAY-Metering-Metering-getMeteringResultToolbar">
    <div class="layui-btn-container">
        <?php if($menuData = get_menu('Metering','Metering','batchSetMeteringResult')):?>
        <button class="layui-btn layui-btn-sm" lay-event="batchSetMeteringResult" data-url="<?php echo ($menuData['actionurl']); ?>">
            <i class="layui-icon">&#xe67c;</i>批量添加计量记录
        </button>
        <?php endif?>
    </div>
</script>