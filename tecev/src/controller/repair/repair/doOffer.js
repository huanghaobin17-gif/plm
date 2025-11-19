//被移除的第三方ID
var delOffid = [];
//被移除的第三方物料ID
var delRepopid = [];
var form;
layui.define(function(exports){
    layui.use(['layer', 'form'], function () {
        form = layui.form, $ = layui.jquery,layer = layui.layer;
        //验证
        form.verify({
            offer_company: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '公司名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '公司名称不能全为数字';
                }
            },
            offer_contacts: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '联系人首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '联系人不能全为数字';
                }
            },
            tel_phone: function (value, item) { //value：表单的值、item：表单的DOM对象
                if(!checkTel(value)){
                    return '请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                }
            },
            price: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!/^(([1-9]\d*)|\d)(\.\d{1,3})?$/.test(value)) {
                    return '请输入正确的金额';
                }
            }
        });

        //清空输入框数据(第三方重置按钮)
        $('.offerReset').on('click', function () {
            $("select[name='offer_company']").val('');
            $("input[name='offer_contacts']").val('');
            $("input[name='telphone']").val('');
            $("input[name='total_price']").val('');
            $("input[name='invoice']").val('');
            $("input[name='cycle']").val('');
            $("input[name='remark']").val('');
            form.render();
            return false;
        });

        $(document).on('click','.downFile',function () {
            var params={};
            params.path= $(this).data('path');
            params.filename=$(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data:params,
                method:'POST'
            });
            return false;
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
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [url +'?path=' + path + '&filename=' + name]
            });
            return false;
        });


        //新增报价公司
        form.on('submit(addCompany)', function (data) {
            var repid = $("input[name='repid']").val();
            var table_tbody = $('.offerTbody').find('tbody.offer_tbody');
            params = data.field;
            params.repid = repid;
            if (!params.offer_company) {
                layer.msg("请选择报价公司", {icon: 2, time: 1000});
                return false;
            }else {
                var existOlisd = params.offer_company;
                $.each(companyInfo,function(k,v){
                    if (params.offer_company == v.olsid){
                        params.offer_company = v.sup_name;
                    }
                })
            }
            if (!params.offer_contacts) {
                layer.msg("请填写报价公司联系人！", {icon: 2, time: 1000});
                return false;
            }
            if (!params.telphone) {
                layer.msg("请填写报价公司联系人方式！", {icon: 2, time: 1000});
                return false;
            } else {
                if (!checkTel(params.telphone)) {
                    layer.msg("报价公司联系人方式格式不正确，请填写正确的手机号或座机号码！", {icon: 2, time: 2000});
                    return false;
                }
            }
            if (!params.total_price) {
                layer.msg("请填写服务金额！", {icon: 2, time: 1000});
                return false;
            }else {
                var reg = /^\d+(?=\.{0,1}\d+$|$)/;
                if(!reg.test(params.total_price)){
                    layer.msg("服务金额格式不正确！", {icon: 2, time: 1000});
                    return false;
                }
            }
            if (!params.invoice) {
                layer.msg("请填写发票！", {icon: 2, time: 1000});
                return false;
            }
            if (!params.cycle) {
                layer.msg("请填写到货/服务周期！", {icon: 2, time: 1000});
                return false;
            }
            params.offer_company = $.trim(params.offer_company);
            var html = '<tr class="offid_tr" data-offid="" data-olsid="'+params.olsid+'">';
            html += '<td class="company_td" style="text-align: center; vertical-align: middle; ">' + params.offer_company + '</td>';
            html += '<td class="contacts_td" style="text-align: center; padding: 0;">' + params.offer_contacts + '</td>';
            html += '<td class="telphone_td" style="text-align: center; padding: 0;">' + params.telphone + '</td>';
            html += '<td class="total_price no-padding-td" style="text-align: center; vertical-align: middle;"><input type="text" name="price" class="layui-input" value="'+params.total_price+'"></td>';
            html += '<td class="invoice_td" style="text-align: center; padding: 0;">' + params.invoice + '</td>';
            html += '<td class="cycle_td" style="text-align: center; padding: 0;">' + params.cycle + '</td>';
            html += '<td style="text-align: center; vertical-align: middle; "></td>';
            html += '<td style="text-align: center; padding: 0;"></td>';
            html += '<td class="remark_td" style="text-align: center; padding: 0;">' + params.remark + '</td>';
            html += '<td style="width: 30px;text-align: center"><input name="last_decisioin" type="radio" value="' + params.offer_company + '"></td>';
            html += '<td style="width:70px;text-align: center; vertical-align: middle; "><button type="button" class="layui-btn layui-btn-xs layui-btn-danger remove_company" ><i class="layui-icon">&#xe640;</i></button></td>';
            html += '</tr>';
            table_tbody.prepend(html);
            layer.msg('添加成功', {icon: 1}, 1000);
            //清除一条新添加的公司
            var companyHtml = '<option value="">请选择公司</option>';
            $.each(companyInfo,function(k,v){
                if (existOlisd == v.olsid){
                    v.status = 0;
                }
            });
            $.each(companyInfo,function(k,v){
                if (v.status == 1){
                    companyHtml += '<option value="'+ v.olsid+'">'+ v.sup_name+'</option>'
                }
            });
            $("select[name='offer_company']").html(companyHtml);
            //清空输入框数据
            $("select[name='offer_company']").val('');
            $("input[name='olsid']").val('');
            $("input[name='offer_contacts']").val('');
            $("input[name='telphone']").val('');
            $("input[name='total_price']").val('');
            $("input[name='invoice']").val('');
            $("input[name='cycle']").val('');
            $("input[name='remark']").val('');
            form.render();
            return false;
        });

        //监听提交-第三方报价
        form.on('submit(saveOffer)', function () {
            var params={};
            if ($('.company_td').length === 0) {
                layer.msg("无报价公司！", {icon: 2}, 1000);
                return false;
            }
            var last_decisioin=$("input[name='last_decisioin']:checked");
            params.last_decisioin = last_decisioin.val();
            if(params.last_decisioin=== '' || params.last_decisioin === undefined){
                layer.msg("请选择最终报价厂家！", {icon: 2}, 1000);
                return false;
            }else{
                var price=last_decisioin.parent('td').parent('tr').find('input[name="price"]').val();
                var reg = /^\d+(?=\.{0,1}\d+$|$)/;

                if(Number(price)>0){
                    console.log(price);
                    if(!reg.test(price)){
                        layer.msg("最终厂商服务金额有误！", {icon: 2, time: 1000});
                        return false;
                    }
                }else{
                    layer.msg("请补充最终厂商服务金额！", {icon: 2, time: 1000});
                    return false;
                }
            }
            layer.open({
                content: '一旦确认无法更改，确认并结束报价吗？'
                ,btn: ['确认', '取消']
                ,yes: function(index, layero){
                    var companyOlsid = '';
                    var companyName = '';
                    var contracts = '';
                    var telphone = '';
                    var offid = '';
                    var invoice = '';
                    var cycle = '';
                    var totalPrice = '';
                    var remark = '';
                    $('.offid_tr').each(function () {
                        companyOlsid += $(this).data('olsid') + '|';
                        offid += $(this).data('offid') + '|';
                    });
                    $('.company_td').each(function () {
                        companyName += $(this).html() + '|';
                    });
                    $('.contacts_td').each(function () {
                        contracts += $(this).html() + '|';
                    });
                    $('.telphone_td').each(function () {
                        telphone += $(this).html() + '|';
                    });
                    $('.invoice_td').each(function () {
                        invoice += $(this).html() + '|';
                    });
                    $('.cycle_td').each(function () {
                        cycle += $(this).html() + '|';
                    });
                    $('input[name="price"]').each(function () {
                        totalPrice += $(this).val() + '|';
                    });
                    $('.remark_td').each(function () {
                        remark += $(this).html() + '|';
                    });
                    //记录厂商ID
                    companyNameArr = companyName.split('|');
                    $.each(companyInfo,function(k,v){
                        $.each(companyNameArr,function(k1,v1){
                            if (v.sup_name == v1){
                                companyOlsid += v.olsid + '|'
                            }
                        })
                    });
                    params.olsid = companyOlsid;
                    params.companyName = companyName;
                    params.offid = offid;
                    params.contracts = contracts;
                    params.telphone = telphone;
                    params.invoice = invoice;
                    params.totalPrice = totalPrice;
                    params.cycle = cycle;
                    params.decision_reasion=$('textarea[name="decision_reasion"]').val();
                    params.remark = remark;
                    params.delRepopid = delRepopid;
                    params.delOffid = delOffid;
                    params.repid=$('input[name="repid"]').val();
                    submit($, params, $('input[name="action"]').val());
                    return false;
                }
                ,btn2: function(index, layero){
                    //按钮【按钮二】的回调
                    //return false 开启该代码可禁止点击该按钮关闭
                }
                ,cancel: function(){
                    //右上角关闭回调
                }
            });
            return false;
        });



        //第三方选择公司联动填充输入框
        form.on('select(companyInfo)', function(data){
            var companyData = {};
            $.each(companyInfo,function(k,v){
                if (data.value == v.olsid){
                    companyData.olsid = v.olsid;
                    companyData.salesman_name = v.salesman_name;
                    companyData.salesman_phone = v.salesman_phone;
                }
            });
            $("input[name='olsid']").val(companyData.olsid);
            $("input[name='offer_contacts']").val(companyData.salesman_name);
            $("input[name='telphone']").val(companyData.salesman_phone);
        });

        //播放停止语音
        var audio = document.getElementById("audio");
        if(audio){
            var btn = document.getElementById("media");
            btn.onclick = function () {
                if (audio.paused) { //判断当前的状态是否为暂停，若是则点击播放，否则暂停
                    audio.play();
                }else{
                    audio.pause();
                }
            };
        }

        $("#showImages").click(function () {
            var result = {};
            result.start = 0;
            result.data = [];
            $(".imageUrl").each(function (k, v) {
                var imageUrlObj = {};
                imageUrlObj.src = $(v).val();
                imageUrlObj.thumb = $(v).val();
                result.data.push(imageUrlObj);
            });
            //显示相册层
            layer.photos({
                photos: result
                , anim: 5
                , maxmin: false
            });
        });

    });
    exports('controller/repair/repair/doOffer', {});
});



//第三方报价 点击移除公司
$(document).on('click', '.remove_company', function () {
    var a_this = $(this).parent().parent();
    layer.confirm('确定移除此公司报价包括已添加的物料信息？', {icon: 3, title: '移除提示'}, function (index) {
        //移除
        a_this.remove();
        if (a_this.data('offid') > 0) {
            //将数据库有记录ID添加到数组中
            delOffid.push(a_this.data('offid'));
        }
        //当添加过配件物料并且默认公司名称是删除的这一家公司 则隐藏配件信息form
        if (a_this.find('.company_td').html() == $('input[name="Company"]').val()) {
            $('.matter').hide();
        }
        layer.close(index);
        //清除一条新添加的公司
        var companyHtml = '<option value="">请选择公司</option>';
        $.each(companyInfo,function(k,v){
            if (a_this.find('.company_td').html() == v.sup_name){
                v.status = 1;
            }
        });
        $.each(companyInfo,function(k,v){
            if (v.status == 1){
                companyHtml += '<option value="'+ v.olsid+'">'+ v.sup_name+'</option>'
            }
        });
        $("select[name='offer_company']").html(companyHtml);
        layui.form.render();
    });
});