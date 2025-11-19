<?php if (!defined('THINK_PATH')) exit();?><style>
    #homePage .layui-input, .layui-select, .layui-textarea {
        height: 30px !important;
    }
</style>
<div class="layui-fluid" id="homePage">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">待办事项</div>
                <div class="layui-card-body">
                    <div id="indexTask" class="layui-carousel layadmin-carousel layadmin-backlog">
                        <?php if(!empty($indexTask)): ?><div carousel-item>
                                <ul class="layui-row layui-col-space10">
                                    <?php if(is_array($indexTask)): $i = 0; $__LIST__ = array_slice($indexTask,0,6,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="layui-col-sm4 layui-col-md3 layui-col-lg2">
                                            <a lay-href="<?php echo ($vo['aLink']); ?>" class="layadmin-backlog-body">
                                                <h3><?php echo ($vo['name']); ?></h3>
                                                <p><cite style="color: <?php echo ($indexTask[$i]['color']); ?>;"><?php echo ($vo['num']); ?></cite></p>
                                            </a>
                                        </li>
                                        <?php unset($indexTask[$key]); endforeach; endif; else: echo "" ;endif; ?>
                                </ul>
                                <?php if($indexTask): ?><ul class="layui-row layui-col-space10">
                                        <?php if(is_array($indexTask)): $i = 0; $__LIST__ = array_slice($indexTask,0,6,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="layui-col-sm4 layui-col-md3 layui-col-lg2">
                                                <a lay-href="<?php echo ($vo['aLink']); ?>" class="layadmin-backlog-body">
                                                    <h3><?php echo ($vo['name']); ?></h3>
                                                    <p><cite style="color: <?php echo ($indexTask[$i]['color']); ?>;"><?php echo ($vo['num']); ?></cite></p>
                                                </a>
                                            </li>
                                            <?php unset($indexTask[$key]); endforeach; endif; else: echo "" ;endif; ?>
                                    </ul><?php endif; ?>
                                <?php if($indexTask): ?><ul class="layui-row layui-col-space10">
                                        <?php if(is_array($indexTask)): $i = 0; $__LIST__ = array_slice($indexTask,0,6,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="layui-col-sm4 layui-col-md3 layui-col-lg2">
                                                <a lay-href="<?php echo ($vo['aLink']); ?>" class="layadmin-backlog-body">
                                                    <h3><?php echo ($vo['name']); ?></h3>
                                                    <p><cite style="color: <?php echo ($indexTask[$i]['color']); ?>;"><?php echo ($vo['num']); ?></cite></p>
                                                </a>
                                            </li>
                                            <?php unset($indexTask[$key]); endforeach; endif; else: echo "" ;endif; ?>
                                    </ul><?php endif; ?>
                            </div>
                            <?php else: ?>
                            暂无待办事项<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-row layui-col-space13">
                <div class="layui-card">
                    <div class="layui-card-header">
                        全院设备概况
                        <span id="survey_setting">设置</span>
                    </div>
                    <div class="layui-card-body">
                        <div id="pic-desc">
                            <div class="pic-desc-card on_use">
                                <div id="repair_scrap_assets" class="index-pic-width"></div>
                            </div>

                            <?php if(in_array('insurance_assets',$show_survey_ids)): $insurance_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $insurance_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($insurance_assets); ?>">
                                <div id="insurance_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>

                            <?php if(in_array('special_assets',$show_survey_ids)): $special_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $special_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($special_assets); ?>">
                                <div id="special_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>

                            <?php if(in_array('lifesupport_assets',$show_survey_ids)): $lifesupport_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $lifesupport_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($lifesupport_assets); ?>">
                                <div id="lifesupport_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>

                            <?php if(in_array('big_assets',$show_survey_ids)): $big_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $big_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($big_assets); ?>">
                                <div id="big_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>

                            <?php if(in_array('firstaid_assets',$show_survey_ids)): $firstaid_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $firstaid_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($firstaid_assets); ?>">
                                <div id="firstaid_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>
                            <?php if(in_array('quality_assets',$show_survey_ids)): $quality_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $quality_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($quality_assets); ?>">
                                <div id="quality_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>
                            <?php if(in_array('metering_assets',$show_survey_ids)): $metering_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $metering_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($metering_assets); ?>">
                                <div id="metering_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>
                            <?php if(in_array('Inspection_assets',$show_survey_ids)): $Inspection_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $Inspection_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($Inspection_assets); ?>">
                                <div id="Inspection_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>
                            <?php if(in_array('maintain_assets',$show_survey_ids)): $maintain_assets = 'on_use'; ?>
                                <?php else: ?>
                                <?php $maintain_assets = 'not_use'; endif; ?>
                            <div class="pic-desc-card <?php echo ($maintain_assets); ?>">
                                <div id="maintain_assets" class="index-pic-width"></div>
                                <ul class="data-desc">

                                </ul>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space15" id="more_chart">
        <!--设备维修情况-->
        <div class="layui-col-xs6 on_use">
            <div class="layui-card">
                <div class="layui-card-header">
                    设备维修情况
                    <span id="target_chart_setting">设置</span>
                </div>
                <div class="layui-card-body chart-my-height">
                    <div class="target-chart-title">
                        <form class="layui-form" action="">
                            <div class="layui-btn-group chart-fl target_chart_assets_repair">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit lay-filter="target_chart_assets_repair" data-year="3">最近三年</button>
                                <button class="layui-btn layui-btn-sm layui-btn-primary" lay-submit lay-filter="target_chart_assets_repair" data-year="1">最近一年</button>
                                <button class="layui-btn layui-btn-sm layui-btn-primary" lay-submit lay-filter="target_chart_assets_repair" data-year="0.5">最近半年</button>
                            </div>
                            <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                <select name="target_count_type" lay-search="" lay-filter="target_chart_assets_repair">
                                    <option value="free" selected>维修费用</option>
                                    <option value="times">维修次数</option>
                                    <option value="hours">费用工时</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="target-chart-pic">
                        <div id="target_chart_assets_repair" class="div-chart-show-workjob"></div>
                    </div>
                </div>
            </div>
        </div>

        <!--科室维修费用分析-->
        <?php if(in_array('target_chart_depart_repair',$sys_showids)): if(in_array('target_chart_depart_repair',$show_ids)): $target_chart_depart_repair = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_depart_repair = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_depart_repair <?php echo ($target_chart_depart_repair); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">科室维修费用分析</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-btn-group chart-fl">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit lay-filter="target_chart_depart_repair" data-year="3">最近三年</button>
                                    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-submit lay-filter="target_chart_depart_repair" data-year="1">最近一年</button>
                                    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-submit lay-filter="target_chart_depart_repair" data-year="0.5">最近半年</button>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_depart_repair" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--设备采购支出情况-->
        <?php if(in_array('target_chart_assets_purchases',$sys_showids)): if(in_array('target_chart_assets_purchases',$show_ids)): $target_chart_assets_purchases = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_purchases = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_purchases <?php echo ($target_chart_assets_purchases); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">设备采购支出情况</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_purchases" lay-search="" lay-filter="target_chart_assets_purchases">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                    <select name="target_show_type_purchases" lay-search="" lay-filter="target_chart_assets_purchases">
                                        <option value="trend" selected>支出趋势</option>
                                        <option value="free">费用分析</option>
                                        <option value="nums">数量分析</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_assets_purchases" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--设备效益分析-->
        <?php if(in_array('target_chart_assets_benefit',$sys_showids)): if(in_array('target_chart_assets_benefit',$show_ids)): $target_chart_assets_benefit = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_benefit = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_benefit <?php echo ($target_chart_assets_benefit); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">设备效益分析</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_benefit" lay-search="" lay-filter="target_chart_assets_benefit">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                    <select name="target_show_type_benefit" lay-search="" lay-filter="target_chart_assets_benefit">
                                        <option value="trend" selected>收支趋势</option>
                                        <option value="income">收入分析</option>
                                        <option value="expenditure">支出分析</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div class="target_chart_no_data">暂无相关数据</div>
                            <div id="target_chart_assets_benefit" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--设备保养情况-->
        <?php if(in_array('target_chart_assets_patrol',$sys_showids)): if(in_array('target_chart_assets_patrol',$show_ids)): $target_chart_assets_patrol = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_patrol = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_patrol <?php echo ($target_chart_assets_patrol); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">设备保养情况</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_patrol" lay-search="" lay-filter="target_chart_assets_patrol">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                    <select name="target_show_type_patrol" lay-search="" lay-filter="target_chart_assets_patrol">
                                        <option value="trend" selected>保养次数</option>
                                        <option value="abnormal">保养统计</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_assets_patrol" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--设备增加情况-->
        <?php if(in_array('target_chart_assets_add',$sys_showids)): if(in_array('target_chart_assets_add',$show_ids)): $target_chart_assets_add = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_add = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_add <?php echo ($target_chart_assets_add); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">设备增加情况</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_add" lay-search="" lay-filter="target_chart_assets_add">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                    <select name="target_show_type_add" lay-search="" lay-filter="target_chart_assets_add">
                                        <option value="trend" selected>增加趋势</option>
                                        <option value="depart">增加分析</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_assets_add" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--设备转移情况-->
        <?php if(in_array('target_chart_assets_move',$sys_showids)): if(in_array('target_chart_assets_move',$show_ids)): $target_chart_assets_move = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_move = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_move <?php echo ($target_chart_assets_move); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">设备转移情况</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_move" lay-search="" lay-filter="target_chart_assets_move">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <!--<div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">-->
                                <!--<select name="target_show_type_move" lay-search="" lay-filter="target_chart_assets_move" >-->
                                <!--<option value="trend" selected>转移趋势</option>-->
                                <!--&lt;!&ndash;<option value="depart">转移分析</option>&ndash;&gt;-->
                                <!--</select>-->
                                <!--</div>-->
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_assets_move" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--设备报废情况-->
        <?php if(in_array('target_chart_assets_scrap',$sys_showids)): if(in_array('target_chart_assets_scrap',$show_ids)): $target_chart_assets_scrap = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_scrap = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_scrap <?php echo ($target_chart_assets_scrap); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">设备报废情况</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_scrap" lay-search="" lay-filter="target_chart_assets_scrap">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                    <select name="target_show_type_scrap" lay-search="" lay-filter="target_chart_assets_scrap">
                                        <option value="trend" selected>报废趋势</option>
                                        <option value="depart">报废分析</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_assets_scrap" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

        <!--不良事件情况-->
        <?php if(in_array('target_chart_assets_adverse',$sys_showids)): if(in_array('target_chart_assets_adverse',$show_ids)): $target_chart_assets_adverse = 'on_use'; ?>
                <?php else: ?>
                <?php $target_chart_assets_adverse = 'not_use'; endif; ?>
            <div class="layui-col-xs6 target_chart_assets_adverse <?php echo ($target_chart_assets_adverse); ?>">
                <div class="layui-card">
                    <div class="layui-card-header">不良事件情况</div>
                    <div class="layui-card-body chart-my-height">
                        <div class="target-chart-title">
                            <form class="layui-form" action="">
                                <div class="layui-input-inline chart-fl" style="width: 90px;height: 20px !important;">
                                    <select name="change_year_adverse" lay-search="" lay-filter="target_chart_assets_adverse">
                                        <?php if(is_array($years)): $i = 0; $__LIST__ = $years;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == 0): ?><option value="<?php echo ($vo); ?>" selected><?php echo ($vo); ?>年</option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo); ?>"><?php echo ($vo); ?>年</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline chart-fr" style="width: 100px;height: 20px !important;">
                                    <select name="target_show_type_adverse" lay-search="" lay-filter="target_chart_assets_adverse">
                                        <option value="trend" selected>不良趋势</option>
                                        <option value="depart">不良分析</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="target-chart-pic">
                            <div id="target_chart_assets_adverse" class="div-chart-show-workjob"></div>
                        </div>
                    </div>
                </div>
            </div><?php endif; ?>

    </div>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md8">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-header">最近一周的数据概览</div>
                        <div class="layui-card-body">
                            <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade" lay-filter="LAY-index-dataview">
                                <div carousel-item id="LAY-index-dataview">
                                    <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                                    <div></div>
                                    <!--<div></div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-col-md4">
            <div class="layui-card">
                <div class="layui-card-header">公告栏目</div>
                <div class="layui-card-body layui-text">
                    <table class="layui-table">
                        <colgroup>
                            <col width="65">
                            <col width="35%">
                        </colgroup>
                        <tbody>
                        <tr>
                            <td style="text-align: center">最新公告</td>
                            <td style="text-align: center">发布日期</td>
                        </tr>
                        <?php if(empty($noticeinfo)): ?><tr>
                                <td colspan="2" class="td-align-center">暂无最新公告</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($noticeinfo)): $i = 0; $__LIST__ = $noticeinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td>
                                        <p class="noticeTitle" style="width: 103%;">
                                            <a href="javascript:void(0)" title="<?php echo ($vo["title"]); ?>" class="showNotice" data-id="<?php echo ($vo["notid"]); ?>" data-url="/A/Notice/getNoticeList"><?php echo ($vo["title"]); ?></a>
                                        </p>
                                    </td>
                                    <td style="text-align: center"><?php echo ($vo["date"]); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        <tr>
                            <td colspan="2">
                                <div class="layui-btn-container">
                                    <?php if($menuData = get_menu('BaseSetting','Notice','addNotice')):?>
                                    <button class="layui-btn" id="addnoticeIndex" data-url="<?php echo ($menuData['actionurl']); ?>">
                                        <i class="layui-icon">&#xe654;</i><?php echo ($menuData['actionname']); ?>
                                    </button>
                                    <?php endif?>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="layui-col-md4">
            <div class="layui-card">
                <div class="layui-card-header">效果报告（当月）</div>
                <div class="layui-card-body layadmin-takerates">
                    <div class="layui-progress" lay-showPercent="yes">
                        <h3>报修设备修复率（上月修复率 <?php echo ($ratePre); ?>% <span class="layui-edge layui-edge-<?php echo ($arrow); ?>" lay-tips="提高" lay-offset="-15"></span>）
                        </h3>
                        <div class="layui-progress-bar" lay-percent="<?php echo ($rate); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use('login/index/target', layui.factory('login/index/target'));
