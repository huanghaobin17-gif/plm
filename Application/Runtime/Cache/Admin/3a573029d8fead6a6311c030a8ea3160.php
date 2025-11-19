<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('BaseSetting','ModuleSetting','module')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<style>
    .layui-table .layui-input-inline {
        margin-left: -10px;
        width: 108%;
    }
    .fanwei{
        width: 450px;
        float: left;
        margin-left: 20px;
    }
</style>

<div class="layui-fluid" id="LAY-BaseSetting-ModuleSetting-module">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane"  action="">
                        <input type="hidden" name="action" value="<?php echo ($url); ?>"/>
                        <div class="content">
                            <blockquote class="layui-elem-quote module-blockquote">医院配置</blockquote>
                            <table class="layui-table">
                                <colgroup>
                                    <col width="6%">
                                    <col width="22%">
                                    <col width="10%">
                                    <col width="10%">
                                    <col width="13%">
                                    <col width="13%">
                                    <col>
                                </colgroup>
                                <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="text-align: center;">院系</th>
                                    <th style="text-align: center;">医院名称（最长14个字符）</th>
                                    <th style="text-align: center;">代码</th>
                                    <th style="text-align: center;">联系人</th>
                                    <th style="text-align: center;">联系电话</th>
                                    <th style="text-align: center;">采购年限下限</th>
                                    <th style="text-align: center;">联系地址</th>
                                </tr>
                                </thead>
                                <tbody id="module_hospitals">
                                <?php if($hospitals): if(is_array($hospitals)): $i = 0; $__LIST__ = $hospitals;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                            <?php if($vo["is_general_hospital"] == 1): ?><td style="text-align: center">总院</td>
                                                <?php else: ?>
                                                <td style="text-align: center">分院</td><?php endif; ?>
                                            <input type="hidden" name="hospital_id[]" value="<?php echo ($vo["hospital_id"]); ?>"/>
                                            <input type="hidden" name="is_general_hospital[]" value="<?php echo ($vo["is_general_hospital"]); ?>"/>
                                            <td>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="hospital_name[]" value="<?php echo ($vo["hospital_name"]); ?>" maxlength="14" lay-verify="required|hospital_name" placeholder="医院名称" autocomplete="off" class="layui-input">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="hospital_code[]" <?php if($canEdit[$vo['hospital_id']] == 0): ?>readonly<?php endif; ?> value="<?php echo ($vo["hospital_code"]); ?>" lay-verify="required|hospital_code" autocomplete="off" placeholder="医院代码" class="layui-input">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="contacts[]" value="<?php echo ($vo["contacts"]); ?>" lay-verify="required|contacts" autocomplete="off" placeholder="联系人" class="layui-input">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="phone[]" value="<?php echo ($vo["phone"]); ?>" lay-verify="required" autocomplete="off" placeholder="联系电话" class="layui-input">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="amount_limit[]" value="<?php echo ($vo["amount_limit"]); ?>" lay-verify="required|amount_limit|number" autocomplete="off" placeholder="采购年限下限" class="layui-input">
                                            </td>
                                            <td>
                                                <div class="layui-input-inline">
                                                    <input type="text" name="address[]" value="<?php echo ($vo["address"]); ?>" lay-verify="required|address" autocomplete="off" placeholder="详细地址" class="layui-input">
                                                </div>
                                            </td>
                                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td style="text-align: center">总院</td>
                                        <input type="hidden" name="hospital_id[]" value="1"/>
                                        <input type="hidden" name="is_general_hospital[]" value="1"/>
                                        <td>
                                            <div class="layui-input-inline">
                                                <input type="text" name="hospital_name[]" value="" lay-verify="required|hospital_name" placeholder="医院名称" autocomplete="off" class="layui-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="layui-input-inline">
                                                <input type="text" name="hospital_code[]" value="" lay-verify="required|hospital_code" autocomplete="off" placeholder="医院代码" class="layui-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="layui-input-inline">
                                                <input type="text" name="contacts[]" value="" lay-verify="required|contacts" autocomplete="off" placeholder="联系人" class="layui-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="layui-input-inline">
                                                <input type="text" name="phone[]" value="" lay-verify="required" autocomplete="off" placeholder="联系电话" class="layui-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="layui-input-inline">
                                                <input type="text" name="amount_limit[]" value="" lay-verify="required" autocomplete="off" placeholder="采购年限下限" class="layui-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="layui-input-inline">
                                                <input type="text" name="address[]" value="" lay-verify="required|address" autocomplete="off" placeholder="详细地址" class="layui-input">
                                            </div>
                                        </td>
                                    </tr><?php endif; ?>
                                </tbody>
                            </table>
                            <?php if($canAddHospital == 1): ?><div style="text-align: center">
                                    <a class="addhospital" >&gt;&gt;新增分院&lt;&lt; </a>
                                </div><?php endif; ?>
                        </div>
                        <div class="content">
                            <blockquote class="layui-elem-quote module-blockquote">首页显示设置</blockquote>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">科室维修费用分析：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_depart_repair][is_open]" lay-filter="target_chart_depart_repair" value="1" title="显示" <?php if($settings['target_setting']['target_chart_depart_repair'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_depart_repair][is_open]" lay-filter="target_chart_depart_repair" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_depart_repair'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_depart_repair" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_depart_repair][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_depart_repair'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_depart_repair][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_depart_repair'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">设备增加情况：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_add][is_open]" lay-filter="target_chart_assets_add" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_add'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_add][is_open]" lay-filter="target_chart_assets_add" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_add'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_add" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_add][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_add'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_add][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_add'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">设备报废情况：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_scrap][is_open]" lay-filter="target_chart_assets_scrap" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_scrap'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_scrap][is_open]" lay-filter="target_chart_assets_scrap" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_scrap'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_scrap" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_scrap][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_scrap'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_scrap][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_scrap'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">设备采购支出情况：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_purchases][is_open]" lay-filter="target_chart_assets_purchases" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_purchases'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_purchases][is_open]" lay-filter="target_chart_assets_purchases" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_purchases'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_purchases" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_purchases][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_purchases'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_purchases][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_purchases'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left;width: 360px;">
                                    <label class="layui-form-label modulelabel">设备效益分析：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_benefit][is_open]" lay-filter="target_chart_assets_benefit" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_benefit'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_benefit][is_open]" lay-filter="target_chart_assets_benefit" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_benefit'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_benefit" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_benefit][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_benefit'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_benefit][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_benefit'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">不良事件情况：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_adverse][is_open]" lay-filter="target_chart_assets_adverse" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_adverse'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_adverse][is_open]" lay-filter="target_chart_assets_adverse" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_adverse'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_adverse" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_adverse][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_adverse'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_adverse][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_adverse'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">设备转移情况：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_move][is_open]" lay-filter="target_chart_assets_move" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_move'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_move][is_open]" lay-filter="target_chart_assets_move" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_move'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_adverse" style="display: none;">-->
                                    <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                    <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_move][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_move'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                        <!--<input type="radio" name="target_setting[target_chart_assets_move][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_move'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>
                            <div>
                                <div class="layui-form-item" pane="" style="float:left; width: 360px;">
                                    <label class="layui-form-label modulelabel">设备保养情况：</label>
                                    <div class="layui-input-block radio-margin-left">
                                        <input type="radio" name="target_setting[target_chart_assets_patrol][is_open]" lay-filter="target_chart_assets_patrol" value="1" title="显示" <?php if($settings['target_setting']['target_chart_assets_patrol'][is_open] == 1): ?>checked<?php endif; ?> >
                                        <input type="radio" name="target_setting[target_chart_assets_patrol][is_open]" lay-filter="target_chart_assets_patrol" value="0" title="不显示" <?php if($settings['target_setting']['target_chart_assets_patrol'][is_open] != 1): ?>checked<?php endif; ?> >
                                    </div>
                                </div>
                                <!--<div class="fanwei target_chart_assets_adverse" style="display: none;">-->
                                <!--<label class="layui-form-label modulelabel">统计范围：</label>-->
                                <!--<div class="layui-input-block radio-margin-left" style="border: 1px solid #e6e6e6;margin-left: 1px !important;">-->
                                <!--<input type="radio" name="target_setting[target_chart_assets_adverse][fanwei]" value="all" title="全部科室" <?php if($settings['target_setting']['target_chart_assets_adverse'][fanwei] == 'all'): ?>checked<?php endif; ?> >-->
                                <!--<input type="radio" name="target_setting[target_chart_assets_adverse][fanwei]" value="not_all" title="用户管理科室" <?php if($settings['target_setting']['target_chart_assets_adverse'][fanwei] != 'all'): ?>checked<?php endif; ?> >-->
                                <!--</div>-->
                                <!--</div>-->
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <!--采购管理-->
                        
                        <?php if($settings_display["purchaseSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">采购管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="purchases[purchases_open][is_open]" lay-filter="purchases_open" value="1" title="开启" <?php if($settings['purchases']['purchases_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="purchases[purchases_open][is_open]" lay-filter="purchases_open" value="0" title="关闭" <?php if($settings['purchases']['purchases_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--设备管理-->
                        <?php if($settings_display["assetsSetting"] == 1 ): ?><style>
    .layui-form-checkbox[lay-skin=primary] span{
        padding-right: 0;
    }
</style>
<a name="assets"></a>
<blockquote class="layui-elem-quote module-blockquote">设备管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important;">
    <label class="layui-form-label modulelabel">设备编码规则：</label>
    <div class="layui-input-block assets radio-margin-left" id="assetsModuleSettingCode">
        <input type="checkbox" id="num_catenum" <?php echo ($assets_encoding_rules["categoryCode"]); ?> name="assets[assets_encoding_rules][categoryCode]" lay-skin="primary" title="分类编号 +">
        <input type="checkbox" id="num_departnum" <?php echo ($assets_encoding_rules["departmentCode"]); ?> name="assets[assets_encoding_rules][departmentCode]" lay-skin="primary" title="科室编号 + 自增ID值 = 设备编号">
        <div style="float: right;" class="layui-form-mid layui-word-aux">请在系统安装后配置,后期改动会导致已打印的标签无法扫描检索</div>
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline assets">
        <label class="layui-form-label modulelabel">辅助分类：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[assets_helpcat]" value="<?php echo (implode('|',$settings['assets']['assets_helpcat']));?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">请用 | 分隔，设置好后，请勿频繁改动！</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">资金来源：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[assets_capitalfrom]" value="<?php echo (implode('|',$settings['assets']['assets_capitalfrom']));?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">请用 | 分隔，设置好后，请勿频繁改动！</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">资产来源：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[assets_assfrom]" value="<?php echo (implode('|',$settings['assets']['assets_assfrom']));?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">请用 | 分隔，设置好后，请勿频繁改动！</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">财务分类：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[assets_finance]" value="<?php echo (implode('|',$settings['assets']['assets_finance']));?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">请用 | 分隔，设置好后，请勿频繁改动！</div>
    </div>
</div>
<div class="layui-form-item assets">
    <label class="layui-form-label modulelabel-2">打印报告顶部logo：</label>
    <div class="layui-input-block repair repairModuleSetting-input radio-margin-left">
        <button type="button" class="layui-btn" id="uploadRepairReportLogo">
            <i class="layui-icon"></i>上传图片
        </button>
        <button class="layui-btn layui-btn-normal" id="showRepairReportLogo">查看图片</button>
        <input name="file_url" value="<?php echo ($settings['all_module']['all_report_logo']); ?>" type="hidden">
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">借调审批单标题：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[borrow_template][title]" value="<?php echo ($settings['assets']['borrow_template'][title]); ?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux"> + 借调审批单 = 借调审批单标题</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">转科审批单标题：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[transfer_template][title]" value="<?php echo ($settings['assets']['transfer_template'][title]); ?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux"> + 转科审批单 = 转科审批单标题</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">外调审批单标题：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[outside_template][title]" value="<?php echo ($settings['assets']['outside_template'][title]); ?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux"> + 外调审批单 = 外调审批单标题</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">报废审批单标题：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="assets[scrap_template][title]" value="<?php echo ($settings['assets']['scrap_template'][title]); ?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux"> + 报废审批单 = 报废审批单标题</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label" style="width: 190px;">设备入库提醒时间范围：</label>
        <div class="layui-input-inline assets" style="width: auto" >
            <input type="text" name="assets[assets_add_remind_day]" value="<?php echo ($settings['assets']['assets_add_remind_day']); ?>" class="layui-input" style="width: 70px;" lay-verify="number">
        </div>
        <div class="layui-form-mid layui-word-aux">天</div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">借调归还时间范围：</label>
        <div class="layui-input-inline assets" style="width: 100px;">
            <input type="text" class="layui-input" placeholder="开始时间" style="cursor: pointer;" name="assets[apply_borrow_back_time_startDate]" id="assetsSettingStartDate" value="<?php echo ($settings['assets']['apply_borrow_back_time'][0]); ?>">
        </div>
        <div class="layui-form-mid">~</div>
        <div class="layui-input-inline assets" style="width: 100px;">
            <input type="text" class="layui-input" placeholder="结束时间" style="cursor: pointer;" name="assets[apply_borrow_back_time_endDate]" id="assetsSettingEndDate" value="<?php echo ($settings['assets']['apply_borrow_back_time'][1]); ?>">
        </div>
    </div>
</div>
<div class="layui-form-item assets">
    <div class="layui-inline">
        <label class="layui-form-label" style="width: 260px;">设备报废超出多少金额标注重点：</label>
        <div class="layui-input-inline assets" style="width: auto" >
            <input type="text" name="assets[assets_scrap_overPrice]" value="<?php echo ($settings['assets']['assets_scrap_overPrice']); ?>" class="layui-input" style="width: 100px;" lay-verify="number">
        </div>
        <div class="layui-form-mid layui-word-aux">元</div>
    </div>
</div>
<!--<div class="layui-form-item assets">-->
    <!--<div class="layui-inline">-->
        <!--<label class="layui-form-label" style="width: 220px;">报废即将到期提前提醒天数：</label>-->
        <!--<div class="layui-input-inline assets" style="width: auto" >-->
            <!--<input type="text" name="assets[assets_scrap_licenseDay]" value="<?php echo ($settings['assets']['assets_scrap_licenseDay']); ?>" class="layui-input" style="width: 70px;" lay-verify="number">-->
        <!--</div>-->
        <!--<div class="layui-form-mid layui-word-aux">天</div>-->
    <!--</div>-->
<!--</div>-->
<script>
    layui.use(['laydate'], function () {
       var  laydate = layui.laydate;
        laydate.render({
            elem: '#assetsSettingStartDate',
            type: 'time'
        });

        laydate.render({
            elem: '#assetsSettingEndDate',
            type: 'time'
        });
    });

</script><?php endif; ?>
                        <!--维修管理-->
                        <?php if($settings_display["repairSetting"] == 1 ): ?><a name="repair"></a>
<blockquote class="layui-elem-quote module-blockquote">维修管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="repair[repair_open][is_open]" lay-filter="repair_open" value="1" title="开启" <?php if($settings['repair']['repair_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="repair[repair_open][is_open]" lay-filter="repair_open" value="0" title="关闭" <?php if($settings['repair']['repair_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2">维修单编码规则：</label>
        <div class="layui-input-inline" style="width: 420px;">
            <div class="layui-input-inline repair" style="width: 90px;">
                <input style="width: 100px;" type="text" name="repair[repair_encoding_rules][prefix]" value="<?php echo ($settings['repair']['repair_encoding_rules'][prefix]); ?>" autocomplete="off" class="layui-input">
            </div>
            <span style='padding: 9px; line-height: 20px;float: left;'>+  报修日期 + “_” + 流水号 = 设备维修维护编号</span><br>
        </div>
        <div class="layui-form-mid layui-word-aux">注意：如维修单不需要前缀，请置空输入框</div>
    </div>
</div>
<div class="layui-form-item repair">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel">自定义维修类别：</label>
        <div class="layui-input-inline width450">
            <input type="text" name="repair[repair_category]" value="<?php echo (implode('|',$settings['repair']['repair_category']));?>" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">请用 | 分隔，设置好后，请勿频繁改动！</div>
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2">维修报告标题：</label>
        <div class="layui-input-inline" style="width: 600px;">
            <div class="layui-input-inline repair">
                <input  type="text" name="repair[repair_template][title]" value="<?php echo ($settings['repair']['repair_template'][title]); ?>" autocomplete="off" class="layui-input">
            </div>
            <span style='padding: 9px; line-height: 20px;float: left;'> + 维修报告 = 维修报告标题</span>
            <div class="layui-form-mid layui-word-aux">如果为空，默认前缀为“医疗设备”</div>
        </div>
    </div>
</div>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">维修报告模板：</label>
    <div class="layui-input-block repair repairModuleSetting-input radio-margin-left">
        <input type="radio" name="repair[repair_template][version]" value="1" title="默认"<?php if($settings['repair']['repair_template'][version] == 1): ?>checked<?php endif; ?>>
        <span data-src="/A/Repair/examine?action=printReport&onlyShow=1" onclick="showimg($(this).data('src'))">
            点击预览
        </span>
        <input type="radio" name="repair[repair_template][version]" value="2" title="模板1"<?php if($settings['repair']['repair_template'][version] != 1): ?>checked<?php endif; ?>>
        <span data-src="/A/Repair/examine?action=printReport&onlyShow=2" onclick="showimg($(this).data('src'))">
            点击预览
        </span>
    </div>
</div>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">水印设置：</label>
    <div class="layui-input-block repair repairModuleSetting-input radio-margin-left">
        <!--<span data-src="/Public/images/nowarterpdf.jpg" onclick="showimg($(this).data('src'))">-->
        <!--点击预览-->
        <!--</span>-->
        <!---->
        <!--<span data-src="/Public/images/warterpdf.jpg" onclick="showimg($(this).data('src'))">-->
        <!--点击预览-->
        <!--</span>-->
        <input type="radio" name="repair[repair_tmp][style]" lay-filter="repair_water" value="1" title="无水印"<?php if($settings['repair']['repair_tmp'][style] == 1): ?>checked<?php endif; ?>>
        <input type="radio" name="repair[repair_tmp][style]" lay-filter="repair_water" value="2" title="有水印"<?php if($settings['repair']['repair_tmp'][style] != 1): ?>checked<?php endif; ?>>
    </div>
</div>
<div class="layui-form-item watermark" style="display: none;">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2">水印文字：</label>
        <div class="layui-input-inline repair">
            <input type="text" name="repair[repair_print_watermark][watermark]" value="<?php echo ($settings['repair']['repair_print_watermark'][watermark]); ?>" autocomplete="off" class="layui-input" style="width: 220px;" maxlength="12">
        </div>
        <div class="layui-form-mid layui-word-aux input-tips">维修单模板选择有水印时必填，最长12个字符</div>
    </div>
</div>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">微信扫码签到功能：</label>
    <div class="layui-input-block repair radio-margin-left">
        <input type="radio" name="repair[open_sweepCode_overhaul][open]"  value="1" title="开启" <?php if($settings['repair']['open_sweepCode_overhaul'][open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="repair[open_sweepCode_overhaul][open]"  value="0" title="关闭" <?php if($settings['repair']['open_sweepCode_overhaul'][open] != 1): ?>checked<?php endif; ?> >
    </div>
</div>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important;">
    <label class="layui-form-label modulelabel-2">表单必填项开关：</label>
    <div class="layui-input-block repair radio-margin-left">
        <input type="checkbox" name="repair[repair_required][repair_date]" lay-skin="primary" title="报修日期" value="1" disabled="disabled"<?php if($settings['repair']['repair_required']['repair_date'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_required][repair_person]" lay-skin="primary" title="报修人" value="1" disabled="disabled"<?php if($settings['repair']['repair_required']['repair_person'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_required][repair_phone]" lay-skin="primary" title="报修电话" value="1" disabled="disabled"<?php if($settings['repair']['repair_required']['repair_phone'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_required][service_date]" lay-skin="primary" title="维修日期" value="1" disabled="disabled"<?php if($settings['repair']['repair_required']['service_date'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_required][service_working]" lay-skin="primary" title="维修工时" value="1" disabled="disabled"<?php if($settings['repair']['repair_required']['service_working'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_required][repair_check]" lay-skin="primary" title="验收时间" value="1" disabled="disabled"<?php if($settings['repair']['repair_required']['repair_check'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_required][repair_detail]" lay-skin="primary" title="报修故障描述" value="1"<?php if($settings['repair']['repair_required']['repair_detail'] == 1): ?>checked<?php endif; ?>/>
    </div>
</div>

<div class="layui-form-item" pane="" style="margin-bottom:25px!important;">
    <label class="layui-form-label modulelabel-2">系统生成字段开关：</label>
    <div class="layui-input-block repair radio-margin-left">
        <input type="checkbox" name="repair[repair_system][repair_date]" lay-skin="primary" title="报修日期" lay-filter="repair_date" value="1"<?php if($settings['repair']['repair_system']['repair_date'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_system][repair_person]" lay-skin="primary" title="报修人" lay-filter="repair_person" value="1"<?php if($settings['repair']['repair_system']['repair_person'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_system][repair_phone]" lay-skin="primary" title="报修电话" lay-filter="repair_phone" value="1"<?php if($settings['repair']['repair_system']['repair_phone'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_system][service_date]" lay-skin="primary" title="维修日期" lay-filter="service_date" value="1"<?php if($settings['repair']['repair_system']['service_date'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_system][service_working]" lay-skin="primary" title="维修工时" lay-filter="service_working" value="1"<?php if($settings['repair']['repair_system']['service_working'] == 1): ?>checked<?php endif; ?>/>
        <input type="checkbox" name="repair[repair_system][repair_check]" lay-skin="primary" title="验收时间" lay-filter="repair_check" value="1"<?php if($settings['repair']['repair_system']['repair_check'] == 1): ?>checked<?php endif; ?>/>
    </div>
</div>

<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">接单预计到场时间上限：</label>
        <div class="layui-input-inline repair" >
            <input type="text" name="repair[repair_uptime]" value="<?php echo ($settings['repair']['repair_uptime']); ?>" class="layui-input" lay-verify="uptime">
        </div>
        <div class="layui-form-mid layui-word-aux">分钟</div>
    </div>
</div>

<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">配件库存不足预警数量：</label>
        <div class="layui-input-inline repair" >
            <input type="text" name="repair[parts_warning]" value="<?php echo ($settings['repair']['parts_warning']); ?>" class="layui-input" lay-verify="parts_warning">
        </div>
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">生命支持类设备超过：</label>
        <div class="layui-input-inline repair" >
            <input type="text" name="repair[life_assets_remind]" value="<?php echo ($settings['repair']['life_assets_remind']); ?>" class="layui-input" lay-verify="life_assets_remind">
        </div>
        <div class="layui-form-mid layui-word-aux">分钟未接单的，重新提醒(不设置或设置为0则无需提醒)</div>
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">普通类设备超过：</label>
        <div class="layui-input-inline repair" >
            <input type="text" name="repair[normal_assets_remind]" value="<?php echo ($settings['repair']['normal_assets_remind']); ?>" class="layui-input" lay-verify="normal_assets_remind">
        </div>
        <div class="layui-form-mid layui-word-aux">分钟未接单的，重新提醒(不设置或设置为0则无需提醒)</div>
    </div>
</div>

<div class="content">
    <table class="layui-table t2">
        <tbody>
        <!--<tr>-->
        <!--<th>服务分类 :</th>-->
        <!--<td>-->
        <!--<div class="layui-input-block repair">-->
        <!--<input type="text" name="repair[repair_service]" placeholoder="" value="<?php echo (implode('|',$settings['repair']['repair_service']));?>" class="layui-input">-->
        <!--</div>-->
        <!--<span style="color: red;">(注意：请用 | 分隔，设置好后，请勿频繁改动！)</span>-->
        <!--</td>-->
        <!--</tr>-->
        <!--<tr>-->
        <!--<th>导出维修单信息项 :</th>-->
        <!--<td>-->
        <!--<div class="layui-form-item repair" >-->
        <!--<ul class="export">-->
        <!--<?php if(is_array($repairPrint)): $i = 0; $__LIST__ = $repairPrint;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>-->
        <!--<?php if(in_array($vo['field'],$settings['repair']['repair_print'])): ?>-->
        <!--<li><input type="checkbox" name="repair[repair_print][<?php echo ($vo["field"]); ?>]" lay-skin="primary" title="<?php echo ($vo["comment"]); ?>" checked="checked"/></li>-->
        <!--<?php else: ?>-->
        <!--<li><input type="checkbox" name="repair[repair_print][<?php echo ($vo["field"]); ?>]" lay-skin="primary" title="<?php echo ($vo["comment"]); ?>" /></li>-->
        <!--<?php endif; ?>-->
        <!--<?php endforeach; endif; else: echo "" ;endif; ?>-->
        <!--</ul>-->
        <!--</div>-->
        <!--</td>-->
        <!--</tr>-->
        </tbody>
    </table>
</div>


<script>
    $(function () {
        var haveWater = $('.repair input[name="repair[repair_tmp][style]"]:checked ').val();
        if (haveWater == '2') {
            $('.watermark').show();
        }
    });
    function showimg(url) {
        top.layer.open({
            type: 2,
            offset:'r',
            title: '预览模板',
            shade: [0.8, '#393D49'],
            shadeClose:true,
            anim:5,
            scrollbar:false,
            area: ['950px', '100%'],
            closeBtn: 1,
            content: [url]
        });
    }
</script><?php endif; ?>
                        <!--巡查管理-->
                        <?php if($settings_display["patrolSetting"] == 1 ): ?><a name="patrol"></a>
<blockquote class="layui-elem-quote module-blockquote">巡查保养管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2" style="width: 190px !important;">模块状态：</label>
    <div class="layui-input-block radio-margin-left" style="margin-left: 190px!important;">
        <input type="radio" name="patrol[patrol_open][is_open]" lay-filter="patrol_open"  value="1" title="开启" <?php if($settings['patrol']['patrol_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="patrol[patrol_open][is_open]" lay-filter="patrol_open"  value="0" title="关闭" <?php if($settings['patrol']['patrol_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">扫码签到保养设备：</label>
    <div class="layui-input-block repair radio-margin-left">
        <input type="radio" name="patrol[patrol_wx_set_situation]"  value="1" title="开启" <?php if($settings['patrol']['patrol_wx_set_situation'] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="patrol[patrol_wx_set_situation]"  value="0" title="关闭" <?php if($settings['patrol']['patrol_wx_set_situation'] != 1): ?>checked<?php endif; ?> >
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">保养报告标题：</label>
        <div class="layui-input-inline patrol" style="width: auto" >
            <input type="text" name="patrol[patrol_template][title]" value="<?php echo ($settings['patrol']['patrol_template'][title]); ?>" class="layui-input" style="width: 200px;">
        </div>
        <div class="layui-form-mid layui-word-aux">保养报告</div>
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">任务将到期提醒范围：</label>
        <div class="layui-input-inline patrol" style="width: auto" >
            <input type="text" name="patrol[patrol_soon_expire_day]" value="<?php echo ($settings['patrol']['patrol_soon_expire_day']); ?>" class="layui-input" style="width: 70px;" lay-verify="number">
        </div>
        <div class="layui-form-mid layui-word-aux">天</div>
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">任务发布提醒范围：</label>
        <div class="layui-input-inline patrol" style="width: auto" >
            <input type="text" name="patrol[patrol_reminding_day]" value="<?php echo ($settings['patrol']['patrol_reminding_day']); ?>" class="layui-input" style="width: 70px;" lay-verify="number">
        </div>
        <div class="layui-form-mid layui-word-aux">天</div>
    </div>
</div>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;float:left;">
    <label class="layui-form-label modulelabel-2" style="width: 190px !important;">价格区间：</label>
    <div class="layui-input-block patrol" style="margin-left: 190px;">
        <textarea class="layui-textarea" name="patrol[priceRange]" style="border: none;border-left: 1px solid #e6e6e6;"><?php echo ($priceRange); ?></textarea>
    </div>
</div>
<div class="layui-form-mid layui-word-aux" style="float: left;margin-left: 15px;">填写规则“区间值 最小值|最大值”，一行一个！例：0|50000</div>
<div class="clear"></div><?php endif; ?>
                        <!--质控管理-->
                        <?php if($settings_display["qualitiesSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">质控管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2" style="width: 190px !important;">模块状态：</label>
    <div class="layui-input-block radio-margin-left" style="margin-left: 190px!important;">
        <input type="radio" name="qualities[qualities_open][is_open]" lay-filter="qualities_open" value="1" title="开启" <?php if($settings['qualities']['qualities_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="qualities[qualities_open][is_open]" lay-filter="qualities_open" value="0" title="关闭" <?php if($settings['qualities']['qualities_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <label class="layui-form-label modulelabel-2" style="width: 190px !important;">计划将到期提醒范围：</label>
        <div class="layui-input-inline qualities" style="width: auto" >
            <input type="text" name="qualities[qualities_soon_expire_day]" value="<?php echo ($settings['qualities']['qualities_soon_expire_day']); ?>" class="layui-input" style="width: 70px;" lay-verify="number">
        </div>
        <div class="layui-form-mid layui-word-aux">天</div>
    </div>
</div><?php endif; ?>
                        <!--计量管理-->
                        <?php if($settings_display["meteringSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">计量管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="metering[metering_open][is_open]" lay-filter="metering_open" value="1" title="开启" <?php if($settings['metering']['metering_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="metering[metering_open][is_open]" lay-filter="metering_open" value="0" title="关闭" <?php if($settings['metering']['metering_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--不良事件管理-->
                        <?php if($settings_display["adverseSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">不良事件管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="adverse[adverse_open][is_open]" lay-filter="adverse_open" value="1" title="开启" <?php if($settings['adverse']['adverse_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="adverse[adverse_open][is_open]" lay-filter="adverse_open" value="0" title="关闭" <?php if($settings['adverse']['adverse_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--效益分析管理-->
                        <?php if($settings_display["benefitSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">效益分析模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="benefit[benefit_open][is_open]" lay-filter="benefit_open" value="1" title="开启" <?php if($settings['benefit']['benefit_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="benefit[benefit_open][is_open]" lay-filter="benefit_open" value="0" title="关闭" <?php if($settings['benefit']['benefit_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--统计分析管理-->
                        <?php if($settings_display["statisticsSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">统计分析模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="statistics[statistics_open][is_open]" lay-filter="statistics_open" value="1" title="开启" <?php if($settings['statistics']['statistics_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="statistics[statistics_open][is_open]" lay-filter="statistics_open" value="0" title="关闭" <?php if($settings['statistics']['statistics_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--决策分析管理-->
                        <?php if($settings_display["strategySetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">决策分析模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="strategy[strategy_open][is_open]" lay-filter="strategy_open" value="1" title="开启" <?php if($settings['strategy']['strategy_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="strategy[strategy_open][is_open]" lay-filter="strategy_open" value="0" title="关闭" <?php if($settings['strategy']['strategy_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--监控管理-->
                        <?php if($settings_display["monitorSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">监控管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="monitor[monitor_open][is_open]" lay-filter="monitor_open" value="1" title="开启" <?php if($settings['monitor']['monitor_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="monitor[monitor_open][is_open]" lay-filter="monitor_open" value="0" title="关闭" <?php if($settings['monitor']['monitor_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--档案管理-->
                        <?php if($settings_display["archivesSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">档案管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="archives[archives_open][is_open]" lay-filter="archives_open" value="1" title="开启" <?php if($settings['archives']['archives_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="archives[archives_open][is_open]" lay-filter="archives_open" value="0" title="关闭" <?php if($settings['archives']['archives_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--培训管理-->
                        <?php if($settings_display["trainSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">培训管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="train[train_open][is_open]" lay-filter="train_open" value="1" title="开启" <?php if($settings['train']['train_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="train[train_open][is_open]" lay-filter="train_open" value="0" title="关闭" <?php if($settings['train']['train_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--盘点管理-->
                        <?php if($settings_display["inventorySetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">盘点管理模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="inventory[inventory_open][is_open]" lay-filter="inventory_open" value="1" title="开启" <?php if($settings['inventory']['inventory_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="inventory[inventory_open][is_open]" lay-filter="inventory_open" value="0" title="关闭" <?php if($settings['inventory']['inventory_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--服务商管理-->
                        <?php if($settings_display["suppliersSetting"] == 1 ): ?><blockquote class="layui-elem-quote module-blockquote">厂商管理（内）模块配置</blockquote>
<div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
    <label class="layui-form-label modulelabel-2">模块状态：</label>
    <div class="layui-input-block radio-margin-left">
        <input type="radio" name="offlineSuppliers[offlineSuppliers_open][is_open]" lay-filter="offlineSuppliers_open" value="1" title="开启" <?php if($settings['offlineSuppliers']['offlineSuppliers_open'][is_open] == 1): ?>checked<?php endif; ?> >
        <input type="radio" name="offlineSuppliers[offlineSuppliers_open][is_open]" lay-filter="offlineSuppliers_open" value="0" title="关闭" <?php if($settings['offlineSuppliers']['offlineSuppliers_open'][is_open] != 1): ?>checked<?php endif; ?> >
    </div>
</div><?php endif; ?>
                        <!--微信公众号绑定验证-->
                        <?php  if(session('isSuper')==C('YES_STATUS')){?>
                        <blockquote class="layui-elem-quote module-blockquote">微信公众号</blockquote>
                        <div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
                            <label class="layui-form-label modulelabel-2">微信端启停：</label>
                            <div class="layui-input-block radio-margin-left">
                                <input type="radio" name="wx_setting[wx_setting_open][open]" lay-filter="wx_setting_open"  value="1" title="启用" <?php if($settings['wx_setting']['wx_setting_open'][open] == 1): ?>checked<?php endif; ?> >
                                <input type="radio" name="wx_setting[wx_setting_open][open]" lay-filter="wx_setting_open"  value="0" title="停用" <?php if($settings['wx_setting']['wx_setting_open'][open] != 1): ?>checked<?php endif; ?> >
                            </div>
                        </div>
                        <div class="layui-form-item" pane="" style="margin-bottom:25px!important; width: 580px;">
                            <label class="layui-form-label modulelabel-2">后台登陆绑定微信：</label>
                            <div class="layui-input-block wx radio-margin-left">
                                <input type="radio"  name="wx_setting[open_wx_login_binding][open]"  value="1" title="需要" <?php if($settings['wx_setting']['open_wx_login_binding'][open] == 1): ?>checked<?php endif; ?> >
                                <input type="radio" name="wx_setting[open_wx_login_binding][open]"  value="0" title="不需要" <?php if($settings['wx_setting']['open_wx_login_binding'][open] != 1): ?>checked<?php endif; ?> >
                            </div>
                        </div>
                        <?php } ?>
                        <div style="text-align: center">
                            <button class="layui-btn" lay-submit lay-filter="saveSetting" type="button" style="margin-top: 15px;"><i class="layui-icon" >&#xe609;</i> 保存</button>
                            <button type="reset" style="margin-top: 15px;" class="layui-btn layui-btn-primary"><i class="layui-icon">ဂ</i> 重置 </button>
                        </div>
                        <br/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/js/ajax.js?v=<?php echo mt_rand(1,54561);?>"></script>
<script>
    layui.use('basesetting/modulesetting/module', layui.factory('basesetting/modulesetting/module'));
</script>