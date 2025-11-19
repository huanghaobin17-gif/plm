<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Archives','Emergency','emergencyPlanList')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var emergencyPlanList = "<?php echo ($emergencyPlanList); ?>";
</script>
<style>
    .tdfile{
        color:#0a8ddf;
        cursor: pointer;
        margin-right: 14px;
    }
</style>
<div class="layui-fluid" id="LAY-Archives-Emergency-emergencyPlanList">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">预案名称：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="name" class="layui-input" placeholder="请输入预案名称" />
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">预案分类：</label>
                                <div class="layui-input-inline">
                                    <select name="category" xm-select="emergencyCategory" xm-select-search="">
                                        <option value="">请选择预案分类</option>
                                        <?php if(is_array($cates)): $i = 0; $__LIST__ = $cates;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["id"]); ?>"><?php echo ($v["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">关键字：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="keyword" class="layui-input" placeholder="请输入关键字" />
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">添加者：</label>
                                <div class="layui-input-inline">
                                    <select name="userid" xm-select="emergencyUser" xm-select-search="">
                                        <option value="">请选择添加者</option>
                                        <?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["userid"]); ?>"><?php echo ($v["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="text-align: center;margin-left: 10px;">
                                <div class="layui-input-inline">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="searchEmergency" id="searchEmergency"><i class="layui-icon">&#xe615;</i> 搜 索 </button>
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
                    <div id="emergencyPlanList" lay-filter="emergencyPlanData"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="LAY-Archives-Emergency-addEmergencyToolbar">
    <div class="layui-btn-container">
        <?php if($menuData = get_menu('Archives','Emergency','addEmergency')):?>
        <button class="layui-btn layui-btn-sm" lay-event="addEmergency" data-url="<?php echo ($menuData['actionurl']); ?>">
            <i class="layui-icon">&#xe654;</i><?php echo ($menuData['actionname']); ?>
        </button>
        <?php endif?>
    </div>
</script>
<script>
    var userid = "<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    layui.use('archives/emergency/emergencyPlanList', layui.factory('archives/emergency/emergencyPlanList'));
</script>