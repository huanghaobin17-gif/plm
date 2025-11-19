<?php if (!defined('THINK_PATH')) exit();?><style>
    #LAY-Assets-Lookup-lifeAssetsList #fields{
        width: 200px;
        margin-left: 20px;
        margin-bottom: 2px;
        height:32px;
    }
</style>
<?php if($menuData = get_menu_name('Assets','Lookup','getAssetsList')):?>
<title><?php echo ($actionName); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){$('input, textarea').placeholder();})
    var lifeAssetsList = "<?php echo ($lifeAssetsList); ?>";
</script>
<div class="layui-fluid" id="LAY-Assets-Lookup-lifeAssetsList" >
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">设备名称：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="lifeAssetsListAssets"  placeholder="请输入设备名称" name="assets">
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
                                        <input type="text" class="form-control bsSuggest" id="lifeAssetsListAssnum"  placeholder="请输入设备编号" name="assnum">
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
                                        <input type="text" class="form-control bsSuggest" id="lifeAssetsListCategory" placeholder="请输入设备分类" name="category">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">原编号：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" class="form-control bsSuggest" id="lifeAssetsListAssorignum" placeholder="请输入设备原编码" name="assorignum" />
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">科室名称：</label>
                                <div class="layui-input-inline">
                                    <select name="department" xm-select="lifeAssetsListDepartment" xm-select-search="">
                                        <option value="">请选择科室名称</option>
                                        <?php if(is_array($departmentInfo)): $i = 0; $__LIST__ = $departmentInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["departid"]); ?>"><?php echo ($v["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备状态：</label>
                                <div class="layui-input-inline">
                                    <select name="status" lay-search="" >
                                        <option value="">请选择设备状态</option>
                                        <option value="0">在用</option>
                                        <option value="1">维修中</option>
                                        <option value="2">已报废</option>
                                        <option value="5">报废中</option>
                                        <option value="6">转科中</option>
                                        <option value="7">质控中</option>
                                        <option value="8">巡查中</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">录入日期：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="add_date" id="lifeAssetsListAdddate" value="" readonly placeholder="请选择录入日期" autocomplete="off" style="cursor: pointer;" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">录入人员：</label>
                                <div class="layui-input-inline">
                                    <select name="add_user" lay-search="" >
                                        <option value="">请选择录入人员</option>
                                        <?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["username"]); ?>"><?php echo ($vo["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="margin-left: 10px;">
                                <div class="fl">
                                    <button class="layui-btn" lay-submit="" lay-filter="lifeAssetsListsSearch" ><i class="layui-icon">&#xe615;</i> 搜 索 </button>
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
                    <div id="lifeAssetsList" lay-filter="lifeAssetsList"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var userid="<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    cookie_url = cookie_url.split('?')[0]+'2';
    layui.use('assets/lookup/lifeAssetsList', layui.factory('assets/lookup/lifeAssetsList'));
</script>
<script type="text/html" id="serialNumTpl">
    {{# return d.LAY_INDEX; }}
</script>
<script type="text/html" id="statusFormat">
    {{#  if(d.status === '已报废'){ }}
    <span style="color: #FF5722;">{{ d.status }}</span>
    {{#  } else if(d.status === '维修中'){ }}
    <span style="color: #FFB800;">{{ d.status }}</span>
    {{#  } else if(d.status === '报废中'){ }}
    <span style="color: #FFB800;">{{ d.status }}</span>
    {{#  } else if(d.status === '转科中'){ }}
    <span style="color: #FFB800;">{{ d.status }}</span>
    {{#  } else { }}
    {{ d.status }}
    {{#  } }}
</script>
<script type="text/html" id="firTpl">
    {{#  if(d.is_firstaid === '是'){ }}
    <span style="color: #FF5722;">{{ d.is_firstaid }}</span>
    {{#  } else { }}
    {{ d.is_firstaid }}
    {{#  } }}
</script>
<script type="text/html" id="specTpl">
    {{#  if(d.is_special === '是'){ }}
    <span style="color: #FF5722;">{{ d.is_special }}</span>
    {{#  } else { }}
    {{ d.is_special }}
    {{#  } }}
</script>
<script type="text/html" id="meteTpl">
    {{#  if(d.is_metering === '是'){ }}
    <span style="color: #FF5722;">{{ d.is_metering }}</span>
    {{#  } else { }}
    {{ d.is_metering }}
    {{#  } }}
</script>
<script type="text/html" id="quaTpl">
    {{#  if(d.is_qualityAssets === '是'){ }}
    <span style="color: #FF5722;">{{ d.is_qualityAssets }}</span>
    {{#  } else { }}
    {{ d.is_qualityAssets }}
    {{#  } }}
</script>
<script type="text/html" id="benTpl">
    {{#  if(d.is_benefit === '是'){ }}
    <span style="color: #FF5722;">{{ d.is_benefit }}</span>
    {{#  } else { }}
    {{ d.is_benefit }}
    {{#  } }}
</script>