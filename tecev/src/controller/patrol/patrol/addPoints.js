layui.define(function(exports){
    $(document).ready(function () {
        //初始化统计已选择项
        $.each($('.layui-colla-item'), function (j, val) {
            var html = '已选择' + ($(val).find('.choose').find('input:checked').length) + '项';
            $(val).find('.many').html(html);
        });
    });

    layui.use('element', function () {
        var element = layui.element;
    });

    function getRadioHtml(id, result) {
        var html = '', name = '';
        for (i = 1; i <= 4; i++) {
            switch (i) {
                case 1:
                    name = '合格';
                    break;
                case 2:
                    name = '修复';
                    break;
                case 3:
                    name = '可用';
                    break;
                case 4:
                    name = '待修';
                    break;
            }
            var check = '';
            if (name == result) {
                check = 'checked';
            }

            html += '<input ' + check + ' type="radio" title="' + name + '" name="result[' + id + ']" class="result" value="' + name + '" lay-filter="result">' +
                '<div class="layui-unselect layui-form-radio"><i class="layui-anim layui-icon"></i><span>' + name + '</span></div>';
        }
        return html;
    }

    layui.use(['layer', 'form','suggest'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,suggest = layui.suggest;

        //初始化搜索建议插件
        suggest.search();

        //统计已选择项
        form.on('checkbox(Choose)', function (data) {
            var html = '已选择' + ($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length) + '项';
            $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
            form.render('checkbox');
        });

        //监听提交
        form.on('submit(next)', function (data) {
            var level = $("input[name='level']").val();
            var params = data.field;
            var num = "";
            var inputArr = $("input[name='num']:checked");
            inputArr.each(function () {
                if (num == '') {
                    num = $(this).val();
                } else {
                    num += "," + $(this).val();
                }
            });
            if (inputArr.length == 0) {
                layer.msg("请至少要补充一个类型明细", {icon: 2}, 1000);
                return false;
            } else {
                if (level == 3) {
                    //预防性维护
                    layer.confirm('', {
                        title: '是否同步',
                        area: ['435px', ''],
                        btn: ['不同步', '同步巡查保养与日常保养', '同步巡查保养']
                        , btn3: function () {
                            params.XC = params.PM = num;
                            addPoints($, params, params.action);
                            return false;
                        }
                    }, function () {
                        params.PM = num;
                        addPoints($, params, params.action);
                    }, function () {
                        params.RC = params.XC = params.PM = num;
                        addPoints($, params, params.action);
                        return false;
                    });
                } else if (level == 2) {
                    //巡查保养
                    layer.confirm('', {
                        title: '是否同步',
                        btn: ['不同步', '同步日常保养']
                        , btn2: function () {
                            params.RC = params.XC = params.PM = num;
                            addPoints($, params, params.action);
                            return false;
                        }
                    }, function () {
                        params.XC = params.PM = num;
                        addPoints($, params, params.action);
                    });
                } else {
                    //日常保养
                    params.RC = params.XC = params.PM = num;
                    addPoints($, params, params.action);
                }
                return false;
            }
        });
    });
    function getTableHtml(val) {
        return '<div class="layui-colla-item"><h2 class="layui-colla-title">' + val.parentName + '<i class="layui-icon layui-colla-icon"></i></h2>' +
            '<div class="layui-colla-content layui-show"> <table class="layui-table tablesorter alltable"> ' +
            '<thead><tr> <th style="width:12%" class="header">编号</th> <th style="width: 20%" class="header">明细名称</th> <th>保养结果</th> ' +
            '<th style="width: 40%;text-align: center;">异常处理详情</th> </tr> </thead><tbody id="parentid_' + val.parentid + '"></tbody></table></div></div>';
    }
    function getTextareaHtml(val) {
        var className = '';
        if (val.result != '合格') {
            className = 'red_border';
        }
        return '<textarea class="abnormal_remark ' + className + '" data-ppid="' + val.ppid + '" style="width: 100%;border: 1px solid #dddddd"></textarea>';
    }


    function addPoints($, params, url) {
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            success: function (data) {
                if (data) {
                    if (data.status == 1) {
                        var index = parent.layer.getFrameIndex(window.name);
                        $.each(data.result, function (key, val) {
                            if (parent.$("#parentid_" + val.parentid).length <= 0) {
                                var table = getTableHtml(val);
                                parent.$(".layui-colla-item:last").after(table);
                            }
                            var html = '<tr class="choose"><td>' + val.num + '<input type="hidden" class="ppid" value="'+val.ppid+'"></td><td>' + val.name + '</td><td class="tdRadio">' + getRadioHtml(val.ppid, val.result) + '</td><td>' + getTextareaHtml(val) + '</td></tr>';
                            parent.$('#parentid_' + val.parentid).append(html);
                        });
                        parent.layui.use('element', function () {
                            var element = layui.element();
                            element.init();
                        });
                        parent.layui.form().render();
                        var count = parent.$('#count');
                        count.html(parseInt(count.html()) + parseInt(data.count));
                        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                        parent.layer.msg(data.msg, {
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

    exports('controller/patrol/patrol/addPoints', {});
});


