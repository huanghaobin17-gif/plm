layui.define(function (exports) {
    layui.use(['table', 'form', 'suggest', 'tablePlug'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //定义一个全局选中的模板id
        lastChooseTpid = [];
        var tpidVal = $("input[name='tpid']").val();
        if (tpidVal != '') {
            lastChooseTpid = tpidVal.split(',');
        } else {
            lastChooseTpid = [];
        }
        //定义一个全局默认选中的模板id
        lastDefaultChooseTpid = [];
        var tpidDefaultVal = $("input[name='default_tpid']").val();
        if (tpidDefaultVal != '') {
            lastDefaultChooseTpid.push(tpidDefaultVal);
        } else {
            lastDefaultChooseTpid = [];
        }


        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());
        layui.form.render();
        table.render({
            elem: '#batchsettingTemplateList'
            , limits: [10, 20, 50, 100]
            , where: {
                name: $("input[name='name']").val()
            }
            , loading: true
            , url: admin_name + '/PatrolSetting/batchsettingTemplate?type=tp' //数据接口
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , request: {
                pageName: 'page' //页码的参数名称，默认：page
                , limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , page: true //开启分页
            , cols: [[ //表头
                {
                    field: 'tpid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'name',
                    title: '保养模板名称',
                    width: 240,
                    align: 'center',
                    templet: function (value, row, index) {
                        return '<a lay-event="show" style="text-decoration: none;color: #00a6c8;" href="javascript:void(0)">' + value.name + '</a>';
                    }
                }
                , {
                    field: 'remark',
                    title: '备注',
                    width: 500,
                    align: 'center'
                }
                , {
                    field: 'm',
                    title: '请选定模板',
                    minWidth: 100,
                    fixed: 'right',
                    align: 'center',
                    templet: function (value) {
                        var thistpid = $("input[name='tpid']").val();
                        var checked = '';
                        if (thistpid == value.tpid) {
                            checked = 'checked';
                        }
                        return '<input class="tpid" type="checkbox" name="tpidCheck" title="" lay-skin="primary" lay-filter="tpidCheck" value="' + value.tpid + '" ' + checked + '>';
                    }
                }
                , {
                    field: 'dm',
                    title: '默认模板',
                    minWidth: 100,
                    fixed: 'right',
                    align: 'center',
                    templet: function (value, row, index) {
                        var thistpid = $("input[name='default_tpid']").val();
                        if (thistpid == value.tpid) {
                            return '<form class="layui-form"><input type="radio" name="default_tpid" lay-skin="primary" lay-filter="radioCheck" value="' + value.tpid + '" checked></form>';
                        } else {
                            return '<form class="layui-form"><input type="radio" name="default_tpid" lay-skin="primary" lay-filter="radioCheck" value="' + value.tpid + '" disabled></form>';
                        }
                    }
                }
            ]],
            done: function () {
                radioChecked();
                checkboxChecked();
            }
        });

        table.on('tool(batchsettingTemplateData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'show':
                    top.layer.open({
                        type: 2,
                        title: rows.name + '模板信息',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [admin_name + '/PatrolSetting/template?id=' + rows.tpid + '&type=showTemplate']
                    });
                    break;
            }
        });
        //搜索按钮
        form.on('submit(settingTemplateSearch)', function (data) {
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('batchsettingTemplateList', {
                url: admin_name + '/PatrolSetting/batchsettingTemplate?type=tp'
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //新增模板
        $("#addTemplate").on('click', function () {
            top.layer.open({
                id: 'addTemplates',
                type: 2,
                title: $(this).html(),
                scrollbar: false,
                offset: 'r',
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [admin_name + '/PatrolSetting/addTemplate'],
                end: function () {
                    table.reload('batchsettingTemplateList', {
                        where: {
                            name: $("input[name='name']").val(),
                            tpid: $("input[name='tpid']").val(),
                            level: $("select[name='level'] option:selected").val()
                        }
                    });
                }
            });
        });

        //单选框操作
        form.on('radio(radioCheck)', function (data) {
            lastDefaultChooseTpid = [];
            lastDefaultChooseTpid.push(data.value);
        });

        function radioChecked() {
            var default_tpid = $("input[name='default_tpid']");//单选框合集
            default_tpid.each(function(k,v){
                var thisValue = $(v).val();
                if ($.inArray(thisValue,lastDefaultChooseTpid) != -1){
                    $(v).prop("checked",true);
                }else {
                    $(v).prop("checked",false);
                }
                if ($.inArray(thisValue,lastChooseTpid) != -1){
                    $(v).prop("disabled",false);
                }else {
                    $(v).prop("disabled",true);
                }
            });
            form.render('radio');
        }

        //多选框操作
        form.on('checkbox(tpidCheck)', function (data) {
            var checked = data.elem.checked,//是否选中状态
                value = data.value,//选中的模板id;
                thisDom = $(data.elem);
            if (checked) {
                //选中就存数组
                lastChooseTpid.push(value);
                thisDom.parents("tr").find("input[name='default_tpid']").prop("disabled", false);
                //thisDom.parents(".layui-table-col-special").siblings(".layui-table-col-special").find("input[name='default_tpid']").prop("disabled",false);
                form.render("radio");
            } else {
                //没选中就剔除
                lastChooseTpid.splice($.inArray(value, lastChooseTpid), 1);
                thisDom.parents("tr").find("input[name='default_tpid']").prop("disabled", true);
                thisDom.parents("tr").find("input[name='default_tpid']").prop("checked", false);
                form.render("radio");
            }
            //判断选中的数量是否大于3
            if (lastChooseTpid.length > 3) {
                //剔除这一次的选中及状态
                lastChooseTpid.splice(3, 1);
                thisDom.prop("checked", false);
                thisDom.parents("tr").find("input[name='default_tpid']").prop("disabled", true);
                thisDom.parents("tr").find("input[name='default_tpid']").prop("checked", false);
                form.render("radio");
                layer.msg('最多选择3个模板', {icon: 2}, 1000);
                judgeCheckbox(1);
            } else {
                judgeCheckbox(-1);
            }
            return false;
        });

        //判断checkbox选中的数量 status ==1 最多选择3个模板
        function judgeCheckbox(status) {
            var tpidCheckObj = $("input[name='tpidCheck']");//选择框合集
            if (status == 1) {
                tpidCheckObj.each(function (k, v) {
                    var thisValue = $(v).val();
                    if ($.inArray(thisValue, lastChooseTpid) != -1) {
                        $(v).prop("disabled", false);
                    } else {
                        $(v).prop("disabled", true);
                    }
                });
                form.render('checkbox');
            } else {
                tpidCheckObj.each(function (k, v) {
                    $(v).prop("disabled", false);
                });
                form.render('checkbox');
            }
        }

        //检测表格是否需要选中 解决分页状态
        function checkboxChecked() {
            var tpidCheckObj = $("input[name='tpidCheck']");//选择框合集
            tpidCheckObj.each(function (k, v) {
                var thisValue = $(v).val();
                if ($.inArray(thisValue, lastChooseTpid) != -1) {
                    $(v).prop("checked", true);
                } else {
                    $(v).prop("checked", false);
                }
            });
            form.render('checkbox');
        }

        $("#batchsettingTemplateName").bsSuggest({
            url: admin_name + '/Public/getAllTemplate',
            effectiveFieldsAlias: {tpid: "序号", name: "模板名称"},
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "tpid",
            keyField: "name",
            listStyle: {
                "max-height": "375px", "max-width": "480px",
                "overflow": "auto", "width": "400px", "text-align": "center"
            },
            clearable: false
        });

        //最终提交
        form.on('submit(add)', function () {
            if (lastChooseTpid.length == 0) {
                layer.msg('请至少选择一个模板', {icon: 2}, 1000);
                return false;
            } else {
                var params = {};
                params.assid = $("input[name='assid']").val();
                params.assnum = $("input[name='assnum']").val();
                params.tpid = lastChooseTpid;
                params.default_tpid = $("input[name='default_tpid']:checked").val();
                if (typeof params.default_tpid == 'undefined') {
                    params.default_tpid = '';
                }
                submit($, params, 'batchSettingTemplate');
            }
            return false;
        });
    });
    exports('controller/patrol/patrolsetting/batchsettingtemplate', {});
});





