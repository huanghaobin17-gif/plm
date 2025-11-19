layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'tipsType', 'suggest'], function () {
        var layer = layui.layer
            , tipsType = layui.tipsType
            , table = layui.table
            , suggest = layui.suggest
            , form = layui.form;

        //初始化
        form.render();
        tipsType.choose();
        //初始化搜索建议插件
        suggest.search();

        var thisbody=$('#LAY-Assets-Subsidiary-subsidiaryAllot');


        //新增操作
        form.on('submit(addApplySubsidiary)', function (data) {
            var params = data.field;
            params.main_assid = parseInt(thisbody.find('input[name="main_assets"]').attr('data-id'));
            submit($, params, subsidiaryAllotUrl);
            return false;
        });


        //选择科室
        var old_departid=0;
        form.on('select(departid)', function (data) {
            var departid = parseInt(data.value);
            if (data.value) {
                if (departid !== old_departid) {
                    initsuggestMainAssets(departid);
                    old_departid = departid;
                }
            }else{
                var main_assets_div = thisbody.find('#main_assets_div');
                var html = '<div class="input-group">';
                html += '<input type="text" class="form-control bsSuggest" id="allotAssetsName" placeholder="请先选择科室" name="main_assets">';
                html += '<div class="input-group-btn">';
                html += '<ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">';
                html += '</ul>';
                html += '</div>';
                main_assets_div.html('');
                main_assets_div.html(html);
            }
            intMainAssets()
        });

        function intMainAssets() {
            thisbody.find("input[name='managedepart']").val('');
            thisbody.find("input[name='address']").val('');
            thisbody.find("input[name='assetsrespon']").val('');
            form.render();
            old_departid=0;
            old_main_assid=0;
        }


        var old_main_assid=0;
        function initsuggestMainAssets(departid) {
            var main_assets_div = thisbody.find('#main_assets_div');
            var html = '<div class="input-group">';
            html += '<input type="text" class="form-control bsSuggest" id="allotAssetsName" placeholder="请选择主设备" name="main_assets">';
            html += '<div class="input-group-btn">';
            html += '<ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">';
            html += '</ul>';
            html += '</div>';
            main_assets_div.html('');
            main_assets_div.html(html);
            thisbody.find("#allotAssetsName").bsSuggest({
                url: admin_name+"/Public/getAllAssetsSearch?departid="+departid+"&type=subsidiary",
                effectiveFieldsAlias: {assid: '设备id12', assnum: "设备编号", assets: "设备名称", pinyin: "拼音", assorignum: "设备原编号"},
                listStyle: {
                    "max-height": "375px", "max-width": "550px",
                    "overflow": "auto", "width": "550px", "text-align": "center"
                },
                ignorecase: false,
                showHeader: true,
                listAlign: 'right',
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                idField: "assid",
                keyField: "assets",
                clearable: false
            }).on('onDataRequestSuccess', function (e, result) {
                if (result.value.length === 0) {
                    //未设置科室
                    var department=$('select[name="departid"]').find('option[value="' + departid + '"]').html();
                    layer.msg('科室 '+department+' 未添加设备，请先补充设备', {icon: 2, time: 3000});
                }
            }).on('onSetSelectValue', function (e, keyword, data) {
                var main_assid=parseInt(thisbody.find('input[name="main_assets"]').attr('data-id'));
                if (main_assid !== old_main_assid) {
                    var params = {};
                    params.assid = main_assid;
                    params.action = 'getAssetsDetail';
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: subsidiaryAllotUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                thisbody.find("input[name='managedepart']").val(data.result.managedepart);
                                thisbody.find("input[name='address']").val(data.result.address);
                                thisbody.find("input[name='assetsrespon']").val(data.result.assetsrespon);
                                form.render();
                                old_main_assid = main_assid;
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
                return false;
            }).on('onUnsetSelectValue', function () {
                thisbody.find("input[name='managedepart']").val('');
                thisbody.find("input[name='address']").val('');
                thisbody.find("input[name='assetsrespon']").val('');
                old_main_assid=0;
                layer.msg('请点击选择所属设备', {icon: 2, time: 3000});
            });
        }

    });
    exports('controller/assets/subsidiary/subsidiaryAllot', {});
});
