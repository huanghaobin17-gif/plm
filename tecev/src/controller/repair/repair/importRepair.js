//批量添加维修单
$("#exportRepairModel").on('click', function () {
    window.location.href = admin_name+'/Repair/batchAddRepair.html?type=exportRepairModel';
});
var postDownLoadFile = function (options) {
    var config = $.extend(true, {method: 'POST'}, options);
    var $iframe = $('<iframe id="down-file-iframe" />');
    var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
    $form.attr('action', config.url);
    for (var key in config.data) {
        $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
    }
    $(document.body).append($iframe);
    $iframe.append($form);
    $form[0].submit();
    $iframe.remove();
};
var parts = new Array;
var subparts = {};

layui.define(function(exports){
    layui.use(['layer', 'upload','laydate'], function () {
        var $ = layui.jquery, upload = layui.upload,laydate = layui.laydate;

        uploadFile = upload.render({
            elem: '#batchAddRepairupload'  //绑定元素
            , url: $('#batchAddRepairupload').attr('data-url') //接口
            , method: 'POST'
            , exts: 'xls|xlsx|xlsm' //格式 用|分隔
            , done: function (res, index, upload) {
                layer.closeAll('loading');
                if (res.status == 1) {
                    parts = res.parts;
                    addItem(res);
                    //对时间的格式化
                    lay('.dayFormat').each(function(){
                        laydate.render({
                            elem: this
                            ,trigger: 'click'
                            ,done: function(value){
                                if (value == ''){
                                    $(this.elem).attr('class', 'td-input-border-r');
                                } else {
                                    $(this.elem).attr('class', 'td-input-border-g');
                                }
                            }
                        });
                    });
                    lay('.dateTimeFormat').each(function(){
                        laydate.render({
                            elem: this
                            ,trigger: 'click'
                            ,type: 'time'
                            ,format: 'HH:mm'
                            ,ready: function(){
                                $(".layui-laydate-list li:last-child").hide();
                                var dateListLiObj = $(".layui-laydate-list li");
                                dateListLiObj.css('width','50%');
                                dateListLiObj.css('overflow','hidden');
                                dateListLiObj.find("p").css('padding-right','15px');
                                $(".layui-laydate-list ol").css('overflow-y','auto');
                                $(".layui-laydate-list ol li").css('cssText','width:100%;padding-left:0;line-height:30px;text-align:center;cursor:pointer;');
                            },done: function(value){
                                if (value == ''){
                                    $(this.elem).attr('class', 'td-input-border-r');
                                } else {
                                    $(this.elem).attr('class', 'td-input-border-g');
                                }
                            }
                        });
                    });

                } else {
                    layer.msg('上传失败', {icon: 2});
                }
            }
            , error: function (index, upload) {
                layer.msg('上传失败', {icon: 2});
            }
        });
        //保存
        $("#save").on('click', function () {
            var target = $('#repairData').find('.rdata');
            var trLen = target.length;
            if (trLen == 0) {
                layer.msg('请先上传维修数据', {icon: 2});
                return false;
            }
            var flag = true;
            target.each(function () {
                var num = $(this).find('td:first').html();
                var assnumCN = $(this).find("input[name='assnum']").attr('class');
                var applicant = $.trim($(this).find("input[name='applicant']").val());
                var applicant_day = $.trim($(this).find("input[name='applicant_day']").val());
                var applicant_time = $.trim($(this).find("input[name='applicant_time']").val());
                var faultProblem = $.trim($(this).find("input[name='faultProblem']").val());
                var breakdown = $.trim($(this).find("input[name='breakdown']").val());
                var response = $.trim($(this).find("input[name='response']").val());
                var responseDate = $.trim($(this).find("input[name='response_date']").val());
                var engineer = $.trim($(this).find("input[name='engineer']").val());
                var engineerTime = $.trim($(this).find("input[name='engineer_time']").val());
                var overdate_day = $.trim($(this).find("input[name='overdate_day']").val());
                var overdate_time = $.trim($(this).find("input[name='overdate_time']").val());
                var disposeDetail = $.trim($(this).find("input[name='dispose_detail']").val());
                var checkperson = $.trim($(this).find("input[name='checkperson']").val());
                var checkdate = $.trim($(this).find("input[name='checkdate']").val());
                //if (assnumCN == 'td-input-border-r') {
                //    flag = false;
                //    layer.msg('该编码的资产不存在（行号：' + num + '）', {icon: 2});
                //    return false;
                //}
                if (!applicant) {
                    flag = false;
                    $(this).find("input[name='applicant']").attr('class', 'td-input-border-r');
                    layer.msg('申报人不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!applicant_day) {
                    flag = false;
                    $(this).find("input[name='applicant_day']").attr('class', 'td-input-border-r');
                    layer.msg('申报日期不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!applicant_time) {
                    flag = false;
                    $(this).find("input[name='applicant_time']").attr('class', 'td-input-border-r');
                    layer.msg('申报时间不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!faultProblem) {
                    flag = false;
                    $(this).find("input[name='faultProblem']").attr('class', 'td-input-border-r');
                    layer.msg('故障问题不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!breakdown) {
                    flag = false;
                    $(this).find("input[name='breakdown']").attr('class', 'td-input-border-r');
                    layer.msg('故障描述不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!response) {
                    flag = false;
                    $(this).find("input[name='response']").attr('class', 'td-input-border-r');
                    layer.msg('接单人不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!responseDate) {
                    flag = false;
                    $(this).find("input[name='response_date']").attr('class', 'td-input-border-r');
                    layer.msg('接单时间不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!engineer) {
                    flag = false;
                    $(this).find("input[name='engineer']").attr('class', 'td-input-border-r');
                    layer.msg('维修工程师不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!engineerTime) {
                    flag = false;
                    $(this).find("input[name='engineer_time']").attr('class', 'td-input-border-r');
                    layer.msg('开始维修时间不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!overdate_day) {
                    flag = false;
                    $(this).find("input[name='overdate_day']").attr('class', 'td-input-border-r');
                    layer.msg('维修结束日期不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!overdate_time) {
                    flag = false;
                    $(this).find("input[name='overdate_time']").attr('class', 'td-input-border-r');
                    layer.msg('维修结束时间不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!disposeDetail) {
                    flag = false;
                    $(this).find("input[name='dispose_detail']").attr('class', 'td-input-border-r');
                    layer.msg('处理详情不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!checkperson) {
                    flag = false;
                    $(this).find("input[name='checkperson']").attr('class', 'td-input-border-r');
                    layer.msg('验收人不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
                if (!checkdate) {
                    flag = false;
                    $(this).find("input[name='checkdate']").attr('class', 'td-input-border-r');
                    layer.msg('验收时间不能为空（行号：' + num + '）', {icon: 2});
                    return false;
                }
            });
            var asssetsName = '';
            var assetsNum = '';
            var assorignum = '';
            var assetsModel = '';
            var applicantName = '';
            var applicant_day = '';
            var applicant_time = '';
            var applicantTel = '';
            var assetsFaultProblem = '';
            var assetsBreakdown = '';
            var responseName = '';
            var responseTime = '';
            var responseTel = '';
            var engineerName = '';
            var engineerTel = '';
            var assistName = '';
            var assistTel = '';
            var engineerTime = '';
            var overdate_day = '';
            var overdate_time = '';
            var actualPrice = '';
            var disposeDetail = '';
            var checkName = '';
            var checkTime = '';
            var checkRemark = '';
            var assetsParts = '';
            if (flag) {
                var flag1 = true;
                $("input[name='assets']").each(function () {
                    if ($.trim($(this).val())) {
                        asssetsName += $(this).val() + ',';
                    } else {
                        asssetsName += '--' + ',';
                    }
                });
                $("input[name='assnum']").each(function (k) {
                    // console.log(k,$(this).val())
                    if (!$(this).val() && $(this).attr('class') === 'td-input-border-g') {
                        layer.msg('设备编号不能为空（行号：' + (k + 1) + '）', {icon: 2});
                        flag1 = false;
                        return false;
                    } else {
                        assetsNum += $(this).val() + ',';
                    }
                });
                $("input[name='applicant']").each(function () {
                    applicantName += $(this).val() + ',';
                });
                $("input[name='applicant_day']").each(function () {
                    applicant_day += $(this).val() + ',';
                });
                $("input[name='applicant_time']").each(function () {
                    applicant_time += $(this).val() + ',';
                });
                $("input[name='applicant_tel']").each(function () {
                    if ($.trim($(this).val())) {
                        applicantTel += $(this).val() + ',';
                    } else {
                        applicantTel += '--' + ',';
                    }
                });
                $("input[name='faultProblem']").each(function () {
                    assetsFaultProblem += $(this).val() + ',';
                });
                $("input[name='breakdown']").each(function () {
                    assetsBreakdown += $(this).val() + ',';
                });
                $("input[name='response']").each(function () {
                    responseName += $(this).val() + ',';
                });
                $("input[name='response_date']").each(function () {
                    responseTime += $(this).val() + ',';
                });
                $("input[name='response_tel']").each(function () {
                    if ($.trim($(this).val())) {
                        responseTel += $(this).val() + ',';
                    } else {
                        responseTel += '--' + ',';
                    }
                });
                $("input[name='engineer']").each(function () {
                    engineerName += $(this).val() + ',';
                });
                $("input[name='engineer_tel']").each(function () {
                    if ($.trim($(this).val())) {
                        engineerTel += $(this).val() + ',';
                    } else {
                        engineerTel += '--' + ',';
                    }
                });
                $("input[name='assist_engineer']").each(function () {
                    if ($.trim($(this).val())) {
                        assistName += $(this).val() + ',';
                    } else {
                        assistName += '--' + ',';
                    }
                });
                $("input[name='assist_engineer_tel']").each(function () {
                    if ($.trim($(this).val())) {
                        assistTel += $(this).val() + ',';
                    } else {
                        assistTel += '--' + ',';
                    }
                });
                $("input[name='engineer_time']").each(function () {
                    engineerTime += $(this).val() + ',';
                });
                $("input[name='overdate_day']").each(function () {
                    overdate_day += $(this).val() + ',';
                });
                $("input[name='overdate_time']").each(function () {
                    overdate_time += $(this).val() + ',';
                });
                $("input[name='actual_price']").each(function () {
                    if ($.trim($(this).val())) {
                        actualPrice += $(this).val() + ',';
                    } else {
                        actualPrice += '--' + ',';
                    }
                });
                $("input[name='dispose_detail']").each(function () {
                    if ($.trim($(this).val())) {
                        disposeDetail += $(this).val() + ',';
                    } else {
                        disposeDetail += '--' + ',';
                    }
                });
                $("input[name='checkperson']").each(function () {
                    checkName += $(this).val() + ',';
                });
                $("input[name='checkdate']").each(function () {
                    checkTime += $(this).val() + ',';
                });
                $("input[name='check_remark']").each(function () {
                    if ($.trim($(this).val())) {
                        checkRemark += $(this).val() + ',';
                    } else {
                        checkRemark += '--' + ',';
                    }
                });
                if (flag1) {
                    var url = admin_name+'/Repair/batchAddRepair.html';
                    var data = {};
                    data.type = 'save';
                    data.assets = asssetsName;
                    data.assnum = assetsNum;
                    data.assorignum = assorignum;
                    data.model = assetsModel;
                    data.applicant = applicantName;
                    data.applicant_day = applicant_day;
                    data.applicant_time = applicant_time;
                    data.applicant_tel = applicantTel;
                    data.faultProblem = assetsFaultProblem;
                    data.breakdown = assetsBreakdown;
                    data.response = responseName;
                    data.response_date = responseTime;
                    data.response_tel = responseTel;
                    data.engineer = engineerName;
                    data.engineer_tel = engineerTel;
                    data.assist_engineer = assistName;
                    data.assist_engineer_tel = assistTel;
                    data.engineer_time = engineerTime;
                    data.overdate_day = overdate_day;
                    data.overdate_time = overdate_time;
                    data.actual_price = actualPrice;
                    data.dispose_detail = disposeDetail;
                    data.checkperson = checkName;
                    data.checkdate = checkTime;
                    data.check_remark = checkRemark;
                    var pflag = true;
                    var cflag = true;
                    var cflag_1 = true;
                    var nflag = true;
                    var nflag_1 = true;
                    var subparts3 = {};
                    target.each(function (key) {
                        var assetsParts = $(this).find('.assetsParts').find('tr');
                        var targetassnum = $(this).find("input[name='assnum']").val();
                        var p = '';
                        var m = '';
                        var c = '';
                        var n = '';
                        var parray = {};
                        assetsParts.each(function () {
                            if (!$.trim($(this).find("input[name='parts']").val())) {
                                pflag = false;
                                $(this).find("input[name='parts']").attr('class', 'td-input-border-r');
                                return false;
                            } else {
                                p += $.trim($(this).find("input[name='parts']").val()) + ',';
                            }
                            if (!$.trim($(this).find("input[name='part_model']").val())) {
                                m += '--' + ',';
                            } else {
                                m += $.trim($(this).find("input[name='part_model']").val()) + ',';
                            }
                            if (!$.trim($(this).find("input[name='part_price']").val())) {
                                // cflag = false;
                                // $(this).find("input[name='part_price']").attr('class', 'td-input-border-r');
                                // return false;
                            } else {
                                var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                                if (!reg.test($(this).find("input[name='part_price']").val())) {
                                    $(this).find("input[name='part_price']").attr('class', 'td-input-border-r');
                                    cflag_1 = false;
                                    return false;
                                } else {
                                    c += $.trim($(this).find("input[name='part_price']").val()) + ',';
                                }
                            }
                            if (!$.trim($(this).find("input[name='part_num']").val())) {
                                nflag = false;
                                $(this).find("input[name='part_num']").attr('class', 'td-input-border-r');
                                return false;
                            } else {
                                var reg = /^[0-9]*[1-9][0-9]*$/;
                                if (!reg.test($.trim($(this).find("input[name='part_num']").val()))) {
                                    $(this).find("input[name='part_num']").attr('class', 'td-input-border-r');
                                    nflag_1 = false;
                                    return false;
                                } else {
                                    n += $.trim($(this).find("input[name='part_num']").val()) + ',';
                                }
                            }
                        });
                        parray['parts'] = p;
                        parray['part_model'] = m;
                        parray['part_price'] = c;
                        parray['part_num'] = n;
                        if (!subparts3.hasOwnProperty(targetassnum)) {
                            subparts3[targetassnum]=[];
                            subparts3[targetassnum][key] = parray;
                        }else{
                            subparts3[targetassnum][key] = parray;
                        }
                         // console.log(subparts3[targetassnum]);
                    });
                    if (!pflag) {
                        layer.msg('配件/服务名称不能为空', {icon: 2});
                        return false;
                    }
                    if (!cflag) {
                        layer.msg('单价不能为空', {icon: 2});
                        return false;
                    }
                    if (!cflag_1) {
                        layer.msg('请填写合理的价格', {icon: 2});
                        return false;
                    }
                    if (!nflag) {
                        layer.msg('数量不能为空', {icon: 2});
                        return false;
                    }
                    if (!nflag_1) {
                        layer.msg('请填写正整数数量', {icon: 2});
                        return false;
                    }
                    data.assetsParts = subparts3;
                    $.ajax({
                        timeout: 500000,
                        type: "POST",
                        url: url,
                        data: data,
                        async: false,//取消异步请求
                        beforeSend: beforeSend,
                        success: function (data) {
                            if (data.status == 1) {
                                layer.msg(data.msg, {icon: 1});
                                parts = data.parts;
                                addItem(data);
                            } else {
                                layer.msg(data.msg, {icon: 2});
                            }
                        },
                        complete: complete,
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2});
                        }
                    });
                }
            }
        });
    });
    exports('repair/repair/importRepair', {});
});
function change(e) {
    if (!$.trim($(e).val())) {
        $(e).attr('class', 'td-input-border-r');
    } else {
        $(e).attr('class', 'td-input-border-g');
    }
}
function delRow(e) {
    $(e).parent().parent().remove();
}
function checkRepeat(e) {
    var type = $(e).attr('name');
    var dataValue = $(e).attr('data-value');
    var val = $(e).val();
    if (val != dataValue) {
        if (type == 'assnum') {
            if ($.inArray(String(val), assnum) == -1) {
                $(e).attr('class', 'td-input-border-r');
            } else {
                $(e).attr('class', 'td-input-border-g');
            }
            $(e).attr('data-value', val);
            $(e).val(val);
        }
    }
}
function addItem(res) {
    var html = '';
    var arr = res.data;
    var len = res.data.length;
    for (var i = 0; i < len; i++) {
        html += '<tr class="rdata">';
        html += '<td style="text-align: center">' + (i + 1) + '</td>';
        html += '<td><input type="text" name="assets" value="' + arr[i]['assets'] + '" class="td-input-border-g"/></td>';
        if (arr[i]['assnum'] == undefined) {
            html += '<td><input type="text" name="assnum" data-value="" value="/" class="td-input-border-g" onblur="checkRepeat(this)"/></td>';
        } else {
            html += '<td><input type="text" name="assnum" data-value="' + arr[i]['assnum'] + '" value="' + arr[i]['assnum'] + '" class="td-input-border-g" onblur="checkRepeat(this)"/></td>';
        }
        html += '<td><input type="text" name="applicant" value="' + arr[i]['applicant'] + '" onblur="change(this)" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="applicant_day" value="' + arr[i]['applicant_day'] + '"  placeholder="请选择申报日期" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dayFormat" ></td>';
        html += '<td><input type="text" name="applicant_time" value="' + arr[i]['applicant_time'] + '"  placeholder="请选择申报时间" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dateTimeFormat" ></td>';
        html += '<td><input type="text" name="applicant_tel" value="' + arr[i]['applicant_tel'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="faultProblem" value="' + arr[i]['faultProblem'] + '" onblur="change(this)" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="breakdown" value="' + arr[i]['breakdown'] + '" onblur="change(this)" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="response" value="' + arr[i]['response'] + '" onblur="change(this)" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="response_date" id="response_date" value="' + arr[i]['response_date'] + '"  placeholder="请选择接单时间" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dateTimeFormat" ></td>';
        html += '<td><input type="text" name="response_tel" value="' + arr[i]['response_tel'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="engineer" value="' + arr[i]['engineer'] + '" onblur="change(this)" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="engineer_tel" value="' + arr[i]['engineer_tel'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="assist_engineer" value="' + arr[i]['assist_engineer'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="assist_engineer_tel" value="' + arr[i]['assist_engineer_tel'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="engineer_time" id="engineer_time" value="' + arr[i]['engineer_time'] + '"  placeholder="请选择开始维修时间" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dateTimeFormat" ></td>';
        html += '<td><input type="text" name="overdate_day" id="overdate" value="' + arr[i]['overdate_day'] + '"  placeholder="请选择维修结束日期" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dayFormat" ></td>';
        html += '<td><input type="text" name="overdate_time" id="overdate" value="' + arr[i]['overdate_time'] + '"  placeholder="请选择维修结束时间" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dateTimeFormat" ></td>';
        html += '<td><input type="text" name="actual_price" value="' + arr[i]['actual_price'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="dispose_detail" value="' + arr[i]['dispose_detail'] + '" onblur="change(this)" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="checkperson" value="' + arr[i]['checkperson'] + '" class="td-input-border-g"/></td>';
        html += '<td><input type="text" name="checkdate" id="checkdate" value="' + arr[i]['checkdate'] + '"  placeholder="请选择验收日期" autocomplete="off" style="cursor: pointer;width:130px;height: 27px;" class="layui-input dayFormat" ></td>';
        var pj = '';
        for (var j = 0; j < parts.length; j++) {
            if (parts[j]['assnum'] == arr[i]['assnum']&&(i+2==parts[j]['bindung']||parts[j]['bindung']=="")) {
                var partName = parts[j]["parts"];
                var partModel = parts[j]["part_model"];
                var partPrice = parts[j]["part_price"];
                var partNum = parts[j]["part_num"];
                pj += '<tr><td><input type="text" name="parts" value="' + partName + '" onblur="change(this)" class="td-input-border-g"/></td><td><input type="text" name="part_model" value="' + partModel + '" class="td-input-border-g"/></td><td><input type="text" name="part_price" value="' + partPrice + '" onblur="change(this)" class="td-input-border-g"/></td><td><input type="text" name="part_num" onblur="change(this)" value="' + partNum + '" class="td-input-border-g"/></td></tr>';
            }
        }
        html += '<td><table style="width:600px;"><thead><tr><th style="width: 240px;text-align: center;">配件/服务名称</th><th style="width: 180px;text-align: center;">配件型号</th><th style="width: 100px;text-align: center;">单价(元)</th><th style="width: 80px;text-align: center;">数量</th></tr></thead><tbody class="assetsParts">' + pj + '</tbody></table></td>';
        html += '<td><button class="layui-btn layui-btn-small layui-btn-danger" onclick="delRow(this)">删除</button></td>';
        html += '</tr>';
    }
    $('#repairData').html('');
    $('#repairData').append(html);
}
