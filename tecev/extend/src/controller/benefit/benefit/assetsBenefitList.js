layui.define(function (exports) {
    function getDate() {
        return $('input[name="assetsBenefitListDate"]').val();
    }

    layui.use(['layer', 'form', 'laydate', 'table', 'suggest', 'tablePlug','formSelects'], function () {
        var laydate = layui.laydate, form = layui.form, suggest = layui.suggest, tablePlug = layui.tablePlug,formSelects = layui.formSelects;

        //初始化搜索建议插件
        suggest.search();

        //渲染所有多选下拉
        formSelects.render('assetsBenefitListDepartment', selectParams(1));
        formSelects.btns('assetsBenefitListDepartment', selectParams(2), selectParams(3));

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        //设备名称搜索建议
        $("#assetsBenefitListAssets").bsSuggest(
            returnAssets()
        );

        //建议性搜索科室
        $("#assetsBenefitListDep").bsSuggest(
            returnDepartment()
        );

        laydate.render({
            elem: '#assetsBenefitListDate',
            festival: true,
            value: new Date()
            , type: 'month'
        });

        $("#assetsBenefitListReset").click(function () {
            layui.index.render();
        });

        var table = layui.table;
        form.render();
        //定义一个全局空对象
        var gloabOptions = {};
        //第一个实例
        table.render({
            elem: '#assetsBenefitList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , url: admin_name+'/Benefit/assetsBenefitList.html' //数据接口
            , where: {
                assetsBenefitListDate: getDate()
                , sort: 'assid'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
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
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-Benefit-Benefit-assetsBenefitListToolbar',
            defaultToolbar: ['filter']
            , cols: [[ //表头
                // {type: 'checkbox', fixed: 'left', field: 'assid'}
                {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'entryDate',
                    title: '录入月份',
                    width: 90,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/entryDate') == 'false' ? true : false,
                    templet: function (d) {
                        return '<input id="' + d.assnum + d.entryDate + '" type="hidden" value="' + d.benefitid + '">' + d.entryDate;
                    }
                }
                , {
                    field: 'assets',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '设备名称',
                    width: 190,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false
                }
                , {
                    field: 'assnum',
                    title: '设备编号',
                    width: 160,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false
                }
                , {
                    field: 'model',
                    title: '设备型号',
                    width: 170,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/model') == 'false' ? true : false
                }
                , {
                    field: 'department',
                    title: '所属科室',
                    width: 160,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/department') == 'false' ? true : false
                }
                , {
                    field: 'income',
                    title: '月收入',
                    edit: 'text',
                    width: 110,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/income') == 'false' ? true : false
                }
                , {
                    field: 'depreciation_cost',
                    title: '折旧费',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/depreciation_cost') == 'false' ? true : false
                }
                , {
                    field: 'material_cost',
                    title: '材料费',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/material_cost') == 'false' ? true : false
                }
                , {
                    field: 'maintenance_cost',
                    title: '维保费',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/maintenance_cost') == 'false' ? true : false
                }
                , {
                    field: 'management_cost',
                    title: '管理费',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/management_cost') == 'false' ? true : false
                }
                , {
                    field: 'operator',
                    title: '操作人员数量',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/operator') == 'false' ? true : false
                }
                , {
                    field: 'comprehensive_cost',
                    title: '综合费',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/comprehensive_cost') == 'false' ? true : false
                }
                , {
                    field: 'interest_cost',
                    title: '利息支出',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/interest_cost') == 'false' ? true : false
                }
                , {
                    field: 'work_day',
                    title: '工作天数',
                    edit: 'text',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/work_day') == 'false' ? true : false
                }
                , {
                    field: 'work_number',
                    title: '诊疗次数',
                    edit: 'text',
                    width: 130,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/work_number') == 'false' ? true : false
                }
                // , {field: 'positive_rate', title: '诊疗阳性次数', edit: 'text', width: 140, align: 'center'}
                , {
                    field: 'balance', title: '结余', width: 120, align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/balance') == 'false' ? true : false,
                    templet: function (d) {
                        var html = '';
                        var redClass = '';
                        if (typeof(d.benefitid) != 'undefined') {
                            if (d.balance <= 0) {
                                redClass = 'class="rquireCoin"';
                            }
                            html = d.balance;
                        }
                        return '<span id="' + d.assnum + d.entryDate + '-balance" ' + redClass + '>' + html + '</span>';
                    }
                }
                , {
                    field: 'surplus_rate', title: '结余率(%)', width: 180, align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/surplus_rate') == 'false' ? true : false,
                    templet: function (d) {
                        var html = '';
                        var redClass = '';
                        if (typeof(d.benefitid) !== 'undefined') {
                            html = d.surplus_rate + '%';
                            redClass = '';
                            if (d.surplus_rate <= 0) {
                                redClass = 'class="rquireCoin"';
                            }
                            if (d.income === 0) {
                                redClass = 'class="rquireCoin"';
                                html = '';
                                // html='无收入('+d.balance+')';
                            }
                        }
                        return '<span id="' + d.assnum + d.entryDate + '-surplus_rate" type="hidden" ' + redClass + ' >' + html + '</span>';
                    }
                }
            ]]
        });

        //编辑操作
        table.on('edit(assetsBenefitData)', function (obj) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var params = {};
            var benefitid = $('#' + data.assnum + data.entryDate).val();
            if (benefitid === 'undefined') {
                params.assnum = data.assnum;
                params.entryDate = getDate();
            } else {
                params.benefitid = benefitid;
            }
            params.value = value;
            params.cloum = cloum;
            params.type = 'updateData';
            //不能为空的字段
            var keyvalue = [];
            keyvalue['entryDate'] = '录入月份';
            keyvalue['assnum'] = '设备编号';
            keyvalue['assets'] = '设备名称';
            keyvalue['model'] = '设备型号';
            keyvalue['department'] = '所属科室';
            keyvalue['income'] = '月收入';
            keyvalue['work_number'] = '诊疗次数';
            keyvalue['depreciation_cost'] = '折旧费';
            keyvalue['material_cost'] = '材料费';
            keyvalue['maintenance_cost'] = '维保费';
            keyvalue['management_cost'] = '管理费';
            keyvalue['operator'] = '操作人员数量';
            keyvalue['comprehensive_cost'] = '综合费';
            keyvalue['interest_cost'] = '利息支出';
            keyvalue['work_day'] = '工作天数';
            // keyvalue['positive_rate'] = '诊疗阳性次数';
            var TDobj = $(this);
            var is_do = true;
            var switchType;
            for (var item in keyvalue) {
                switch (cloum) {
                    case 'work_number':
                        switchType = 2;
                        break;
                    case 'work_day':
                        switchType = 2;
                        break;
                    case 'operator':
                        switchType = 2;
                        break;
                    case 'positive_rate':
                        switchType = 2;
                        break;
                    default :
                        switchType = 1;
                        break;
                }
                switch (switchType) {
                    case 1:
                        if (!check_price(value)) {
                            is_do = false;
                            TDobj.parent().find('div').addClass('rquireCoin');
                        } else {
                            TDobj.parent().find('div').removeClass('rquireCoin');
                        }
                        break;
                    case  2:
                        if (!check_num(value)) {
                            is_do = false;
                            TDobj.parent().find('div').addClass('rquireCoin');
                        } else {
                            TDobj.parent().find('div').removeClass('rquireCoin');
                        }
                        break;
                    case 3:
                        if (!check_Percentage(value)) {
                            is_do = false;
                            TDobj.parent().find('div').addClass('rquireCoin');
                        } else {
                            TDobj.parent().find('div').removeClass('rquireCoin');
                        }
                        break;
                }
                if (item == cloum && !is_do) {
                    layer.msg('修改失败！请输入正确的' + keyvalue[cloum] + '！', {icon: 2, time: 2000});
                    return false;
                }
            }
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Benefit/assetsBenefitList.html',
                data: params,
                dataType: "json",
                success: function (result) {
                    var ajaxResult = result.result;
                    if (result.status === 1) {
                        if (benefitid === 'undefined') {
                            //将ID 赋值
                            $('#' + data.assnum + data.entryDate).val(ajaxResult.benefitid);
                        }
                        var surplus_rate = $('#' + data.assnum + data.entryDate + '-surplus_rate');
                        var balance = $('#' + data.assnum + data.entryDate + '-balance');
                        //修改结余
                        balance.removeClass('rquireCoin');
                        if (ajaxResult.balance <= 0) {
                            balance.addClass('rquireCoin');
                        }
                        balance.html(ajaxResult.balance);
                        //修改结余率
                        var surplus_rate_val = ajaxResult.surplus_rate + '%';
                        surplus_rate.removeClass('rquireCoin');
                        if (ajaxResult.surplus_rate <= 0) {
                            surplus_rate.addClass('rquireCoin');
                        }
                        console.log(ajaxResult.income);
                        if (ajaxResult.income === '0.00' || ajaxResult.income === '0') {
                            surplus_rate.addClass('rquireCoin');
                            surplus_rate_val = '';
                            // surplus_rate_val = '无收入(' + d.balance + ')';
                        }
                        surplus_rate.html(surplus_rate_val);
                        layer.msg(result.msg, {icon: 1, time: 2000});
                    } else {
                        layer.msg(result.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
        });

        //监听提交
        form.on('submit(assetsBenefitListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            if (!gloabOptions.assetsBenefitListDate) {
                layer.msg("请选择录入的月份", {icon: 2, time: 2000});
                return false
            }
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('assetsBenefitList', {
                url: admin_name+'/Benefit/assetsBenefitList.html'
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('toolbar(assetsBenefitData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url');
            switch (event) {
                case 'exportBenefit'://批量导出设备明细
                    var params = {};
                    params.assetsName = $('input[name="assetsName"]').val();
                    params.assetsDep = $('input[name="assetsDep"]').val();
                    params.assetsBenefitListDate = $('input[name="assetsBenefitListDate"]').val();
                    if (!params.assetsBenefitListDate) {
                        layer.msg("请选择录入的月份", {icon: 2, time: 2000});
                        return false
                    }
                    postDownLoadFile({
                        url: url,
                        data: params,
                        method: 'POST'
                    });
                    break;
                case 'batchAddBenefit'://批量入库
                    top.layer.open({
                        id: 'batchAddBenefits',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar: false,
                        maxmin: true,
                        area: ['100%', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table.reload('assetsBenefitList', {
                                url: admin_name+'/Benefit/assetsBenefitList.html'
                                , where: gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }

                    });
                    break;
            }
        });
    });
    exports('benefit/benefit/assetsBenefitList', {});
});

