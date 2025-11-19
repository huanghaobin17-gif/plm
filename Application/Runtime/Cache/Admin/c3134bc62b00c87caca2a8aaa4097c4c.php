<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Qualities','Quality','getDetectingList')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<style>
    #LAY-Qualities-Quality-getDetectingList .no-padding-td {
        padding: 0px !important;
    }

    #LAY-Qualities-Quality-getDetectingList .no-padding-td .layui-input {
        border: none;
        height: 48px;
        line-height: 48px;
    }
    #scanfile{
        display: none;
    }
</style>
<script>
    var getDetectingList = "<?php echo ($getDetectingList); ?>";
</script>
<div class="layui-fluid" id="LAY-Qualities-Quality-getDetectingList">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe62d;</i> 检测仪器（标准器、模拟器）管理</div>
                <div class="layui-card-body">
                    <table class="layui-table" lay-size="sm">
                        <colgroup>
                            <col width="50">
                            <col>
                            <col>
                            <col>
                            <col width="100">
                            <col>
                            <col>
                            <col width="100">
                            <col width="100">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="th-align-center">序号</td>
                            <td class="th-align-center">仪器名称</td>
                            <td class="th-align-center">规格 / 型号</td>
                            <td class="th-align-center">产品序列号</td>
                            <td class="th-align-center">计量检定日期</td>
                            <td class="th-align-center">检定单位</td>
                            <td class="th-align-center">计量编号</td>
                            <td class="th-align-center">检定报告</td>
                            <td class="th-align-center">操作</td>
                        </tr>
                        </thead>
                        <tbody class="materiel-list">
                        <?php if(empty($data)): ?><tr class="tech-empty" id="arc-empty-materiel">
                                <td colspan="9" style="text-align: center;">暂无相关数据</td>
                            </tr>
                            <?php else: ?>
                            <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id="row_<?php echo ($key+1); ?>">
                                    <td class="td-align-center xuhao"><?php echo ($key+1); ?></td>
                                    <td class="td-align-center name"><?php echo ($vo["instrument"]); ?></td>
                                    <td class="td-align-center model"><?php echo ($vo["model"]); ?></td>
                                    <td class="td-align-center serialnum"><?php echo ($vo["productid"]); ?></td>
                                    <td class="td-align-center date"><?php echo ($vo["metering_date"]); ?></td>
                                    <td class="td-align-center company"><?php echo ($vo["metering_place"]); ?></td>
                                    <td class="td-align-center num"><?php echo ($vo["metering_num"]); ?></td>
                                    <td class="td-align-center report">
                                        <?php echo ($vo["html"]); ?>
                                    </td>
                                    <td class="th-align-center">
                                        <div class="layui-btn-group">
                                            <button class="layui-btn layui-btn-xs layui-btn-warm" lay-submit="" lay-filter="editInstruments" title="编辑" data-pic="<?php echo ($vo["metering_report"]); ?>" data-row="<?php echo ($key+1); ?>" data-qiid="<?php echo ($vo["qiid"]); ?>">
                                                <i class="layui-icon">&#xe642;</i>
                                            </button>
                                            <button class="layui-btn layui-btn-xs layui-btn-danger" lay-submit="" lay-filter="delInstruments" title="删除" data-row="<?php echo ($key+1); ?>" data-qiid="<?php echo ($vo["qiid"]); ?>">
                                                <i class="layui-icon">&#xe640;</i>
                                            </button>
                                        </div>
                                    </td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        <tr class="count">
                            <form action="" class="layui-form">
                                <input type="hidden" name="report" value=""/>
                                <td class="layui-bg-gray" style="text-align: center">新增</td>
                                <td class="no-padding-td">
                                    <input type="text" name="name" placeholder="仪器名称" class="layui-input">
                                </td>
                                <td class="no-padding-td">
                                    <input type="text" name="model" placeholder="规格/型号" class="layui-input">
                                </td>
                                <td class="no-padding-td">
                                    <input type="text" name="serialnum" placeholder="产品序列号" class="layui-input">
                                </td>
                                <td class="no-padding-td">
                                    <input type="text" name="date" id="checkdate" placeholder="计量检定日期" class="layui-input">
                                </td>
                                <td class="no-padding-td">
                                    <input type="text" name="company" placeholder="检定单位" class="layui-input">
                                </td>
                                <td class="no-padding-td">
                                    <input type="text" name="num" placeholder="计量编号" class="layui-input">
                                </td>
                                <td class="no-padding-td" id="uploadfile" style="text-align: center;">
                                    <div class="layui-btn-group">
                                        <button type="button" class="layui-btn layui-btn-xs" id="scanfile" data-url="">
                                            预览
                                        </button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-warm" id="file_url" name="file_url">
                                            上传文件
                                        </button>
                                    </div>
                                    <!--点击就选择文件-->
                                </td>
                                <td class="th-align-center">
                                    <div class="layui-btn-group">
                                        <button class="layui-btn layui-btn-xs addMateriel" lay-submit="" lay-filter="addInstruments" title="确定">
                                            保存
                                        </button>
                                        <button class="layui-btn layui-btn-xs layui-btn-primary" id="reset" >
                                            重置
                                        </button>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use('qualities/quality/getDetectingList', layui.factory('qualities/quality/getDetectingList'));
</script>