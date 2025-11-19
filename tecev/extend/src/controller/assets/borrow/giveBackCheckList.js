layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'laydate', 'tablePlug'], function () {

        //获取本页面
        var thisBody=$('#LAY-Assets-Borrow-giveBackCheckList');
        var layer = layui.layer
            , table = layui.table
            , form = layui.form
            , laydate = layui.laydate
            , tablePlug = layui.tablePlug;


        laydate.render({
            elem: '#give_back_time_input',
            type: 'datetime',
            format: 'yyyy-MM-dd HH:mm',
            done: function (value, date) {
                var give_back_time_input = thisBody.find('#give_back_time_input');
                if (date.hours == 0 && date.minutes == 0 && date.seconds == 0) {
                    layer.confirm('当前选择日期暂无具体时间,请选择具体时间。', {
                        btn: ['继续选择', '暂不填写'],
                        title: '请补充具体时间',
                        shade: [0.8, '#393D49'],
                        closeBtn: 0
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            give_back_time_input.click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                        give_back_time_input.val("");
                    });
                } else {
                    if (apply_borrow_back_start_time && apply_borrow_back_end_time) {
                        var back_start = apply_borrow_back_start_time.split(':');
                        var back_end = apply_borrow_back_end_time.split(':');
                        if (back_start[0] <= date.hours && date.hours <= back_end[0]) {
                            //最后一个小时 分钟不能大于设置的分钟否则不合理
                            if (date.hours === parseInt(back_end[0]) && (back_end[1] < date.minutes)) {
                                layer.msg('归还时间范围' + apply_borrow_back_start_time + '至' + apply_borrow_back_end_time, {icon: 2});
                                give_back_time_input.addClass('border-red');
                                return false;
                            }
                            //第一个小时 分钟不能小于设置的分钟否则不合理
                            if (date.hours === parseInt(back_start[0]) && (back_start[1] > date.minutes )) {
                                layer.msg('归还时间范围' + apply_borrow_back_start_time + '至' + apply_borrow_back_end_time, {icon: 2});
                                give_back_time_input.addClass('border-red');
                                return false;
                            }
                        } else {
                            layer.msg('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, {icon: 2});
                            give_back_time_input.addClass('border-red');
                            return false;
                        }
                    }
                    give_back_time_input.removeClass('border-red');
                }

            }
        });

        layer.config(layerParmas());

        var subsidiary=[];
        table.render({
            elem: '#giveBackCheckList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '归还验收列表'
            , url: giveBackCheckListUrl //数据接口
            , where: {
                sort: 'borid',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'borid' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'borid',
                    title: '序号',
                    width: 65,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'borrow_num',
                    hide: get_now_cookie(userid + cookie_url + '/borrow_num') == 'false' ? true : false,
                    title: '流水号',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 170,
                    align: 'center'
                },
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 150, align: 'center'},
                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 180, align: 'center'},
                {field: 'apply_department',hide: get_now_cookie(userid+cookie_url+'/apply_department')=='false'?true:false, title: '申请科室', width: 150, align: 'center'},
                {field: 'apply_user',hide: get_now_cookie(userid+cookie_url+'/apply_user')=='false'?true:false, title: '申请人', width: 100, align: 'center'},
                {field: 'apply_time',hide: get_now_cookie(userid+cookie_url+'/apply_time')=='false'?true:false, title: '申请时间', width: 170, align: 'center'},
                {field: 'borrow_in_time',hide: get_now_cookie(userid+cookie_url+'/borrow_in_time')=='false'?true:false, title: '借出时间', width: 170, align: 'center'},
                {field: 'estimate_back',hide: get_now_cookie(userid+cookie_url+'/estimate_back')=='false'?true:false, title: '预计归还时间', width: 170, align: 'center'},
                {
                    field: 'give_back_time',
                    hide: get_now_cookie(userid + cookie_url + '/give_back_time') == 'false' ? true : false,
                    title: '实际归还时间',
                    width: 170,
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                },
                {
                    field: 'operation',
                    fixed: 'right',
                    title: '操作',
                    minWidth: 200,
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                }
            ]],
            done:function (value) {
                var result=value.rows;
                if(result){
                    $.each(result,function (k,v) {
                        subsidiary[v.borid]=[];
                        subsidiary[v.borid]['data']=[];
                        subsidiary[v.borid]['supplement']=v.supplement;
                        if(v.subsidiary){
                            var num=0;
                            $.each(v.subsidiary,function (k1,v1) {
                                subsidiary[v.borid]['data'][num]=[];
                                subsidiary[v.borid]['data'][num]['assets']=v1.assets;
                                subsidiary[v.borid]['data'][num]['assnum']=v1.assnum;
                                subsidiary[v.borid]['data'][num]['model']=v1.model;
                                num++;
                            });
                        }
                    });
                }
            }
        });
        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
            var key=data.elem.name;
            var status=data.elem.checked;
            document.cookie=userid+cookie_url+'/'+key+'='+status+"; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }
           // 
        });


        table.on('tool(giveBackCheckData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var params = {};
            params.borid = rows.borid;
            switch (layEvent) {
                case 'borrowBackCheck':
                    if (!subsidiary[rows.borid]['back_time']) {
                        layer.msg('请先补充 ' + rows.assets + ' 的实际归还时间', {icon: 2});
                        return false;
                    }
                    params.give_back_time = subsidiary[rows.borid]['back_time'];
                    do_post(url, params);
                    break;
                default :
                    layer.msg('异常参数', {icon: 2});
            }
            return false;

        });

        function do_post(url, params) {
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: params,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {
                            icon: 1, time: 2000
                        }, function () {
                            layer.closeAll();
                            table.reload('giveBackCheckList', {
                                url: giveBackCheckListUrl
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete: complete
            });
        }

        //确认时间
        form.on('submit(giveBackCheckListConfirm)', function (data) {
            if ($('.border-red').length !== 0) {
                layer.msg('请选择正确的归还时间', {icon: 2});
                return false;
            }
            var focu = thisBody.find('.focu');
            var borid=$(focu).siblings('input[name="borid"]').val();
            var params = data.field;
            if (params.give_back_time) {
                focu.html(params.give_back_time);
                layer.msg("成功录入,请点击 ‘确认设备完好无损并结束流程’ 按钮完成验收", {
                    icon: 1,
                    time: 2000
                }, function () {
                    layer.closeAll();
                });
                subsidiary[borid]['back_time']=params.give_back_time;
            } else {
                layer.msg("请点击录入归还时间", {icon: 2}, 1000);
            }
            return false;
        });


        //点击 录入时间
        $(document).on('click', '.give_back_time', function () {
            var thisDiv = $(this);
            layer.open({
                type: 1,
                title: '实际归还时间',
                area: ['450px', '250px'],
                offset: 'auto',
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: thisBody.find('#give_back_time'),
                end: function () {
                    thisDiv.removeClass('focu');
                },
                success:function () {

                    var subsidiaryDiv=thisBody.find('.subsidiaryDiv');
                    subsidiaryDiv.hide();

                    var supplementDiv=thisBody.find('.supplementDiv');
                    supplementDiv.hide();

                    thisBody.find('.give_back_time').removeClass('focu');
                    thisDiv.addClass('focu');

                    var borid=$(thisDiv).siblings('input[name="borid"]').val();

                    var give_back_time_input=thisBody.find('#give_back_time_input');
                    give_back_time_input.removeClass('border-red');
                    if(typeof subsidiary[borid]['back_time'] != 'undefined'){
                        give_back_time_input.val(subsidiary[borid]['back_time']);
                    }else{
                        give_back_time_input.val('');
                    }

                    var html='';
                    subsidiaryDiv.find('tbody').html('');

                    if(subsidiary[borid]['data'].length>0){
                        supplementDiv.find('th').addClass('border-notTop');
                        //显示附属设备
                        subsidiaryDiv.show();
                        html='';
                        $.each(subsidiary[borid]['data'],function (k,v) {
                            html+='<tr>';
                            html+='<td>'+v.assets+'</td>';
                            html+='<td>'+v.assnum+'</td>';
                            html+='<td>'+v.model+'</td>';
                            html+='</tr>';
                        });
                        subsidiaryDiv.find('tbody').html(html);
                    }else{
                        supplementDiv.find('th').removeClass('border-notTop');
                    }

                    if(subsidiary[borid]['supplement']){
                        html='<tr><td>'+subsidiary[borid]['supplement']+'</td></tr>';
                        supplementDiv.find('tbody').html(html);
                        supplementDiv.show();
                    }
                }
            });
        });
    });
    exports('assets/borrow/giveBackCheckList', {});
});

