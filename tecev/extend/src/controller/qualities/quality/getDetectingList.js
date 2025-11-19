var layer;
layui.define(function(exports){
    layui.use(['layer', 'form', 'element','laydate','table','upload'], function(){

        layer.config(layerParmas());

        layer  = layui.layer;
        var form = layui.form,element = layui.element,laydate = layui.laydate,table = layui.table,upload = layui.upload;

        laydate.render(dateConfig('#checkdate'));

        //上传文件
        var uploadFile = upload.render({
            elem: '#file_url'  //绑定元素
            ,url: getDetectingList //接口
            ,accept: 'file'
            ,exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {actionType: 'upload'}
            ,choose: function(obj){
                //选择文件后
            }
            ,done: function(res, index, upload){
                layer.closeAll('loading');
                if (res.status == 1) {
                    var path = res.path;
                    $('input[name="report"]').val(path);
                    $('#scanfile').attr('data-url',path);
                    $('#scanfile').show();
                    $('#file_url').html('重选');
                    layer.msg(res.msg,{icon : 1},1000);
                }else{
                    layer.msg(res.msg,{icon : 2},1000);
                }
            }
            ,error: function(index, upload){
                //失败
            }
        });
        //预览文件
        $(document).on('click','#scanfile',function () {
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'scanfiles',
                type: 2,
                title:'文件预览',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [url]
            });
            return false;
        });

        //预览文件
        $('.showfile').on('click',function () {
            var url = $(this).attr('data-url');
            top.layer.open({
                type: 2,
                title:'文件预览',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [admin_name+'/Quality/scanPic.html?url='+url]
            });
            return false;
        });

        //监听确定新增仪器按钮
        form.on('submit(addInstruments)', function (data) {
            var name      = $.trim($('input[name="name"]').val());
            var model     = $.trim($('input[name="model"]').val());
            var serialnum = $.trim($('input[name="serialnum"]').val());
            var date      = $.trim($('input[name="date"]').val());
            var company   = $.trim($('input[name="company"]').val());
            var num       = $.trim($('input[name="num"]').val());
            var report    = $('input[name="report"]').val();
            if (!name) {
                layer.msg("请填写仪器名称！", {icon: 2, time: 1000});
                return false;
            }
            if (!model) {
                layer.msg("请填写仪器规格/型号！", {icon: 2, time: 1000});
                return false;
            }
            if (!serialnum) {
                layer.msg("请填写仪器序列号！", {icon: 2, time: 1000});
                return false;
            }
            if (!date) {
                layer.msg("请填写检定日期！", {icon: 2, time: 1000});
                return false;
            }
            if (!company) {
                layer.msg("请填写检定单位！", {icon: 2, time: 1000});
                return false;
            }
            if(!num){
                layer.msg("请填写计量编号！", {icon: 2, time: 1000});
                return false;
            }
            if (!report) {
                layer.msg("请上传检定报告", {icon: 2, time: 1000});
                return false;
            }
            var params = {};
            //保存数据
            params.name = name;
            params.model = model;
            params.serialnum = serialnum;
            params.date = date;
            params.company = company;
            params.num = num;
            params.report = report;
            var editRow = $(this).attr('edit-row');
            if(editRow){
                params.actionType = 'update';
                params.qiid = $(this).attr('data-qiid');
            }else{
                params.actionType = 'add';
            }
            var qiid = 0;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: getDetectingList,
                data: params,
                dataType: "json",
                beforeSend:beforeSend,
                async:false,
                success: function (data) {
                    if (data.status == 1) {
                        qiid = data.qiid;
                        layer.msg(data.msg,{icon : 1,time:1000});
                    }else{
                        layer.msg(data.msg,{icon : 2,time:1000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2,time:1000});
                },
                complete:complete
            });
            if(qiid > 0){
                var rp = '<div class="layui-btn-group">\n' +
                    '<button type="button" class="layui-btn layui-btn-xs" onclick="showfile(this)" data-url="'+$('input[name="report"]').val()+'">\n' +
                    '查看\n' +
                    '</button>\n' +
                    '</div>';
                if(editRow){
                    //编辑对应行数据
                    var target = $('#'+editRow);
                    target.find('td.name').html(name);
                    target.find('td.model').html(model);
                    target.find('td.serialnum').html(serialnum);
                    target.find('td.date').html(date);
                    target.find('td.company').html(company);
                    target.find('td.num').html(num);
                    target.find('td.report').html(rp);
                    $('.addMateriel').removeAttr('edit-row');
                    $('.addMateriel').removeAttr('data-qiid');
                }else{
                    $('#arc-empty-materiel').remove();
                    var tr = $('.materiel-list').find('tr');
                    var xuhao = tr.length;
                    var html = '<tr id="row_'+xuhao+'">\n' +
                        '<td class="td-align-center xuhao">'+xuhao+'</td>\n' +
                        '<td class="td-align-center name">'+name+'</td>\n' +
                        '<td class="td-align-center model">'+model+'</td>\n' +
                        '<td class="td-align-center serialnum">'+serialnum+'</td>\n' +
                        '<td class="td-align-center date">'+date+'</td>\n' +
                        '<td class="td-align-center company">'+company+'</td>\n' +
                        '<td class="td-align-center num">'+num+'</td>\n' +
                        '<td class="td-align-center report">'+rp+'</td>\n' +
                        '<td class="td-align-center">\n' +
                        '<div class="layui-btn-group">\n' +
                        '<button class="layui-btn layui-btn-xs layui-btn-warm" lay-submit="" lay-filter="editInstruments" title="编辑" data-row="'+xuhao+'" data-qiid="'+qiid+'"><i class="layui-icon">&#xe642;</i></button>\n' +
                        '<button class="layui-btn layui-btn-xs layui-btn-danger" lay-submit="" lay-filter="delInstruments" title="删除" style="margin-left:5px" data-row="'+xuhao+'" data-qiid="'+qiid+'"><i class="layui-icon">&#xe640;</i></button>\n' +
                        '</div>\n'+
                        '</td>\n'+
                        '</tr>';
                    $('.count').before(html);
                }
                //清空输入框数据
                $('input[name="name"]').val('');
                $('input[name="model"]').val('');
                $('input[name="serialnum"]').val('');
                $('input[name="date"]').val('');
                $('input[name="company"]').val('');
                $('input[name="num"]').val('');
                $('input[name="report"]').val('');
                $('#scanfile').hide();
                $('#file_url').html('上传文件');
            }
            return false;
        });
        //监听修改仪器按钮
        form.on('submit(editInstruments)', function(data){
            var row = $(this).attr('data-row');
            var qiid = $(this).attr('data-qiid');
            var pic = admin_name+'/Tool/showFile?path='+$(this).attr('data-pic');
            var target = $('#row_'+row);
            var name = target.find('td.name').html();
            var model = target.find('td.model').html();
            var serialnum = target.find('td.serialnum').html();
            var date = target.find('td.date').html();
            var company = target.find('td.company').html();
            var num = target.find('td.num').html();
            var report = target.find('td.report').find('.showFile').attr('data-path');
            $('input[name="name"]').val(name);
            $('input[name="model"]').val(model);
            $('input[name="serialnum"]').val(serialnum);
            $('input[name="date"]').val(date);
            $('input[name="company"]').val(company);
            $('input[name="num"]').val(num);
            $('input[name="report"]').val(report);
            $('#uploadfile').find('div:first').find('button:first').show();
            $('#uploadfile').find('div:first').find('button:first').attr('data-url',pic);
            $('#uploadfile').find('div:first').find('button:last').html('重选');
            $('.addMateriel').attr('edit-row','row_'+row);
            $('.addMateriel').attr('data-qiid',qiid);
            return false;
        });
        //监听删除仪器按钮
        form.on('submit(delInstruments)', function(data){
            var params = {};
            params.qiid = $(this).attr('data-qiid');
            params.actionType = 'del';
            var flag = false;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: getDetectingList,
                data: params,
                dataType: "json",
                beforeSend:beforeSend,
                async:false,
                success: function (data) {
                    if (data.status == 1) {
                        flag = true;
                        layer.msg(data.msg,{icon : 1,time:1000});
                    }else{
                        layer.msg(data.msg,{icon : 2,time:1000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2,time:1000});
                },
                complete:complete
            });
            if(flag){
                $(this).parent().parent().parent().remove();
                //重新编排序号
                var tr = $('.materiel-list').find('tr');
                var i = 1;
                $.each(tr,function () {
                    $(this).find('td.xuhao').html(i);
                    i++;
                });
                if(tr.length == 1){
                    var html = '<tr class="arc-empty" id="arc-empty-materiel">\n' +
                        '<td colspan="9" style="text-align: center;">暂时没数据</td>\n' +
                        '</tr>';
                    $('.count').before(html);
                }
            }
            return false;
        });
        $('#reset').on('click',function(){
            //清空输入框数据
            $('input[name="name"]').val('');
            $('input[name="model"]').val('');
            $('input[name="serialnum"]').val('');
            $('input[name="date"]').val('');
            $('input[name="company"]').val('');
            $('input[name="num"]').val('');
            $('input[name="report"]').val('');
            return false;
        });
    });
    exports('qualities/quality/getDetectingList', {});
});

