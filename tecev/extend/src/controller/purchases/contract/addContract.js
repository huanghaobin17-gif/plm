layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'upload', 'tipsType', 'formSelects','suggest'], function () {
        var layer = layui.layer,
            formSelects = layui.formSelects,
            form = layui.form,
            laydate = layui.laydate,
            upload = layui.upload,
            tipsType = layui.tipsType,
            suggest = layui.suggest,
            $ = layui.jquery;
        //先更新页面部分需要提前渲染的控件
        form.render();
        tipsType.choose();

        //初始化搜索建议插件
        suggest.search();

        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));

        laydate.render(dateConfig('#addContractSign_date'));
        laydate.render(dateConfig('#addContractEnd_date'));
        laydate.render(dateConfig('#addContractCheck_date'));
        laydate.render(dateConfig('#addContractGuarantee_date'));

        form.verify({
            tel: function (value) {
                if (value !== '') {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '号码首尾不能出现下划线\'_\'';
                    }
                    if (!checkTel(value)) {
                        return '请正确填写号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                    }
                }
            },
            price: function (value) {
                if (!check_price(value)) {
                    return '请输入正确的合同金额';
                }
            }
        });


        //监听提交
        form.on('submit(add)', function (data) {
            var erro = false;
            var bug_msg = '';
            var params = data.field;
            //获取分期信息
            var phaseDataTr = $('.phaseDataTr');
            if (phaseDataTr.length > 0) {
                params.pay_amount='';
                params.estimate_pay_date='';
                params.real_pay_date='';
                params.phase='';
                params.phase_sum=0;
                $.each(phaseDataTr, function (key, value) {
                    var phase = $(value).find('.phaseTd').html();
                    var pay_amount = $(value).find('input[name="pay_amount"]').val();
                    var estimate_pay_date = $(value).find('input[name="estimate_pay_date"]').val();
                    var real_pay_date = $(value).find('input[name="real_pay_date"]').val();
                    if (!pay_amount) {
                        bug_msg = '第' + phase + '期合同付款信息 付款金额未补充';
                        erro=true;
                        return true;
                    }
                    if (!estimate_pay_date) {
                        bug_msg = '第' + phase + '期合同付款信息 预计付款时间未补充';
                        erro=true;
                        return true;
                    }
                    params.phase_sum+=Number(pay_amount);
                    params.pay_amount += pay_amount + '|';
                    params.phase += phase + '|';
                    params.estimate_pay_date += estimate_pay_date + '|';
                    params.real_pay_date += real_pay_date + '|';
                });
                if (erro===true) {
                    layer.msg(bug_msg, {icon: 2, time: 3000});
                    return false;
                }
                if(params.phase_sum!==Number(params.contract_amount)){
                    layer.msg('合同付款明细总金额需要等于合同金额,不需要录入的请移除', {icon: 2, time: 3000});
                    return false;
                }
            }
            //获取上传文件信息
            var fileDataTr=$('.fileDataTr');
            if (fileDataTr.length > 0) {
                params.file_name='';
                params.save_name='';
                params.file_type='';
                params.file_size='';
                params.file_url='';
                $.each(fileDataTr, function (key, value) {
                    params.file_name += $(value).find('input[name="file_name"]').val() + '|';
                    params.save_name += $(value).find('input[name="save_name"]').val() + '|';
                    params.file_type +=  $(value).find('input[name="file_type"]').val() + '|';
                    params.file_size +=  $(value).find('input[name="file_size"]').val() + '|';
                    params.file_url +=  $(value).find('input[name="file_url"]').val() + '|';
                });
            }
            if (erro===true) {
                layer.msg(bug_msg, {icon: 2, time: 3000});
                return false;
            }
            console.log(params);
            submit($, params, addContractUrl);
            return false;
        });


        var dateNum = 0;
        var phase = 1;
        //点击添加期数
        $(document).on('click', '#addPhase', function () {
            var notPhaseDataTr = $('.notPhaseDataTr');
            if (notPhaseDataTr.length > 0) {
                notPhaseDataTr.remove();
            }
            var addPhaseTbody = $('.addPhaseTbody');
            var html = '<tr class="phaseDataTr">';
            html += ' <td class="phaseTd">' + phase + '</td>';
            html += '<td class="pay_amount_td"><input type="text" name="pay_amount" autocomplete="off" placeholder="" value="" class="layui-input"></td>';
            html += '<td><input type="text" name="estimate_pay_date" autocomplete="off" placeholder="请选择预计付款日期" class="layui-input" id="estimate_pay_date' + dateNum + '"></td>';
            html += '<td><input type="text" name="real_pay_date" autocomplete="off" placeholder="请选择付款日期" class="layui-input" id="real_pay_date' + dateNum + '"></td>';
            html += '<td class="pay_status">未付款</td>';
            html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_pay">移除</div></td>';
            addPhaseTbody.append(html);
            laydate.render({
                elem: '#estimate_pay_date' + dateNum
                , calendar: true
                , min: '1'
                , done: function (value, date, endDate) {

                }
            });
            laydate.render({
                elem: '#real_pay_date' + dateNum
                , calendar: true
                , min: '1'
                , done: function (value) {
                    var dateTd = $(this.elem[0]).parent('td');
                    var pay_amount = dateTd.siblings('.pay_amount_td').find('input[name="pay_amount"]').val();
                    if (value !== '' && pay_amount > 0) {
                        dateTd.siblings('.pay_status').html('<span style="color:green">已付款</span>');
                    }
                }
            });
            dateNum++;
            phase++;
        });
        //移除期次
        $(document).on('click', '.del_pay', function () {
            var thisTr = $(this).parents('tr');
            var addPhaseTbody = $('.addPhaseTbody');
            var phaseDataTr = $('.phaseDataTr');
            thisTr.remove();
            if (addPhaseTbody.find('tr').length > 0) {
                $.each(addPhaseTbody.find('tr'), function (key, value) {
                    console.log($(value).find('.phaseTd').html(key + 1));
                })
            } else {
                addPhaseTbody.html('<tr class="notPhaseDataTr"><td colspan="6" style="text-align: center!important;">暂无数据</td></tr>');
            }
            phase--;
            layer.msg('移除成功', {icon: 1}, 1000);
        });

        //上传文件
        uploadFile = upload.render({
            elem: '#addContractFile'  //绑定元素
            , url: addContractUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = $('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = $('.addFileTbody');
                    var input = '<input type="hidden" name="file_name" value="' + res.formerly + '">';
                    input += '<input type="hidden" name="save_name" value="' + res.title + '">';
                    input += '<input type="hidden" name="file_type" value="' + res.ext + '">';
                    input += '<input type="hidden" name="file_size" value="' + res.size + '">';
                    input += '<input type="hidden" name="file_url" value="' + res.path + '">';
                    var html = '<tr class="fileDataTr">';
                    html += '<td class="fileName">' + input + res.formerly + '</td>';
                    html += '<td class="addFileuser">' + res.adduser + '</td>';
                    html += '<td class="addFileTime">' + res.thisTime + '</td>';
                    html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
                    addFileTbody.append(html);
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });
        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = $('.addFileTbody');
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
        });
        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).siblings('input[name="path"]').val();
            var name = $(this).siblings('input[name="name"]').val();
            var ext = $(this).siblings('input[name="ext"]').val();
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
                    content: [url + '?path=' + path + '&filename=' + name + '.' + ext]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
        });
    });
    exports('controller/purchases/contract/addContract', {});
});
