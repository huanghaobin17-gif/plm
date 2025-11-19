layui.define(function(exports){
    layui.use(['layer', 'form', 'element','table','upload','tipsType'], function() {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table,upload = layui.upload,tipsType = layui.tipsType;
        //初始化tips的选择功能
        tipsType.choose();

        //先更新页面部分需要提前渲染的控件
        form.render();
        $(".caculater").on('keyup', function () {
            var caculaterListObj = $(".caculaterList"),
                index = caculaterListObj.find("td input").index(this);
            if ($(this).val() == '') {
                caculaterListObj.next().find("td input").eq(index).val("");
            } else {
                var num = parseInt(caculaterListObj.prev().find("td").eq(index).html()) - parseInt($(this).val());
                if (num < 0) {
                    num = -num;
                }
                caculaterListObj.next().find("td input").eq(index).val(num);
            }
        });
        //监听保存按钮
        form.on('submit(save)',function (data) {
            var params = {};
            params = data.field;
            params.save_edit = params.save_edit ? params.save_edit : 'add';
            var flag = true;
            var target_textarea = $('.layui-collapse').find('textarea');
            $.each(target_textarea,function (index,item) {
                var classname = $(this).attr('class');
                if(classname.indexOf('red_border') >= 0){
                    if(!$.trim($(this).val())){
                        flag = false;
                    }
                }
            });
            if(!flag){
                layer.msg('保养结果不合格的，请填写异常处理详情！', {icon: 2,time:2000});
                return false;
            }
            submit($,params,'setQualityDetail');
            return false;
        });
        form.on('submit(nosave)',function (data) {
            return false;
        });

        //上传文件
        var uploadFile = upload.render({
            elem: '#file_url'  //绑定元素
            ,url: admin_name+'/Quality/setQualityDetail.html' //接口
            ,accept: 'file'
            ,exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload',qsid:qsid}
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
                    $('#file_url').html('<i class="layui-icon">&#xe67c;</i>重新上传');
                    layer.msg(res.msg,{icon : 1},1000);
                }else{
                    layer.msg(res.msg,{icon : 2},1000);
                }
            }
            ,error: function(index, upload){
                //失败
            }
        });

        var uploadFile1 = upload.render({
            elem: '.nameplate'  //绑定元素
            ,url: admin_name+'/Quality/setQualityDetail.html' //接口
            ,accept: 'file'
            ,exts: 'jpg|png|bmp|jpeg' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload_pic',type:'nameplate',qsid:$('input[name="qsid"]').val()}
            ,done: function(res, index, upload){
                if (res.status == 1) {
                    layer.msg(res.msg,{icon : 1,time:2000},function(){
                        getpic();
                    });
                }else{
                    layer.msg(res.msg,{icon : 2,time:2000});
                }
            }
            ,error: function(index, upload){
                //失败
            }
        });
        var uploadFile2 = upload.render({
            elem: '.instrument_view'  //绑定元素
            ,url: admin_name+'/Quality/setQualityDetail.html' //接口
            ,accept: 'file'
            ,exts: 'jpg|png|bmp|jpeg' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload_pic',type:'instrument_view',qsid:$('input[name="qsid"]').val()}
            ,done: function(res, index, upload){
                if (res.status == 1) {
                    layer.msg(res.msg,{icon : 1,time:2000},function(){
                        getpic();
                    });
                }else{
                    layer.msg(res.msg,{icon : 2,time:2000});
                }
            }
            ,error: function(index, upload){
                //失败
            }
        });

        $('.settingTemplate').on('click',function () {
            var flag = 1;
            top.layer.open({
                id: 'settingTemplates',
                type: 2,
                title: '设定模板【' + assets + '】',
                area: ['75%', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [$(this).attr('data-url')+'?assid='+assid+'&assnum='+assnum],
                end: function () {
                    if(flag){
                        location.reload();
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });

        //预览文件
        $('#scanfile').on('click',function () {
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
        $("body").on('click','.pic_name',function () {
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
        $("body").on('click','.remove_pic',function () {
            var tar = $(this);
            var id = tar.attr('data-id');
            layer.confirm('确定要删除该照片？', {icon: 3, title: '删除照片'}, function (index) {
                var params = {};
                params['id'] = id;
                params['action'] = 'delpic';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: 'setQualityDetail',
                    data: params,
                    //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                    beforeSend: function () {
                        layer.load(2);
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        if (data.status == 1) {
                            tar.parent().remove();
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                    },
                    //调用出错执行的函数
                    error: function () {
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 5});
                    },
                    complete: function () {
                        layer.closeAll('loading');
                    }
                });
                layer.close(index);
            });
            return false;
        });
        //监听保存按钮
        form.on('submit(upload)',function (data) {
            return false;
        });
        form.on('submit(keepquality)',function (data) {
            var params = {};
            params = data.field;
            console.log(params);
            params['action'] = 'keepquality';
            var flag = true;
            var target_textarea = $('.layui-collapse').find('textarea');
            $.each(target_textarea,function (index,item) {
                var classname = $(this).attr('class');
                if(classname.indexOf('red_border') >= 0){
                    if(!$.trim($(this).val())){
                        flag = false;
                    }
                }
            });
            if(!flag){
                layer.msg('保养结果不合格的，请填写异常处理详情！', {icon: 2,time:2000});
                return false;
            }
            submit($,params,'setQualityDetail');
            return false;
        });
        // var interval = setInterval(function () {
        //     var params = {};
        //     params['id'] = $('input[name="qsid"]').val();
        //     params['action'] = 'getpic';
        //     $.ajax({
        //         timeout: 5000,
        //         type: "POST",
        //         url: 'setQualityDetail',
        //         data: params,
        //         //成功返回之后调用的函数
        //         success: function (data) {
        //             if(!$.isEmptyObject(data.file_data)){
        //                 $.each(data.file_data,function (index,item) {
        //                     if(index == 'nameplate' || index == 'instrument_view'){
        //                         $("#"+index).find('ul').html('');
        //                         var lihtml = '';
        //                         $.each(item,function (index1,item1) {
        //                             lihtml += '<li><span class="pic_name" data-url="'+item1.file_url+'">'+item1.file_name+'</span> &nbsp;&nbsp;<i class="layui-icon layui-tab-close remove_pic" data-id="'+item1.file_id+'">&#x1006;</i></li>'
        //                         });
        //                         $("#"+index).find('ul').html(lihtml);
        //                     }
        //                 });
        //             }
        //         }
        //     });
        // },3500);


        $('.paizhao').on('mouseover',function () {
            $(this).find('.qrcode').show();
        });
        $('.paizhao').on('mouseout',function () {
            $(this).find('.qrcode').hide();
        });

        form.on('radio(result)', function (data, e) {
            var textarea = $(this).parent().next().find('textarea');
            if (data.value !== '合格') {
                if (textarea.val() === '') {
                    //标红
                    textarea.addClass('red_border');
                } else {
                    textarea.removeClass('red_border');
                }
            } else {
                textarea.removeClass('red_border');
            }
            form.render('radio');
        });
        $(document).ready(function () {
            $(document).on('blur', '.abnormal_remark', function () {
                var ppid = $(this).data('ppid');
                if ($.trim($(this).val()) !== '') {
                    $(this).removeClass('red_border');
                } else {
                    var value = $(this).parent('td').prev().find('input:radio[name="result[' + ppid + ']"]:checked').val();
                    if (value !== '合格') {
                        $(this).addClass('red_border');
                    }
                }
            });

        });

        function getpic(){
            var params = {};
            params['id'] = $('input[name="qsid"]').val();
            params['action'] = 'getpic';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'setQualityDetail',
                data: params,
                //成功返回之后调用的函数
                success: function (data) {
                    if(!$.isEmptyObject(data.file_data)){
                        $.each(data.file_data,function (index,item) {
                            if(index == 'nameplate' || index == 'instrument_view'){
                                $("#"+index).find('ul').html('');
                                var lihtml = '';
                                $.each(item,function (index1,item1) {
                                    lihtml += '<li><span class="pic_name" data-url="'+item1.file_url+'">'+item1.file_name+'</span> &nbsp;&nbsp;<i class="layui-icon layui-tab-close remove_pic" data-id="'+item1.file_id+'">&#x1006;</i></li>'
                                });
                                $("#"+index).find('ul').html(lihtml);
                            }
                        });
                    }
                }
            });
        }
    });
    exports('controller/qualities/quality/setQualityDetail', {});
});

//设置误差
function wucha(e,name,key) {
    //用户填写的测量值
    var user_value = $(e).val();
    user_value = $.trim(user_value);
    if(user_value == ''){
        return false;
    }
    var re = /^(([1-9]\d*)|\d)(\.\d{1,3})?$/;
    if (!re.test(user_value)) {
        layer.msg('请填写合理的测量值！',{icon : 2,time:2000});

    }
    var real_tolerance = 0;
    //读取系统设置值
    var sys_target = $(e).parent().parent().parent().find('tr')[0];
    var sys_td = $(sys_target).find('td')[key-1];
    var sys_value = $(sys_td).html();
    real_tolerance = sys_value - user_value;
    real_tolerance = Math.abs(real_tolerance);
    //设置误差
    var tolerance = $(e).parent().parent().next();
    var t_tolerance = tolerance.find('td')[key-1];
    $(t_tolerance).find('input').val(real_tolerance);

    //查找最大的误差值
    var max = tolerance.find('td');
    var max_value = [];
    $.each(max,function (index,item) {
        if($(this).find('input').val()){
            max_value.push(parseFloat($(this).find('input').val()));
        }
    });
    var max_tolerance = Math.max.apply(null, max_value);
    $('input[name="'+name+'_max_output"]').val(max_tolerance);
}
//设置示值误差
function shizhiwucha(e,name,key) {
    //用户填写的测量值
    var user_value = $(e).val();
    user_value = $.trim(user_value);
    if(user_value == ''){
        return false;
    }
    var re = /^(([1-9]\d*)|\d)(\.\d{1,3})?$/;
    if (!re.test(user_value)) {
        layer.msg('请填写合理的测量值！',{icon : 2,time:2000});

    }
    var real_tolerance = 0;
    //读取系统设置值
    var sys_target = $(e).parent().parent().parent().find('tr')[0];
    var sys_td = $(sys_target).find('td')[key-1];
    var sys_value = $(sys_td).html();
    real_tolerance = sys_value - user_value;
    real_tolerance = Math.abs(real_tolerance);
    //设置示值误差
    var tolerance = $(e).parent().parent().next();
    var t_tolerance = tolerance.find('td')[key-1];
    $(t_tolerance).find('input').val(real_tolerance);

    //查找最大的示值误差值
    var max = tolerance.find('td');
    var max_value = [];
    $.each(max,function (index,item) {
        if($(this).find('input').val()){
            max_value.push(parseFloat($(this).find('input').val()));
        }
    });
    var max_tolerance = Math.max.apply(null, max_value);
    $('input[name="'+name+'_max_value"]').val(max_tolerance);
}
//设置平均值
function average(e,name)
{
    var value = $('input[name="'+name+'[]"]');
    var count = 0;
    var num = 0;
    var Temperature_deviation = 0;
    var max = 0;
    var min = 100;
    $.each(value,function (index,item) {
        if (item.value) {
            count += parseFloat(item.value);
            num++;
            if (name=="T1") {
                if (parseFloat(item.value)>max) {
                    max = parseFloat(item.value);
                }
                if (parseFloat(item.value)<min) {
                    min = parseFloat(item.value);
                }
            }
        }
    });
    var ave = (count/num).toFixed(3);
    $('input[name="'+name+'a[]"]').val(ave);
    $('#'+name+'a').html(ave);
    if (name=="T1") {
        if(Math.abs(max-parseFloat(ave))>Math.abs(min-parseFloat(ave)))
        {
            var Volatility=(Math.abs(max-parseFloat(ave))).toFixed(3);
        }else{
            var Volatility=(Math.abs(min-parseFloat(ave))).toFixed(3);
        }
        $('#Volatility').html(Volatility);
        $('input[name="Volatility[]"]').val(Volatility);
        var Temperature_control_deviation = Math.abs(34-parseFloat(ave)).toFixed(3);
        $('#Temperature_control_deviation').html(Temperature_control_deviation);
        $('input[name="Temperature_control_deviation[]"]').val(Temperature_control_deviation);

    }
    if (name=="Tx"||name=="T1") {
        Temperature_deviation = parseFloat($('input[name="Txa[]"]').val())-parseFloat($('input[name="T1a[]"]').val());
        $('#Temperature_deviation').html((Math.abs(Temperature_deviation)).toFixed(3));
        $('input[name="Temperature_deviation[]"]').val((Math.abs(Temperature_deviation)).toFixed(3));
    }
    if (name=="T1"||name=="T2"||name=="T3"||name=="T4"||name=="T5") {
        var T1a_ave = parseFloat($('input[name="T1a[]"]').val());
        var T2a_ave = parseFloat($('input[name="T2a[]"]').val());
        var T3a_ave = parseFloat($('input[name="T3a[]"]').val());
        var T4a_ave = parseFloat($('input[name="T4a[]"]').val());
        var T5a_ave = parseFloat($('input[name="T5a[]"]').val());
        var T_ave = [Math.abs(T2a_ave-T1a_ave).toFixed(3),Math.abs(T3a_ave-T1a_ave).toFixed(3),Math.abs(T4a_ave-T1a_ave).toFixed(3),Math.abs(T5a_ave-T1a_ave).toFixed(3)];
        T_ave.sort();
        $('#Temperature_uniformity').html(T_ave[3]);
        $('input[name="Temperature_uniformity[]"]').val(T_ave[3]);
    }

}
