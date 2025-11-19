layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form, $ = layui.jquery;
        form.render();
        var dag = window.parent;
        dag.fun = function () {
            var params = {};
            params.action = 'show_setting';
            if ($('input[name="target_chart_depart_repair"]:checked').val() == 1) {
                params.target_chart_depart_repair = 1;
            }
            if ($('input[name="target_chart_assets_add"]:checked').val() == 1) {
                params.target_chart_assets_add = 1;
            }
            if ($('input[name="target_chart_assets_scrap"]:checked').val() == 1) {
                params.target_chart_assets_scrap = 1;
            }
            if ($('input[name="target_chart_assets_purchases"]:checked').val() == 1) {
                params.target_chart_assets_purchases = 1;
            }
            if ($('input[name="target_chart_assets_benefit"]:checked').val() == 1) {
                params.target_chart_assets_benefit = 1;
            }
            if ($('input[name="target_chart_assets_adverse"]:checked').val() == 1) {
                params.target_chart_assets_adverse = 1;
            }
            if ($('input[name="target_chart_assets_move"]:checked').val() == 1) {
                params.target_chart_assets_move = 1;
            }
            if ($('input[name="target_chart_assets_patrol"]:checked').val() == 1) {
                params.target_chart_assets_patrol = 1;
            }
            return params;
        };

        dag.fun_survey = function () {
            var params = {};
            params.action = 'survey_setting';
            if ($('input[name="insurance_assets"]:checked').val() == 1) {
                params.insurance_assets = 1;
            }
            if ($('input[name="special_assets"]:checked').val() == 1) {
                params.special_assets = 1;
            }
            if ($('input[name="lifesupport_assets"]:checked').val() == 1) {
                params.lifesupport_assets = 1;
            }
            if ($('input[name="big_assets"]:checked').val() == 1) {
                params.big_assets = 1;
            }
            if ($('input[name="firstaid_assets"]:checked').val() == 1) {
                params.firstaid_assets = 1;
            }
            if ($('input[name="quality_assets"]:checked').val() == 1) {
                params.quality_assets = 1;
            }
            if ($('input[name="metering_assets"]:checked').val() == 1) {
                params.metering_assets = 1;
            }
            if ($('input[name="Inspection_assets"]:checked').val() == 1) {
                params.Inspection_assets = 1;
            }
            if ($('input[name="maintain_assets"]:checked').val() == 1) {
                params.maintain_assets = 1;
            }
            params.chart_type = $("input[name='chart_type']:checked").val();
            return params;
        };

    });
    exports('controller/login/index/show_setting', {});
});

