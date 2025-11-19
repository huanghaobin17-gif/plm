<?php if (!defined('THINK_PATH')) exit();?><style>
    #LAY-Repair-RepairSearch-getRepairSearchList .layui-form-label{width: 85px;}
</style>
<?php if($menuData = get_menu_name('Repair','RepairSearch','getRepairSearchList')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<div class="layui-fluid" id="LAY-Repair-RepairSearch-getRepairSearchList" >
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">设备编号：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="getRepairSearchListAssetsNum" placeholder="请输入设备编号" name="getRepairSearchListAssetsNum">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">设备名称：</label>
                                <div class="layui-input-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control bsSuggest" id="getRepairSearchListAssets" placeholder="请输入设备名称" name="getRepairSearchListAssets">
                                        <div class="input-group-btn">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">维修单编号：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  name="getRepairSearchListRepnum"  value="" autocomplete="off" class="layui-input" placeholder="请输入维修单编号" />
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">所属科室：</label>
                                <div class="layui-input-inline">
                                    <select name="department" xm-select="department" xm-select-search="">
                                        <option value="">请选择所属科室</option>
                                        <?php if(is_array($departmentInfo)): $i = 0; $__LIST__ = $departmentInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["departid"]); ?>"><?php echo ($v["department"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label" >生产厂家：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  name="getRepairSearchListFactory"  value="" autocomplete="off" class="layui-input" placeholder="请输入生产厂家" />
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">维修状态：</label>
                                <div class="layui-input-inline">
                                    <select name="getRepairSearchListRepairStatus" lay-search="">
                                        <option value="">请选择</option>
                                        <?php if(is_array($repairStatus)): $i = 0; $__LIST__ = $repairStatus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">规格/型号：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  name="getRepairSearchListModel"  value="" autocomplete="off" class="layui-input" placeholder="请输入规格/型号" />
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">维修工程师：</label>
                                <div class="layui-input-inline">
                                    <select name="getRepairSearchListEngineer" lay-search="">
                                        <option value="">请选择维修工程师</option>
                                        <?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["username"]); ?>"><?php echo ($vo["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">报修人员：</label>
                                <div class="layui-input-inline">
                                    <select name="getRepairSearchListApplicant" lay-search="">
                                        <option value="">请选择报修人员</option>
                                        <?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["username"]); ?>"><?php echo ($vo["username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">验收状态：</label>
                                <div class="layui-input-inline">
                                    <select name="getRepairSearchListExamineStatus" lay-search="">
                                        <option value="">请选择</option>
                                        <option value="7">待验收</option>
                                        <option value="8">已验收</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">启用日期：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="getRepairSearchListOpendate" readonly placeholder="请选择启用日期" autocomplete="off" style="cursor: pointer;" class="layui-input formatDate" >
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">维修性质：</label>
                                <div class="layui-input-inline">
                                    <select name="getRepairSearchListRepairType" lay-search="">
                                        <option value="">请选择</option>
                                        <option value="0">自修</option>
                                        <option value="1">维保厂家</option>
                                        <option value="2">第三方维修</option>
                                        <option value="3">现场解决</option>
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
                                    <input class="layui-input formatDate" placeholder="开始日期" style="cursor: pointer;" readonly name="getRepairSearchListStartDate">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="结束日期" style="cursor: pointer;" readonly name="getRepairSearchListEndDate">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">维修日期：</label>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="开始日期" style="cursor: pointer;" readonly name="getRepairSearchListEngineerStartDate">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 83px;">
                                    <input class="layui-input formatDate" placeholder="结束日期" style="cursor: pointer;" readonly name="getRepairSearchListEngineerEndDate">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">是否保修：</label>
                                <div class="layui-input-inline" style="width: auto">
                                    <input type="radio" name="getRepairSearchListGuarantee" value="-1" title="全部" checked>
                                    <input type="radio" name="getRepairSearchListGuarantee" value="1" title="是">
                                    <input type="radio" name="getRepairSearchListGuarantee" value="0" title="否">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">是否接单：</label>
                                <div class="layui-input-inline" style="width: auto">
                                    <input type="radio" name="getRepairSearchListMeetStatus" value="0" title="全部" checked>
                                    <input type="radio" name="getRepairSearchListMeetStatus" value="1" title="未接单">
                                    <input type="radio" name="getRepairSearchListMeetStatus" value="2" title="已接单">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">是否产生配件</label>
                                <div class="layui-input-inline" style="width: auto">
                                    <input type="radio" name="getRepairSearchListIsnum" value="0" title="全部" checked>
                                    <input type="radio" name="getRepairSearchListIsnum" value="1" title="是">
                                    <input type="radio" name="getRepairSearchListIsnum" value="2" title="否">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <button class="layui-btn" lay-submit="" lay-filter="RepairSearchListSearch"><i class="layui-icon">&#xe615;</i> 搜 索 </button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
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
                    <i class="layui-icon">&#xe62d;</i> 列表
                </div>
                <div class="layui-card-body">
                    <div id="RepairSearchList" lay-filter="RepairSearchList"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="LAY-Repair-RepairSearch-getRepairSearchListToolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="exportHistory">
            <i class="layui-icon">&#xe654;</i>导出
        </button>
    </div>
</script>
<script>
    var userid="<?php echo session('userid'); ?>";
    var cookie_url = window.location.hash;
    layui.use('repair/search/getRepairSearchList', layui.factory('repair/search/getRepairSearchList'));
</script>