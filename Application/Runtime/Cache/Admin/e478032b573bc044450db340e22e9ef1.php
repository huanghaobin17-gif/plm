<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Archives','Box','boxList')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var boxList = "<?php echo ($boxList); ?>";
</script>
<style>
    .tdfile{
        color:#0a8ddf;
        cursor: pointer;
        margin-right: 14px;
    }
</style>
<div class="layui-fluid" id="LAY-Archives-Box-boxList">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">档案编号：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="box_num"  placeholder="请输入档案编号" name="box_num">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备名称：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="boxListAssets"  placeholder="请输入设备名称" name="assets">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备编号：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="boxListAssnum"  placeholder="请输入设备编号" name="assnum">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">所属科室：</label>
                                <div class="layui-input-inline">
                                    <select name="department" xm-select="boxListDepartment" xm-select-search="">
                                        <option value="">请选择所属科室</option>
                                        <?php if(is_array($departmentInfo)): $i = 0; $__LIST__ = $departmentInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["departid"]); ?>"><?php echo ($v["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="text-align: center;margin-left: 10px;">
                                <div class="layui-input-inline">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="searchBox" id="searchBox"><i class="layui-icon">&#xe615;</i> 搜 索 </button>
                                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row table-list">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <div class="fl">
                        <i class="layui-icon">&#xe62d;</i> 列表
                    </div>
                </div>
                <div class="layui-card-body">
                    <div id="boxList" lay-filter="boxData"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="LAY-Archives-Box-boxListToolbar">
    <div class="layui-btn-container">
        <?php if($menuData = get_menu('Archives','Box','addBox')):?>
        <button class="layui-btn layui-btn-sm" lay-event="addBox" data-url="<?php echo ($menuData['actionurl']); ?>">
            <i class="layui-icon">&#xe654;</i><?php echo ($menuData['actionname']); ?>
        </button>
        <?php endif?>
    </div>
</script>
<script>
    var userid = "<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    var expire_days = "<?php echo ($expire_days); ?>";
    layui.use('archives/box/boxList', layui.factory('archives/box/boxList'));
</script>