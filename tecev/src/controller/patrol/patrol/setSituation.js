layui.define(function(exports){
    layui.use(['layer', 'form', 'element','upload','laydate'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,upload = layui.upload, element = layui.element,laydate = layui.laydate;

        laydate.render({
            elem: '#executeData' //指定元素
            , type: 'datetime',
            calendar: true
            , trigger: 'click'
            ,value: now
            ,min: min
            ,max: max
        });
        var thisbody = $('#LAY-Patrol-Patrol-setSituation');

        var url = thisbody.find("input[name='url']").val();
        form.on('radio(result)', function (data, e) {
            var textarea = $(this).parent().next().find('textarea');
            var addRepair = thisbody.find('.addRepair'), addexecute = thisbody.find('.addexecute');
            if (data.value !== '合格') {
                if (textarea.val() === '') {
                    //标红
                    textarea.addClass('red_border');
                } else {
                    textarea.removeClass('red_border');
                }
            } else {
                var asset_status = thisbody.find("input[name='asset_status']:checked").val();
                textarea.removeClass('red_border');
            }
            thisbody.find("input[name='asset_status']:checked").removeAttr('checked');
            addexecute.addClass('layui-btn-disabled');
            addexecute.attr("disabled", true);
            addRepair.addClass('layui-btn-disabled');
            addRepair.attr("disabled", true);
            thisbody.find('.user').hide();
            form.render('radio');
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
                content: [admin_name+'/Patrol/scanPic.html?assnum='+assnum+'&cycid='+cycid]
            });
            return false;
        });
        if (file_path!=""&&file_path!=undefined) {
            var path = file_path;
            $('input[name="report"]').val(path);
            $('#scanfile').attr('data-url',path);
            $('#scanfile').show();
            $('#file_url').html('<i class="layui-icon">&#xe67c;</i>继续上传');
        }
        //上传文件
        var uploadFile = upload.render({
            elem: '#file_url'  //绑定元素
            ,url: admin_name+'/patrol/doTask' //接口
            ,accept: 'file'
            ,exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload',cycid:cycid,assnum:assnum}
            ,choose: function(obj){
                //选择文件后
            },before: function(obj){
                layer.msg('正在上传文件，请稍候...', {
                        icon: 16,
                        time: 30000,
                        shade: 0.01
                    });
            }
            ,done: function(res, index, upload){
                layer.closeAll('loading');
                if (res.status == 1) {
                    var path = res.path;
                    $('input[name="report"]').val(path);
                    $('#scanfile').attr('data-url',path);
                    $('#scanfile').show();
                    $('#file_url').html('<i class="layui-icon">&#xe67c;</i>继续上传');
                    layer.msg(res.msg,{icon : 1},1000);
                }else{
                    layer.msg(res.msg,{icon : 2},1000);
                }
            }
            ,error: function(index, upload){
                //失败
            }
        });
        //监听保存按钮
        form.on('submit(upload)',function (data) {
            return false;
        });
        thisbody.find('#addPoints').click(function () {
            var tpid = $(this).data('tpid'), level = thisbody.find('input[name="level"]').val();
            top.layer.open({
                id: 'addPoints',
                type: 2,
                title: asArr_Name + '-补充明细',
                shade: 0,
                anim: 2,
                offset: 'r',//弹窗位置固定在右边
                scrollbar: false,
                area: ['780px', '100%'],
                content: url + '?tpid=' + tpid + '&level=' + level+'&action=addSetPoints',
                end: function (layero, index) {
                    var data = JSON.parse(localStorage.getItem('Points'));
                    if (data) {
                        if (thisbody.find("#parentid_" + data.parentid).length <= 0) {
                            var table = getTableHtml(data);
                            thisbody.find(".layui-colla-item:last").after(table);
                        }
                        var html = '' +
                            '<tr class="choose"><td style="text-align: center;">' + data.num + '<input type="hidden" class="ppid" value="' + data.ppid + '"></td>' +
                            '<td>' + data.name + '</td><td class="tdRadio">' + getRadioHtml(data.ppid, data.result) + '</td>' +
                            '<td>' + getTextareaHtml(data) + '</td></tr>';
                        thisbody.find("#parentid_" + data.parentid).append(html);

                        element.init();
                        form.render();
                        var count = thisbody.find('#count');
                        count.html(parseInt(count.html()) + 1);
                        localStorage.clear('Points');
                    }
                }
            });
        });

        //选择设备现状-改变提交按钮状态
        form.on('radio(asset_status)', function (data) {
            var addRepair = thisbody.find('.addRepair'), addexecute = thisbody.find('.addexecute');
            if (parseInt(data.value) === parseInt(ASSETS_STATUS_FAULT) || parseInt(data.value) === parseInt(ASSETS_STATUS_ABNORMAL)) {
                //转至报修 按钮可用
                var result = arrToString(thisbody.find(".result:checked"));
                var repeat = true;
                $.each(result.split(','), function (i, val) {
                    if (val !== '合格') {
                        repeat = false;
                        return false
                    }
                });
                if (repeat) {
                    $(this).removeAttr('checked');
                    layer.msg("设备巡查项全部合格,请重新检查并录入异常的明细项", {icon: 2}, 1000);
                    form.render('radio');
                    return false;
                }
                addRepair.removeClass('layui-btn-disabled');
                addRepair.removeAttr("disabled");
                addexecute.addClass('layui-btn-disabled');
                addexecute.attr("disabled", true);
                thisbody.find('.user').show();
            } else {
                //保存该设备保养 按钮可用
                addRepair.addClass('layui-btn-disabled');
                addRepair.attr("disabled", true);
                addexecute.removeClass('layui-btn-disabled');
                addexecute.removeAttr("disabled");
                thisbody.find('.user').hide();
            }
            form.render('radio');
        });


        form.on('checkbox(execute_status)', function () {
            var addRepair = thisbody.find('.addRepair'), addexecute = thisbody.find('.addexecute'),
                temporary = thisbody.find('.temporary');
            var execute_status = thisbody.find('input[name="execute_status"]:checked').val();
            var asset_status = thisbody.find("input[name='asset_status']");
            if (parseInt(execute_status) === parseInt(ASSETS_STATUS_NOT_OPERATION)) {
                thisbody.find('.reason').show();
                addexecute.addClass('layui-btn-disabled');
                addexecute.attr("disabled", true);
                addRepair.addClass('layui-btn-disabled');
                addRepair.attr("disabled", true);
                temporary.addClass('layui-btn-disabled');
                temporary.attr("disabled", true);
                thisbody.find("input[name='asset_status']:checked").removeAttr('checked');
                asset_status.addClass('layui-btn-disabled');
                asset_status.attr("disabled", true);
                form.render('radio');
            } else {
                asset_status.removeClass('layui-btn-disabled');
                asset_status.removeAttr("disabled");
                form.render('radio');
                temporary.removeClass('layui-btn-disabled');
                temporary.removeAttr("disabled");
                thisbody.find('.reason').hide();
            }
        });

        //完成并保存该设备保养
        form.on('submit(addexecute)', function () {
            var params = getParams();
            if (parseInt(params.asset_status) !== parseInt(ASSETS_STATUS_IN_MAINTENANCE) && parseInt(params.asset_status) !== parseInt(ASSETS_STATUS_SCRAPPED)) {
                if (thisbody.find(".red_border").length > 0) {
                    layer.msg("请将不是合格的保养明细项中的异常处理详情补全", {icon: 2}, 1000);
                    return false;
                }
            }
            params.executeStatus = MAINTAIN_COMPLETE;
            console.log(params);
            addPatrolExecute($, params);
            return false;
        });


        //不进行保养并结束
        form.on('submit(noOperation)', function () {
            var params = getParams();
            params.reason = $.trim(thisbody.find('textarea[name="reason"]').val());
            if (params.reason === '') {
                layer.msg("请输入该设备不进行保养的原因", {icon: 2}, 1000);
                return false;
            }
            params.asset_status = ASSETS_STATUS_NOT_OPERATION;
            params.executeStatus = MAINTAIN_COMPLETE;
            return false;
            addPatrolExecute($, params);
            return false;
        });


        //转至报修
        form.on('submit(addRepair)', function () {
            if (thisbody.find(".red_border").length > 0) {
                layer.msg("请将不是合格的保养明细项中的异常处理详情补全", {icon: 2}, 1000);
                return false;
            }
            var params = getParams(), applicant = thisbody.find("input[name='applicant']:checked");
            params.applicant = applicant.attr('title');
            params.userid = applicant.val();
            params.executeStatus = MAINTAIN_COMPLETE;
            addPatrolExecute($, params);
            return false;
        });

        //暂存该设备保养
        form.on('submit(temporary)', function () {
            if (thisbody.find(".red_border").length > 0) {
                layer.msg("请将不是合格的保养明细项中的异常处理补全", {icon: 2}, 1000);
                return false;
            }
            var params = getParams();
            params.executeStatus = MAINTAIN_PATROL;
            addPatrolExecute($, params);
            return false;
        });


        //返回对应拼接好的字符串
        function arrToString(Arr) {
            var string = '';
            var value = '';
            Arr.each(function () {
                if ($(this).val() === '') {
                    value = '#';
                } else {
                    value = $(this).val();
                }
                string += "," + value;
            });
            return string.substring(1);
        }

        $(document).ready(function () {
            $(document).on('blur', '.abnormal_remark', function () {
                var ppid = $(this).data('ppid');
                if ($(this).val() !== '') {
                    $(this).removeClass('red_border');
                } else {
                    var value = $(this).parent('td').prev().find('input:radio[name="result[' + ppid + ']"]:checked').val();
                    if (value !== '合格') {
                        $(this).addClass('red_border');
                    }
                }
            });

        });



        function getParams() {
            var params = {};
            var resultArr = thisbody.find(".result:checked");
            var ppidArr = thisbody.find(".ppid");
            var abnormal_remarkArr = thisbody.find(".abnormal_remark");
            params.ppid = arrToString(ppidArr);
            params.result = arrToString(resultArr);
            params.abnormal_remark = arrToString(abnormal_remarkArr);
            params.asset_status = thisbody.find("input[name='asset_status']:checked").val();
            params.remark = thisbody.find("textarea[name='remark']").val();
            params.assetnum = thisbody.find("input[name='assetnum']").val();
            params.cycid = thisbody.find("input[name='cycid']").val();
            params.action = thisbody.find("input[name='action']").val();
            params.url = thisbody.find("input[name='url']").val();
            if(thisbody.find("input[name='complete_time']").val()){
                params.complete_time = thisbody.find("input[name='complete_time']").val();
            }
            return params;
        }

//生成table
        function getTableHtml(val) {
            return '' +
                '<div class="layui-colla-item"><h2 class="layui-colla-title">' + val.parentName + '<i class="layui-icon layui-colla-icon">' +
                '</i></h2><div class="layui-colla-content layui-show"> <table class="layui-table tablesorter alltable"> <thead><tr> ' +
                '<th style="width: 12%" class="header">编号</th> <th style="width: 20%" class="header">明细名称</th> <th>保养结果</th> ' +
                '<th style="width: 40%;text-align: center;">异常处理详情</th> </tr> </thead><tbody id="parentid_' + val.parentid + '">' +
                '</tbody></table></div></div>';
        }
//生成异常处理textarea
        function getTextareaHtml(val) {
            var className = '';
            if (val.result !== '合格') {
                className = 'red_border';
            }
            return '<textarea class="abnormal_remark ' + className + '" data-ppid="' + val.ppid + '" style="width: 100%; border: 1px solid #dddddd"></textarea>';
        }

//生成保养结果Radio
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
                if (name === result) {
                    check = 'checked';
                }
                html += '' +
                    '<input ' + check + ' type="radio" title="' + name + '" name="result[' + id + ']" class="result" ' +
                    'value="' + name + '" lay-filter="result"><div class="layui-unselect layui-form-radio">' +
                    '<i class="layui-anim layui-icon"></i><span>' + name + '</span></div>';
            }
            return html;
        }
    });
    function addPatrolExecute($, params) {
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: params.url,
            data: params,
            dataType: "json",
            beforeSend: function (){
                layer.load(1, {
                    shade: [0.1,'#fff'] //0.1透明度的白色背景
                });
            },
            success: function (data) {
                if (data) {
                    if (data.status === 1) {
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
            },
            complete:function () {
                layer.closeAll('loading');
            }
        });
    }
    exports('controller/patrol/patrol/setSituation', {});
});




