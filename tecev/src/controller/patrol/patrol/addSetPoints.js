layui.define(function(exports){
    layui.use(['layer', 'form','suggest'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,suggest = layui.suggest;

        //初始化搜索建议插件
        suggest.search();

        var thisbody=$('#LAY-Patrol-Patrol-addSetPoints');

        thisbody.find("#addSetPointsTypeName").bsSuggest({
            url: admin_name+'/Public/getAllTemplateType',
            effectiveFields: ["name"],
            searchFields: [ "name"],
            effectiveFieldsAlias: {name: "类型名称"},
            listStyle: {
                "max-height": "375px", "max-width": "480px",
                "overflow": "auto", "width": "480px", "text-align": "center"
            },
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            keyField: "name",
            clearable: false
        }).on('onDataRequestSuccess', function (e, result) {
        }).on('onSetSelectValue', function (e, keyword, data) {
            // Cookies.set('ppid', data.ppid, 5000);
            var div = thisbody.find('#addSetPointsNameBlock');
            var html='<div class="input-group" style="width: 100%">';
            html += '<input type="text" class="form-control bsSuggest" id="addSetPointsName" placeholder="可输入搜索并自动检索" name="name" lay-verify="detail" value="">';
            html += '<div class="input-group-btn">';
            html += '<ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">';
            html += '</ul>';
            html += '</div>';
            div.html('');
            div.html(html);
            thisbody.find("#addSetPointsName").bsSuggest({
                url: admin_name+'/Public/getTemplatePoints?tpid=' + $('input[name="tpid"]').val() + '&level=' + $('input[name="level"]').val()+'&ppid='+data.ppid,
                effectiveFieldsAlias: {name: "明细名称"},
                listStyle: {
                    "max-height": "375px", "max-width": "480px",
                    "overflow": "auto", "width": "480px", "text-align": "center"
                },
                ignorecase: false,
                showHeader: true,
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                keyField: "name",
                clearable: false
            });
        }).on('onUnsetSelectValue', function () {
            var div = thisbody.find('#addSetPointsNameBlock');
            var html='<input type="text" class="layui-input" name="name" value="">';
            div.html(html);
        });
        //监听提交
        form.verify({
            typeName: function (value) {
                if (value) {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '类型名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '类型名称不能全为数字';
                    }
                } else {
                    return '类型名称不能为空';
                }
            },
            detail: function (value) {
                if (value) {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '明细名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '明细名称不能全为数字';
                    }
                } else {
                    return '明细名称不能为空';
                }
            }
        });

        form.on('submit(Submit)', function (data) {
            var level = thisbody.find("input[name='level']").val();
            var params = data.field;
            params.action=thisbody.find("input[name='action']").val();
            params.url=thisbody.find("input[name='url']").val();
            switch (params.type) {
                case 'Synchro':
                    params.RC = params.XC = params.PM = true;
                    break;
                case 'notSynchro':
                    if (parseInt(level) === 2) {
                        params.XC = params.PM = true;
                    } else {
                        params.PM = true;
                    }
                    break;
                case 'SynchroXC':
                    params.XC = params.PM = true;
                    break;
                default :
                    params.RC = params.XC = params.PM = true;
                    break;
            }
            addPoints($, params);
            return false;
        });
    });

    function addPoints($, params) {
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: params.url,
            data: params,
            dataType: "json",
            success: function (data) {
                if (data) {
                    if (data.status === 1) {
                        var DATA = JSON.stringify(data.result);
                        localStorage.setItem('Points', DATA);
                        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                        layer.msg(data.msg, {
                            icon: 1,
                            time: 2000
                        }, function () {
                            parent.layer.close(index); //再执行关闭
                        });
                        return false;
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                } else {
                    layer.msg("数据异常！", {icon: 2}, 1000);
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2}, 1000);
            }
        });
    }

    exports('controller/patrol/patrol/addSetPoints', {});
});