</script>
<script>
    /*后台不操作15min退出 S*/
    var oldTime = new Date().getTime();
    var newTime = new Date().getTime();
    var outTime = 30 * 120 * 1000; //设置超时时间： 15分钟

    $(function () {
        /* 鼠标移动事件 */
        $(document).mouseover(function () {
            oldTime = new Date().getTime(); //鼠标移入重置停留的时间
        });
    });
    /* 定时器  判断每5秒是否长时间未进行页面操作 */
    let ab = window.setInterval(checkTime, 5000);

    function checkTime() {
        newTime = new Date().getTime(); //更新未进行操作的当前时间
        if (newTime - oldTime > outTime) { //判断是否超时不操作
            clearInterval(ab)
            $.ajax({
                type: "POST",
                dataType: "json",
                url: admin_name + '/Login/logout',
                //成功返回之后调用的函数
                success: function (data) {
                    if (data.status == 1) {
                        layer.open({
                            closeBtn:0
                            ,btnAlign: 'c'
                            ,time:0
                            ,content: '60分钟未操作，已自动退出'
                            ,end:function (){
                                window.location.href = admin_name + "/Login/login";
                            }
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 2});
                }
            });
        }
    }

    /*后台不操作15min退出 E*/

    var xAxisData = <?php echo ($xAxisData); ?>;
    var seriesDdata = <?php echo ($seriesDdata); ?>;
    var seriesDdata_patrol = <?php echo ($seriesDdata_patrol); ?>;
    var current_year = "<?php echo ($current_year); ?>";

    //定时更新数据

    setInterval(function () {
        //return false;
//        $.ajax({
//            timeout: 5000,
//            type: "POST",
//            url: admin_name+'/System/updataTask.html',
//            dataType: "json",
//            success: function (data) {
//
//
//
//
//
//            },
//            error: function () {
//                layer.msg("网络访问失败",{icon : 2},1000);
//            },
//        });


    }, 60000);

    function showAssetsDetail(e) {
        var url = admin_name + '/Lookup/showAssets.html';
        var assid = $(e).attr('data-id');
        var assets = $(e).attr('data-name');
        top.layer.open({
            type: 2,
            title: '【' + assets + '】设备详情信息',
            area: ['1000px', '100%'],
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            scrollbar: false,
            closeBtn: 1,
            content: [url + '?assid=' + assid]
        });
    }

    var loginTimes = localStorage.getItem('loginTimes');

    if (loginTimes != null) {
        var buttonObj = $("#LAY_app").find(".layui-layout-right .systemMessage a");
        buttonObj.click();
        localStorage.removeItem('loginTimes');
    }

</script>
<script>
    //加载 controller 目录下的对应模块
    /*

     小贴士：
     这里 console 模块对应 的 console.js 并不会重复加载，
     然而该页面的视图则是重新插入到容器，那如何保证能重新来控制视图？有两种方式：
     1): 借助 layui.factory 方法获取 console 模块的工厂（回调函数）给 layui.use
     2): 直接在 layui.use 方法的回调中书写业务代码，即:
     layui.use('console', function(){
     //同 console.js 中的 layui.define 回调中的代码
     });

     这里我们采用的是方式1。其它很多视图中采用的其实都是方式2，因为更简单些，也减少了一个请求数。

     */
    layui.use('console', layui.factory('console'));
</script>