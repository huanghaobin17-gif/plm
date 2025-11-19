layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'laydate', 'tablePlug'], function () {
        var thisBody=$('#LAY-Assets-Borrow-borrowInCheckList');
        var layer = layui.layer
            , table = layui.table
            , form = layui.form
            , laydate = layui.laydate
            , tablePlug = layui.tablePlug;


        laydate.render({
            elem: '#borrow_in_time_input',
            type: 'datetime',
            format: 'yyyy-MM-dd HH:mm'
        });

        layer.config(layerParmas());
        var subsidiary=[];
        table.render({
            elem: '#borrowInCheckList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '借入检查确定列表'
            , url: borrowInCheckListUrl //数据接口
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
                    align: 'center',
                    style: 'background-color: #f9f9f9;',
                    type: 'space',
                    fixed: 'left',
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
                    width: 180,
                    align: 'center'
                },
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 180, align: 'center'},
                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 180, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 160, align: 'center'},
                {field: 'apply_user',hide: get_now_cookie(userid+cookie_url+'/apply_user')=='false'?true:false, title: '申请人', width: 100, align: 'center'},
                {field: 'deparment_approve',hide: get_now_cookie(userid+cookie_url+'/deparment_approve')=='false'?true:false, title: '借出科室审批', width: 120, align: 'center'},
                {field: 'assets_approve',hide: get_now_cookie(userid+cookie_url+'/assets_approve')=='false'?true:false, title: '设备科审核', width: 120, align: 'center'},
                {field: 'estimate_back',hide: get_now_cookie(userid+cookie_url+'/estimate_back')=='false'?true:false, title: '预计归还时间', width: 180, align: 'center'},
                {
                    field: 'borrow_in_time',
                    hide: get_now_cookie(userid + cookie_url + '/borrow_in_time') == 'false' ? true : false,
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    title: '确认借入时间',
                    width: 150,
                    align: 'center'
                },
                {
                    field: 'operation',
                    fixed: 'right',
                    title: '操作',
                    minWidth: 320,
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
        table.on('tool(borrowInCheckData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var params = {};
            params.borid = rows.borid;
            switch (layEvent) {
                case 'borrowInCheck':
                    params.status = BORROW_STATUS_GIVE_BACK;
                    if(!subsidiary[rows.borid]['in_time']){
                        layer.msg('请先补充 ' + rows.assets + ' 的确定借入时间', {icon: 2});
                        return false;
                    }
                    params.borrow_in_time = subsidiary[rows.borid]['in_time'];
                    params.supplement = subsidiary[rows.borid]['supplement'];
                    do_post(url,params);
                    break;
                case 'notBorrowInCheck':
                    params.status = BORROW_STATUS_NOT_APPLY;
                    layer.open({
                        id: 'notBorrowInChecks',
                        type: 1,
                        title: '【<span class="rquireCoin">*</span> 不借入原因】',
                        area: ['450px', '300px'],
                        offset: 'auto',
                        anim: 5,
                        resize: false,
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: thisBody.find('#end_reason'),
                        end: function () {
                            thisBody.find('textarea[name="end_reason"]').val('');
                        }
                    });

                    form.on('submit(NotToBorrow)', function (data) {
                        var field=data.field;
                        if(!field.end_reason){
                            layer.msg('请先补充 ' + rows.assets + ' 不借入的原因', {icon: 2});
                            return false;
                        }
                        params.end_reason=field.end_reason;
                        do_post(url,params);
                    });
                    break;
                default :
                    layer.msg('异常参数', {icon: 2});
            }
            return false;

        });

        function do_post(url,params) {
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
                            table.reload('borrowInCheckList', {
                                url: borrowInCheckListUrl
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
        form.on('submit(borrowInCheckListConfirm)', function (data) {
            var focu = thisBody.find('.focu');
            var borid=$(focu).siblings('input[name="borid"]').val();
            var params = data.field;
            if (params.borrow_in_time) {
                focu.html(params.borrow_in_time);
                layer.msg("成功录入,请点击 ‘确认设备完好无损并借入使用’ 按钮完成验收", {
                    icon: 1,
                    time: 2000
                }, function () {
                    layer.closeAll();
                });
                subsidiary[borid]['in_time']=params.borrow_in_time;
                subsidiary[borid]['supplement']=params.supplement;
            } else {
                layer.msg("请补充确认借入时间", {icon: 2}, 1000);
            }
            return false;
        });


        //点击 录入时间
        $(document).on('click', '#LAY-Assets-Borrow-borrowInCheckList .borrow_in_time', function () {
            var thisDiv = $(this);
            top.layer.open({
                type: 1,
                title: '【确认借入时间】',
                area: ['450px', '250px'],
                offset: 'auto',
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: thisBody.find('#borrow_in_time'),
                end: function () {
                    thisDiv.removeClass('focu');
                },
                success:function () {

                    var subsidiaryDiv=thisBody.find('.subsidiaryDiv');
                    subsidiaryDiv.hide();

                    thisBody.find('.borrow_in_time').removeClass('focu');
                    thisDiv.addClass('focu');

                    var borid=$(thisDiv).siblings('input[name="borid"]').val();

                    var borrow_in_time_input=thisBody.find('#borrow_in_time_input');
                    if(typeof subsidiary[borid]['in_time'] != 'undefined'){
                        borrow_in_time_input.val(subsidiary[borid]['in_time']);
                    }else{
                        borrow_in_time_input.val('');
                    }

                    var supplementTextarea=thisBody.find('textarea[name="supplement"]');
                    if(subsidiary[borid]['supplement']){
                        supplementTextarea.val(subsidiary[borid]['supplement']);
                    }else{
                        supplementTextarea.val('');
                    }
                    subsidiaryDiv.find('tbody').html('');
                    if(subsidiary[borid]['data'].length>0){
                        //显示附属设备
                        subsidiaryDiv.show();
                        var html='';
                        $.each(subsidiary[borid]['data'],function (k,v) {
                            html+='<tr>';
                            html+='<td>'+v.assets+'</td>';
                            html+='<td>'+v.assnum+'</td>';
                            html+='<td>'+v.model+'</td>';
                            html+='</tr>';
                        });
                        subsidiaryDiv.find('tbody').html(html);

                    }
                }
            });
        });
    });
    exports('assets/borrow/borrowInCheckList', {});
});

