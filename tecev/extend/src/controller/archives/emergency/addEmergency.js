layui.define(function(exports){
    layui.use(['table','form', 'layedit','upload','element'], function(){
        var table = layui.table,form = layui.form,upload = layui.upload,layer = layui.layer,layedit = layui.layedit,element = layui.element;
 
        layedit.set({
            uploadImage: {
                url: admin_name+'/Tool/addLayerImg' //接口url
            }
        });
        index =  layedit.build('editor'); //建立编辑器

        //自定义验证规则
        form.verify({
            title: function(value){
                if(value.length < 5){
                    return '标题至少得5个字符啊';
                }
            },
            cate_sel: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择预案分类！';
                }
            }
            ,content: function(value){
                layedit.sync(editIndex);
            }
        });

        //监听提交
        form.on('submit(save)', function(data){
            var params = data.field;
            params.content = layedit.getContent(index);
            submit($,params,'addEmergency');
            return false;
        });

        //监听修改
        form.on('submit(edit)', function(data){
            var params = data.field;
            params.content = layedit.getContent(index);
            submit($,params,'editEmergency');
            return false;
        });

        //添加分类 
        $('#addCate').on('click',function () {
            var flag = 1;
            var target = $('#category');
            top.layer.open({
                id: 'addEmerCate',
                type: 2,
                title: '预案分类设置',
                anim: 2, //动画风格
                scrollbar: false,
                area: ['560px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                maxmin:false,
                closeBtn: 1,
                content: admin_name+'/Emergency/addEmergency.html?action=addEmerCate',
                end:function () {
                    if(flag){
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: 'addEmergency',
                            data: {"action":"getEmerCate"},
                            dataType: "json",
                            async: true,
                            success: function (res) {
                                if (res.count > 0) {
                                    var html = '<option value="">请选择预案分类</option>';
                                    $.each(res.data,function (index,item) {
                                        html += '<option value="'+item.id+'">'+item.name+'</option>';
                                    });
                                    target.html('');
                                    target.html(html);
                                    form.render();
                                }else{
                                    layer.msg(res.msg,{icon : 2});
                                }
                            }
                        });
                    }
                },
                cancel:function () {
                    flag = 0;
                }
            });
        });

        //监听提交
        form.on('submit(saveCate)', function(data){
            var params = data.field;
            var title = data.field['title'].split("\n");
            $.each(title,function(k,v){
                title[k]=$.trim(v);
            });
            params.title = title.join(',');
            params.action = 'addEmerCate';
            submit($,params,'addEmergency');
            return false;
        });

        //上传文件
        var uploadInst = upload.render({
            elem: '#uploadFileEmer' //绑定元素
            ,exts: 'pdf|txt|doc|docx'
            , data: {"action": 'uploadFile'}
            ,url: 'addEmergency' //上传接口
            ,done: function(res){
                //上传完毕回调
                if(res.status == 1){
                    switch(res.ext){
                        case 'pdf':
                            var pic = '<img src="/Public/images/pdf.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        case 'doc':
                        case 'docx':
                            var pic = '<img src="/Public/images/word.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        case 'txt':
                            var pic = '<img src="/Public/images/text.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        default:
                            var pic = '<img src="/Public/images/word.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                    }
                    var html = '<li>' +
                        '   <input type="hidden" name="file_name[]" value="'+res['formerly']+'">\n' +
                        '   <input type="hidden" name="save_name[]" value="'+res['title']+'">\n' +
                        '   <input type="hidden" name="file_type[]" value="'+res['ext']+'">\n' +
                        '   <input type="hidden" name="file_size[]" value="'+res['size']+'">\n' +
                        '   <input type="hidden" name="file_url[]" value="'+res['src']+'">' +pic+
                        '   <span>'+res.formerly+'</span>\n' +
                        '   <span class="show_file">预览</span>\n' +
                        '   <span class="del_file">删除</span>\n' +
                        '</li>';
                    $('#add_files').append(html);
                }else{
                    layer.msg(res.msg, {icon: 2, time: 2000});
                }
            }
            ,error: function(){
                //请求异常回调
                layer.msg('网络繁忙', {icon: 2, time: 2000});
            }
        });

        table.render({
            elem: '#EmerCateList'
            , limits: [5, 20, 50, 100]
            , loading: true
            , limit: 5
            , title: '应急预案'
            , url: addEmergency //数据接口
            , where: {
                action: 'getcategory'
            } //如果无需传递额外参数，可不加该参数
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
             cols: [[ //表头
                {
                    field: 'id',
                    title: '序号',
                    width: '10%',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'name',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '分类名称',
                    width: '50%',
                    edit:'text',
                    align: 'center'
                }
                , {
                    field: 'num',
                    fixed: 'left',
                    title: '已有预案数量',
                    width: '40%',
                    align: 'center',
                    style: 'color:red'
                }
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        table.on('edit(EmerCateData)', function(obj){ 
            if (obj.value=="") {
                layer.msg('修改失败,分类名称不能为空');
            }
            var params = {};
            params['id'] = obj.data.id;
            params['action'] = 'savecategory';
            params['value'] = obj.value;
            $.ajax({
                            type: "POST",
                            url: addEmergency,
                            data: params,
                            //成功返回之后调用的函数
                            success: function (data) {
                                    layer.msg(data.msg, {icon: 1});
                            },
                            //调用出错执行的函数
                            error: function () {
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 5});
                            }
                        });
        });
        $(document).on('click', '.del_file',function () {
            $(this).parent().remove();
        });
        $(document).on('click', '.show_file',function () {
            var path = $(this).siblings('input[name="file_url[]"]').val();
            var name = $(this).siblings('input[name="file_name[]"]').val();
            var ext = $(this).siblings('input[name="file_type[]"]').val();

            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + '相关文件查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url + '?path=' + path + '&filename=' + name+'.'+ext]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
        });
    });
    exports('controller/archives/emergency/addEmergency', {});
});