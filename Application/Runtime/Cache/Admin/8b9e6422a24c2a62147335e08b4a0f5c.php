<?php if (!defined('THINK_PATH')) exit(); if($menuData = get_menu_name('Statistics','StatisPurchases','purAnalysis')):?>
<title><?php echo ($menuData['actionname']); ?></title>
<?php endif?>
<script>
    //解决ie placeholder兼容性
    $(function(){ $('input, textarea').placeholder(); });
    var purAnalysis = "<?php echo ($purAnalysis); ?>";
    var year = "<?php echo ($year); ?>";
</script>
<div class="layui-fluid" id="LAY-Statistics-StatisPurchases-repairAnalysis">
    <div class="layui-row">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 查询</div>
                <div class="layui-card-body">
                    <form class="layui-form" lay-filter="component-form-group">
                        <div class="layui-form-item spacingBalance">
                            <div class="layui-inline">
                                <label class="layui-form-label">统计年份：</label>
                                <div class="layui-input-inline">
                                    <input name="year" value="<?php echo ($year); ?>" readonly type="text" id="pur_year" placeholder="请选择统计年份"  class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline" style="margin-left: 10px;">
                                <div class="fl">
                                    <button class="layui-btn" type="button" lay-submit="" lay-filter="purAnalysisSearch" id="purAnalysisSearch">
                                        <i class="layui-icon">&#xe615;</i> 搜 索
                                    </button>
                                    <button type="reset" lay-submit="" lay-filter="reset" class="layui-btn layui-btn-primary">重置</button>
                                </div>
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
                <div class="layui-card-header"><i class="layui-icon">&#xe615;</i> 图示</div>
                <div class="layui-row" style="padding-right: 15px;">
                    <div class="layui-col-md4 tb-padding">
                        <div class="grid-demo grid-demo-bg1">
                            <div class="div-chart-show">
                                <div class="div-chart-show-title">科室采购设备数量</div>
                                <div class="div-chart-show-pic">
                                    <div id="departAssetsNums" class="div-chart-show-pic-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-md4 tb-padding">
                        <div class="grid-demo">
                            <div class="div-chart-show">
                                <div class="div-chart-show-title">科室采购设备费用</div>
                                <div class="div-chart-show-pic">
                                    <div id="departAssetsFee" class="div-chart-show-pic-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-md4 tb-padding">
                        <div class="grid-demo">
                            <div class="div-chart-show">
                                <div class="div-chart-show-title">设备购置类型</div>
                                <div class="div-chart-show-pic">
                                    <div id="assetsBuyType" class="div-chart-show-pic-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-tab layui-tab-brief"  lay-filter="docDemoTabBrief1">
                    <ul class="layui-tab-title">
                        <li class="layui-this">科室采购设备数量</li>
                        <li>科室采购设备费用</li>
                        <li>设备购置类型</li>
                    </ul>
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <table id="departAssetsNumsLists" lay-filter="departAssetsNumsData"></table>
                        </div>
                        <div class="layui-tab-item">
                            <table id="departAssetsFeeLists" lay-filter="departAssetsFeeData"></table>
                        </div>
                        <div class="layui-tab-item">
                            <table id="assetsBuyTypeLists" lay-filter="assetsBuyTypeData"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use('statistics/statisPurchases/purAnalysis', layui.factory('statistics/statisPurchases/purAnalysis'));
</script>