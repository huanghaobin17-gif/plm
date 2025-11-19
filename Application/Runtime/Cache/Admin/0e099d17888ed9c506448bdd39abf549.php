<?php if (!defined('THINK_PATH')) exit();?><style>
    #LAY-Assets-Lookup-assetsLifeList .layui-form .layui-inline .layui-input{width: 190px;}
</style>
<?php if($menuData = get_menu_name('Assets','Lookup','assetsLifeList')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){$('input, textarea').placeholder();});
    var assetsLifeList = "<?php echo ($assetsLifeList); ?>";
</script>
<div class="layui-fluid" id="LAY-Assets-Lookup-assetsLifeList" >
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
                                        <input type="text" class="form-control bsSuggest" id="assetsLifeListAssets"  placeholder="请输入设备名称" name="assetsLifeListAssets">
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
                                        <input type="text" class="form-control bsSuggest" id="assetsLifeListAssnum"  placeholder="请输入设备编号" name="assetsLifeListAssnum">
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
                                        <input type="text" class="form-control bsSuggest" id="assetsLifeListCategory" placeholder="请输入设备分类" name="assetsLifeListCategory">
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
                                        <input type="text" class="form-control bsSuggest" id="assetsLifeListAssorignum" placeholder="请输入设备原编码" name="assetsLifeListAssorignum">
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
                                    <select name="department" xm-select="assetsLifeListDepartment" xm-select-search="">
                                        <option value="">请选择科室名称</option>
                                        <?php if(is_array($departmentInfo)): $i = 0; $__LIST__ = $departmentInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["departid"]); ?>"><?php echo ($v["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">录入日期：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="assetsLifeListAddDate" id="assetsLifeListAddDate" value="" readonly placeholder="请选择录入日期" autocomplete="off" style="cursor: pointer;" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">录入人员：</label>
                                <div class="layui-input-inline">
                                    <select name="assetsLifeListAdduser" lay-search="" >
                                        <option value="">请选择录入人员</option>
                                        <?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["username"]); ?>"><?php echo ($vo["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline" style="margin-left: 10px;">
                                <div class="fl">
                                    <button class="layui-btn" lay-submit="" lay-filter="assetsLifeListSearch" ><i class="layui-icon">&#xe615;</i> 搜 索 </button>
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
                    <div id="assetsLifeList" lay-filter="assetsLifeList"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var header = <?php echo ($header); ?>;
</script>
<script>
    var userid="<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    layui.use('assets/lookup/assetsLifeList', layui.factory('assets/lookup/assetsLifeList'));
</script>
<script type="text/html" id="serialNumTpl">
    {{# return d.LAY_INDEX; }}
</script>
<script type="text/html" id="firTpl">
    {{#  if(d.is_firstaid === '是'){ }}
    <span style="color: #F581B1;">{{ d.is_firstaid }}</span>
    {{#  } else { }}
    {{ d.is_firstaid }}
    {{#  } }}
</script>
<script type="text/html" id="specTpl">
    {{#  if(d.is_special === '是'){ }}
    <span style="color: #F581B1;">{{ d.is_special }}</span>
    {{#  } else { }}
    {{ d.is_special }}
    {{#  } }}
</script>
<script type="text/html" id="meteTpl">
    {{#  if(d.is_metering === '是'){ }}
    <span style="color: #F581B1;">{{ d.is_metering }}</span>
    {{#  } else { }}
    {{ d.is_metering }}
    {{#  } }}
</script>
<script type="text/html" id="quaTpl">
    {{#  if(d.is_qualityAssets === '是'){ }}
    <span style="color: #F581B1;">{{ d.is_qualityAssets }}</span>
    {{#  } else { }}
    {{ d.is_qualityAssets }}
    {{#  } }}
</script>