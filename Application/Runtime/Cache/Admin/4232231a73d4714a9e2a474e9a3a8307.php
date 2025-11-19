<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('BaseSetting','User','getUserList')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var getUserList = "<?php echo ($getUserList); ?>";
</script>
<style>
    #LAY-BaseSetting-User-getUserList .downloadConfirmReport{
        cursor: pointer;
        color: #01AAED;
    }
</style>
<div class="layui-fluid" id="LAY-BaseSetting-User-getUserList">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label cuslabelwidth">用户名称：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest"  id="bsSuggestUser" placeholder="请输入用户名称" name="username">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">管理科室：</label>
                                <div class="layui-input-inline">
                                    <select name="departid" lay-search="">
                                        <option value="">请选择管理科室</option>
                                        <?php if(is_array($department)): $i = 0; $__LIST__ = $department;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["departid"]); ?>"><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">所属角色：</label>
                                <div class="layui-input-inline">
                                    <select name="roleid" lay-search="">
                                        <option value="">请选择所属角色</option>
                                        <?php if(is_array($roleInfo)): $i = 0; $__LIST__ = $roleInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["roleid"]); ?>"><?php echo ($vo["role"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="text-align: center;margin-left: 10px;">
                                <div class="layui-input-inline">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="searchUser"
                                            id="searchUser"><i class="layui-icon">&#xe615;</i> 搜 索
                                    </button>
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
                    <div class="fr">
                        <a class="downloadConfirmReport">点击下载《批量添加用户模板》</a>
                    </div>
                </div>
                <div class="layui-card-body">
                    <div id="userLists" lay-filter="userData"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var userid = "<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    layui.use('basesetting/user/user', layui.factory('basesetting/user/user'));
</script>
<script type="text/html" id="LAY-BaseSetting-User-getUserListToolbar">
    <div class="layui-btn-container">
        <?php if($menuData = get_menu('BaseSetting','User','addUser')):?>
        <button class="layui-btn layui-btn-sm" lay-event="adduser" data-url="<?php echo ($menuData['actionurl']); ?>">
            <i class="layui-icon">&#xe654;</i><?php echo ($menuData['actionname']); ?>
        </button>
        <?php endif?>
        <?php if($menuData = get_menu('BaseSetting','User','batchDeleteUser')):?>
        <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="batchDeleteUser" data-url="<?php echo ($menuData['actionurl']); ?>">
            <i class="layui-icon">&#xe640;</i><?php echo ($menuData['actionname']); ?>
        </button>
        <?php endif?>
        <?php if($menuData = get_menu('BaseSetting','User','batchAddUser')):?>
        <button class="layui-btn layui-btn-sm " lay-event="batchAddUser" data-url="<?php echo ($menuData['actionurl']); ?>" id="batchAddUser" lay-data="{accept: 'file'}">
            <i class="layui-icon">&#xe654;</i>批量添加用户
        </button>
        <?php endif?>
    </div>
</script>