function showfile(e) {
    var url = $(e).attr('data-url');
    if(url.indexOf(".jpg") > 0 || url.indexOf(".jpeg") > 0 || url.indexOf(".png") > 0 || url.indexOf(".gif") > 0){
        top.layer.open({
            type: 2,
            title:'文件预览',
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            scrollbar: false,
            area: ['75%', '100%'],
            closeBtn: 1,
            content: [admin_name+'/Quality/scanPic.html?url='+url]
        });
    }else{
        top.layer.open({
            type: 2,
            title:'文件预览',
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            scrollbar: false,
            area: ['75%', '100%'],
            closeBtn: 1,
            content: [url]
        });
    }
}
//下载
$(document).on('click','.downFile',function () {
    var params={};
    params.path= $(this).data('path');
    params.filename=$(this).data('name');
    postDownLoadFile({
        url:admin_name+'/Tool/downFile',
        data:params,
        method:'POST'
    });
});

//预览
$(document).on('click','.showFile',function () {
    var path= $(this).data('path');
    var name=$(this).data('name');
    var url=admin_name+'/Tool/showFile';
    top.layer.open({
        type: 2,
        title: name + '相关文件查看',
        scrollbar: false,
        offset: 'r',//弹窗位置固定在右边
        anim: 2, //动画风格
        area: ['70%', '100%'],
        closeBtn: 1,
        content: [url +'?path=' + path + '&filename=' + name]
    });
    return false;
});