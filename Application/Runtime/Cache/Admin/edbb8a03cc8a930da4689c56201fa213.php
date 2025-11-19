<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Repair','Repair','dispatchingLists')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
</script>
<div class="layui-fluid" id="LAY-Repair-Repair-dispatchingLists">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form" action="" lay-filter="component-form-group">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">设备名称：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="dispatchingListsAssetsName" placeholder="请输入设备名称" name="dispatchingListsAssetsName">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备分类：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest2" id="dispatchingListsAssetsCat" placeholder="请输入设备分类" name="assetsCat">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">报修人：</label>
                                <div class="layui-input-inline">
                                    <select name="applicant" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($user)): $i = 0; $__LIST__ = $user;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["username"]); ?>"><?php echo ($vo["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">使用科室：</label>
                                <div class="layui-input-inline">
                                    <select name="assetsDep" class="input_select" lay-search="">
                                        <option value="">全部</option>
                                        <?php if(is_array($departments)): $i = 0; $__LIST__ = $departments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["departid"]); ?>"><?php echo ($vo["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if($repair_category): ?><div class="layui-inline">
                                    <label class="layui-form-label">维修类别：</label>
                                    <div class="layui-input-inline">
                                        <select name="repair_category" class="input_select" lay-search="">
                                            <option value="">全部</option>
                                            <?php if(is_array($repair_category)): $i = 0; $__LIST__ = $repair_category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key+1); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </div>
                                </div><?php endif; ?>
                            <div class="layui-inline">
                                <label class="layui-form-label">报修日期：</label>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="开始日期" style="cursor: pointer;" readonly name="startDate">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="结束日期" style="cursor: pointer;" readonly name="endDate">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">接单日期：</label>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="开始日期" style="cursor: pointer;" readonly name="response_startDate">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="结束日期" style="cursor: pointer;" readonly name="response_endDate">
                                </div>
                            </div>
                            <div class="layui-inline" style="margin-left: 10px;">
                                <div class="fl">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="dispatchingSearch" id="dispatchingSearch">
                                        <i class="layui-icon">&#xe615;</i> 搜 索
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
    <!--内容部分-->
        <div class="layui-row" style="margin-top: 15px">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">
                        <div class="fl">
                            <i class="layui-icon">&#xe62d;</i> 列表
                        </div>
                        <div class="fl" style="margin-left: 50px">
                            <!--业务操作部分-->
                            <!--<button class="layui-btn layui-btn-sm" id="addBusinessControl" data-url="/A/Repair/Repair/addBusinessControl.html">-->
                            <!--<i class="layui-icon">&#xe654;</i>新增业务-->
                            <!--</button>-->
                        </div>
                    </div>
                    <div class="layui-card-body">
                        <table id="getDispatchingLists" lay-filter="dispatchingData"></table>
                    </div>
                </div>

            </div>
        </div>
    <!--内容部分结束-->
</div>
<script>
    var userid="<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    layui.use('repair/repair/dispatchingLists', layui.factory('repair/repair/dispatchingLists'));
</script>