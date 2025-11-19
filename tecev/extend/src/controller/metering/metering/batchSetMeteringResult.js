layui.define(function(exports){
    layui.use(['table', 'element', 'upload', 'form'], function () {
        var $ = layui.jquery, laydate = layui.laydate, upload = layui.upload, table = layui.table, form = layui.form,element = layui.element,
            layer = layui.layer, tablePlug = layui.tablePlug;

        //计量附件上传文件
        var uploadListIns = upload.render({
            elem: '#uploadMeteringResultPic'
            ,elemList: $('#demoList') //列表元素对象
            ,url: admin_name+'/Metering/batchSetMeteringResult' //此处用的是第三方的 http 请求演示，实际使用时改成您自己的上传接口即可。
            ,accept: 'file'
            ,multiple: true
            ,exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            ,method: 'POST'
            ,data: {type: 'uploadFiles'}
            ,number: 100
            ,auto: false
            ,bindAction: '#uploadMeteringResultPicAction'
            ,choose: function(obj){
                var that = this;
                var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                //读取本地文件
                obj.preview(function(index, file, result){
                    var tr = $(['<tr id="upload-'+ index +'">'
                        ,'<td>'+ file.name +'</td>'
                        ,'<td>'+ (file.size/1014).toFixed(1) +'kb</td>'
                        ,'<td><div class="layui-progress-'+ index +'">待上传</div></td>'
                        ,'<td>'
                        ,'<button class="layui-btn layui-btn-xs demo-reload layui-hide">重传</button>'
                        ,'<button class="layui-btn layui-btn-xs layui-btn-danger demo-delete">删除</button>'
                        ,'</td>'
                        ,'</tr>'].join(''));

                    //单个重传
                    tr.find('.demo-reload').on('click', function(){
                        obj.upload(index, file);
                    });

                    //删除
                    tr.find('.demo-delete').on('click', function(){
                        delete files[index]; //删除对应的文件
                        tr.remove();
                        uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                    });

                    that.elemList.append(tr);
                });
            }
            ,done: function(res, index, upload){ //成功的回调
                var that = this;
                if(res.status == 1){ //上传成功
                    var tr = that.elemList.find('tr#upload-'+ index)
                        ,tds = tr.children();
                    tds.eq(2).find('.layui-progress-'+index).html('<span style="color: #5FB878;">上传成功</span>'); //清空操作
                    tds.eq(3).html(''); //清空操作
                    delete this.files[index]; //删除文件队列已经上传成功的文件
                    return;
                }else if (res.status == 0){
                    var tr = that.elemList.find('tr#upload-'+ index)
                        ,tds = tr.children();
                    tds.eq(2).find('.layui-progress-'+index).html('<span style="color: #FF5722;">该文件已经上传过</span>'); //清空操作
                    delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            }
            ,allDone: function(obj){ //多文件上传完毕后的状态回调
                //console.log(obj)
            }
            ,error: function(index, upload){ //错误回调
                var that = this;
                var tr = that.elemList.find('tr#upload-'+ index)
                    ,tds = tr.children();
                tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
            }
        });
        //执行实例
        upload.render({
            elem: '#uploadMeteringResultFile', //绑定元素
            url: admin_name+'/Metering/batchSetMeteringResult',
            title: '上传文件',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            ext: 'xls|xlsx|xlsm',
            type: 'file',
            unwrap: false,
            auto: true,
            data: {
                "type": "upload"
            },
            multiple: true,
            before: function (input) {
                //返回的参数item，即为当前的input DOM对象
                layer.load(2);
            },
            done: function (res) {
                //上传完毕回调
                layer.closeAll('loading');
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1, time: 2000}, function () {
                        //刷新表格数据
                        table.reload('batchSetMeteringResultList', {
                            url: admin_name+'/Metering/batchSetMeteringResult.html'
                            , where: {"type": "getData"}
                            , page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                } else {
                    layer.msg(res.msg, {icon: 2, time: 2000});
                }
            }
            , error: function () {
                //请求异常回调
                layer.closeAll('loading');
                layer.msg('网络异常，请稍后再重试！', {icon: 2}, 1000);
            }
        });
        //导出模板
        $("#exploreMeteringResultModel").on('click', function () {
            window.location.href = 'batchSetMeteringResult.html?type=exploreMeteringResultModel';
        });
        //列表数据
        table.render({
            elem: '#batchSetMeteringResultList'
            , size:'sm'
            , limits: [10, 20, 50, 100]
            , loading: true
            //, width: 600
            , url: admin_name+'/Metering/batchSetMeteringResult.html' //数据接口
            , where: {
                type: 'getData'
                , sort: 'assets'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'temp_id' //排序字段，对应 cols 设定的各字段名
                , type: 'assets' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            //,page: true //开启分页
            , cols: [[ //表头
                {
                    type: 'checkbox',
                    fixed: 'left'
                }, {
                    field: 'temp_id',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'assets', fixed: 'left', title: '设备名称', width: 140, align: 'center'}
                , {field: 'assnum', fixed: 'left', title: '设备编码', width: 140, align: 'center'}
                , {field: 'model', title: '设备型号', width: 120, align: 'center'}
                , {field: 'productid', title: '产品序列号', width: 120, align: 'center'}
                , {field: 'cate', event: 'cate', title: '计量分类', edit: 'text', width: 160, align: 'center'}
                , {field: 'test_way', title: '检定方式', edit: 'text', width: 80, align: 'center'}
                , {field: 'this_date', title: '检定日期', edit: 'text', width: 100, align: 'center'}
                , {field: 'result', title: '检定结果', edit: 'text', width: 80, align: 'center'}
                , {field: 'report_num', title: '证书编号', edit: 'text', width: 120, align: 'center'}
                , {field: 'company', title: '检定机构', edit: 'text', width: 220, align: 'center'}
                , {field: 'money', title: '计量费用', edit: 'text', width: 80, align: 'center'}
                , {field: 'test_person', title: '检定人', edit: 'text', width: 80, align: 'center'}
                , {field: 'auditor', title: '审核人', edit: 'text', width: 80, align: 'center'}
                , {field: 'remark', title: '检定备注', edit: 'text', width: 140, align: 'center'}
                , {field: 'file_name', title: '计量附件',unresize:false, width: 220, align: 'center'}
                , {field: 'operation', fixed: 'right',title: '操作', width: 70, align: 'center'}
            ]]
            , done: function (res) {
                //上传完毕回调
                $.each($('.rquireCoin'), function (k, v) {
                    //因为不能直接帮TD赋值，所以在完成加载时找到异常项 赋值到父DIV
                    $(v).removeClass('rquireCoin').parent('div').addClass('rquireCoin');
                });

            }
        });
        //监听工具条
        table.on('tool(batchSetMeteringResultData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = admin_name+'/Metering/batchSetMeteringResult.html';
            var params = {};
            var layer_index = 0;
            params.temp_id = data.temp_id;
            params.type = 'updateData';
            switch (layEvent){
                case 'delResult':
                    params.type = 'delResult';
                    layer.confirm('确定删除该条数据吗？', {
                        icon: 3,
                        title: data.assets + '计量记录'
                    }, function (index) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: params,
                            dataType: "json",
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                layer.closeAll('loading');
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                        table.reload('batchSetMeteringResultList', {
                                            url: admin_name+'/Metering/batchSetMeteringResult.html'
                                            , where: {"type": "getData"}
                                            , page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                } else {
                                    layer.msg(data.msg, {icon: 2});
                                }
                            },
                            //调用出错执行的函数
                            error: function () {
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 5});
                            }
                        });
                        layer.close(index);
                    });
                    break;
                case 'cate':
                    form.render('select');
                    layer.open({
                        type: 1,
                        title: '修改设备:' + data.assets + ' 的计量分类',
                        area: ['450px', '300px'],
                        offset: '10px',
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 5,
                        resize: false,
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: $('#mcategoryList'),
                        end: function () {
                            //console.log('end');
                        },
                        success: function (layero, index) {
                            form.render('select');
                            layer_index = index;
                        }
                    });
                    break;
                case 'showFile':
                    var path = $(this).attr('data-path');
                    var name = $(this).attr('data-name');
                    top.layer.open({
                        type: 2,
                        title: '计量附件查看【'+name+'】',
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['50%', '100%'],
                        closeBtn: 1,
                        content: [admin_name+'/Tool/showFile?path=' + path + '&filename=' + name]
                    });
                    break;
            }
            //监听修改计量分类
            form.on('submit(saveMcategory)', function (data) {
                if (!data.field.mcid) {
                    layer.msg("请选择计量分类！", {icon: 2, time: 2000});
                    return false;
                }
                params.mcid = data.field.mcid;
                var url = admin_name+'/Metering/batchSetMeteringResult.html';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                layer.close(layer_index);
                                //同步更新表格和缓存对应的值
                                table.reload('batchSetMeteringResultList', {
                                    url: admin_name+'/Metering/batchSetMeteringResult.html'
                                    , where: {"type": "getData"}
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
        });


        //监听单元格编辑
        table.on('edit(batchSetMeteringResultData)', function (obj, e) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};

            params[cloum] = value;
            params.temp_id = data.temp_id;
            params.type = 'updateData';


            //需要验证的字段
            var keyvalue = [];
            keyvalue['this_date'] = '检定日期';
            keyvalue['test_way'] = '检定方式';
            keyvalue['result'] = '检定结果';
            keyvalue['money'] = '计量费用';

            var TDobj = $(this).parent().find('div');
            var is_do = true;
            for (var item in keyvalue) {
                if (cloum === 'money') {
                    if (value !== '') {
                        if (!check_price(value)) {
                            is_do = false;
                            TDobj.addClass('rquireCoin');
                        } else {
                            TDobj.removeClass('rquireCoin');
                        }
                    }
                }
                if (cloum === 'this_date') {
                    if (!value) {
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    } else {
                        var dateReg = /^\d{4}(-)\d{1,2}\1\d{1,2}$/;
                        if (!dateReg.test(value)) {
                            is_do = false;
                            TDobj.addClass('rquireCoin');
                        }
                    }
                }
                if (cloum === 'test_way') {
                    if (value !== '院内' && value !== '院外') {
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    } else {
                        TDobj.removeClass('rquireCoin');
                    }
                }
                if (cloum === 'result') {
                    if (value !== '合格' && value !== '不合格') {
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    } else {
                        TDobj.removeClass('rquireCoin');
                    }
                }
                if (item === cloum && !is_do) {
                    layer.msg('修改失败！请输入正确的' + keyvalue[cloum] + '！', {icon: 2, time: 2000});
                    return false;
                }
            }
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Metering/batchSetMeteringResult.html',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        TDobj.removeClass('rquireCoin');
                        layer.msg(data.msg, {icon: 1, time: 2000});
                    } else {
                        TDobj.addClass('rquireCoin');
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
        });
        //批量删除数据
        $('#batchDel').on('click', function () {
            var checkStatus = table.checkStatus('batchSetMeteringResultList');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要删除的数据！', {icon: 2, time: 1000});
                return false;
            }
            var temp_id = '';
            var params = {};
            params.type = 'delResult';
            for (j = 0, len = data.length; j < len; j++) {
                temp_id += data[j]['temp_id'] + ',';
            }
            params.temp_id = temp_id;
            layer.confirm('确定删除选中的数据吗？', {icon: 3, title: '批量删除数据'}, function (index) {
                $.ajax({
                    type: "POST",
                    url: admin_name+'/Metering/batchSetMeteringResult.html',
                    data: params,
                    dataType: "json",
                    beforeSend: function () {
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        layer.closeAll('loading');
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                table.reload('batchSetMeteringResultList', {
                                    url: admin_name+'/Metering/batchSetMeteringResult.html'
                                    , where: {"type": "getData"}
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                    },
                    //调用出错执行的函数
                    error: function () {
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 5});
                    }
                });
                layer.close(index);
            });
        });
        //保存选中数据
        $('#uploadSel').on('click', function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索

            var checkStatus = table.checkStatus('batchSetMeteringResultList');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要保存的数据！', {icon: 2, time: 2000});
                return false;
            }

            var error_nums = 0;
            $.each(data, function (k,v) {
                $.each(v, function (kk,vv) {
                    if(kk == 'cate' || kk == 'result' || kk == 'test_way' || kk == 'this_date' || kk == 'money'){
                        if(vv.indexOf('rquireCoin') > 0){
                            error_nums++;
                        }
                    }
                });
            });
            if (error_nums > 0) {
                layer.msg('请将有误的数据改正后再保存', {icon: 2, time: 2000});
                return false;
            }

            var error_file = 0;
            $.each(data, function (k,v) {
                $.each(v, function (kk,vv) {
                    if(kk == 'file_name'){
                        if(vv.indexOf('rquireCoin') > 0){
                            error_file++;
                        }
                    }
                });
            });
            if (error_file > 0) {
                layer.msg('请上传计量附件，匹配成功后再操作', {icon: 2, time: 2000});
                return false;
            }

            var temp_id = '';
            var params = {};
            params.type = 'save';
            for (j = 0, len = data.length; j < len; j++) {
                temp_id += data[j]['temp_id'] + ',';
            }
            params.temp_id = temp_id;
            $.ajax({
                type: "POST",
                url: admin_name+'/Metering/batchSetMeteringResult.html',
                data: params,
                dataType: "json",
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchSetMeteringResultList', {
                                url: admin_name+'/Metering/batchSetMeteringResult.html'
                                , where: {"type": "getData"}
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        });
                    } else {
                        //layer.msg(data.msg, {icon: 2});
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 5});
                }
            });
            layer.close(index);
        });
        //保存当页数据
        $('#uploadAll').on('click', function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            if ($('.rquireCoin').length != 0) {
                layer.msg('请将有误的数据改正后再保存', {icon: 2, time: 2000});
                return false;
            }
            var temp_id = '';
            var params = {};
            params.type = 'save';
            var tr = $('.layui-table-main').find('tr');
            $.each(tr, function () {
                $.each($(this).find('td'), function () {
                    if ($(this).attr('data-field') == 'temp_id') {
                        temp_id += ($(this).attr('data-content')) + ',';
                    }
                });
            });
            if (temp_id == '') {
                layer.msg('没有要保存的数据！', {icon: 2, time: 2000});
                return false;
            }
            params.temp_id = temp_id;
            $.ajax({
                type: "POST",
                url: admin_name+'/Metering/batchSetMeteringResult.html',
                data: params,
                dataType: "json",
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchSetMeteringResultList', {
                                url: admin_name+'/Metering/batchSetMeteringResult.html'
                                , where: {"type": "getData"}
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 5});
                }
            });
            layer.close(index);
        });

        //匹配计量附件
        $('#matchFiles').on('click', function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索
            var params = {};
            params.type = 'matchFiles';
            $.ajax({
                type: "POST",
                url: admin_name+'/Metering/batchSetMeteringResult.html',
                data: params,
                dataType: "json",
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchSetMeteringResultList', {
                                url: admin_name+'/Metering/batchSetMeteringResult.html'
                                , where: {"type": "getData"}
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 5});
                }
            });
            layer.close(index);
        });
    });
    exports('controller/metering/metering/batchSetMeteringResult', {});
});
