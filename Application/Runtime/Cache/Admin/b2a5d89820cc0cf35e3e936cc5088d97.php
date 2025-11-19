<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Patrol','PatModSetting','patrolModuleSetting')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<div class="layui-fluid" id="LAY-Patrol-PatrolSetting-patrolModuleSetting" >
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane"  action="">
                        <input type="hidden" name="action" value="<?php echo ($url); ?>"/>
                        <a name="patrol"></a>
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
<div class="clear"></div>

                        <div style="text-align: center">
                            <button class="layui-btn" lay-submit lay-filter="saveSetting" type="button" style="margin-top: 15px;"><i class="layui-icon" >&#xe609;</i> 保存</button>
                            <button type="reset" style="margin-top: 15px;" class="layui-btn layui-btn-primary"><i class="layui-icon">ဂ</i> 重置 </button>
                        </div>
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