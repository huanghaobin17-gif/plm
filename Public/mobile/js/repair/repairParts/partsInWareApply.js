layui.use(['form'], function () {
    var form = layui.form,detailObj = $(".detail");

    //初始化下方导航栏菜单
    menuListSpread();
    //操作checkbox
    detailObj.click(function(){
        var isCheckedDiv = $(this).find('.layui-form-checkbox i');
        isCheckedDiv.click();
    });
    detailObj.find(".layui-form-checkbox").click(function(e){
        e.stopPropagation();
    });
    form.on('checkbox(choose)', function(data){
        var checked = data.elem.checked,
            thisDomBorder = $(data.elem).parents(".content"),
            thisInputDiv = $(data.elem).parents(".detail").siblings(".layui-form-item");
        if (checked == true){
            thisInputDiv.show();
            thisDomBorder.css("border-color","#5FB878");
        }else {
            thisInputDiv.hide();
            thisDomBorder.css("border-color","#E5E5E5");
        }

    });

    form.on('submit(submit)', function(){
        var params = {};
        params.action = 'partsInWareApply';
        params.inwareid = $('input[name="inwareid"]').val();
        params.leader = $('input[name="leader"]').val();
        params.supplier_id = $('input[name="supplier_id"]').val();
        params.supplier_name = $('input[name="supplier_name"]').val();
        params.addtime = $('input[name="addtime"]').val();
        params.price = $('input[name="price"]').val();
        if(params.price <= 0){
            $.toptip('请填写的合理价格', 'error');
            return false;
        }
        params.parts = $('input[name="parts"]').val();
        params.parts_model = $('input[name="parts_model"]').val();
        params.sum = $.trim($('input[name="sum"]').val());
        params.apply_sum = $.trim($('input[name="min_sum"]').val());
        if(!params.sum){
            $.toptip('请填写入库数量', 'error');
            return false;
        }
        if(params.sum - params.apply_sum < 0){
            $.toptip('最少入库'+params.apply_sum+params.parts_model+'', 'error');
            return false;
        }
        submit(params, url,mobile_name+'/RepairParts/partsOutWare.html?repid='+repid);
        return false;
    });

    $(".priceEdit").click(function(){
        var thisEdit = $(this).parent().find("span").attr("class");
        if (thisEdit == 'price'){
            var priceObj = $(".price");
            //修改金额
            $.prompt({
                title: '修改配件单价',
                input: priceObj.html(),
                empty: false,
                onOK: function (v) {
                    //点击确认
                    if(v){
                        var reg=/^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                        if (!reg.test(v)) {
                            $.toptip('请输入合理的价格', 'error');
                            return false;
                        }
                    }
                    if (parseFloat(v) <= 0){
                        $.toptip('配件单价必须为正数', 'error');
                        return false;
                    }else {
                        $('input[name="price"]').val(v);
                        priceObj.html(v);
                        var price = parseFloat($(".price").html()),num = parseInt($('input[name="sum"]').val()),totalObj = $('input[name="tprice"]');
                        totalObj.val(price*num);
                    }
                }
            });
        }

    });
    //修改供应商 
    $(".supplierEdit").select({
        title: "选择供应商",
        closeText:'取消',
        items: sups,
        onClose:function(d){
            var value = d.data.values,title = d.data.titles;
            $("input[name='supplier_id']").val(value);
            $("input[name='supplier_name']").val(title);
            $(".supplier").html(title);
        }
    });
});

$("input[name='sum']").bind("input propertychange",function(){
    var price = parseFloat($(".price").html()),num = $(this).val(),check = /^[0-9]*[1-9][0-9]*$/,totalObj = $('input[name="tprice"]');
    if (!check.test(num)){
        $.toptip('采购入库数量必须为正整数', 'error');
        $(this).val("");
        totalObj.val("");
        return false;
    }else {
        num = parseInt(num);
        totalObj.val(price*num);
    }
});
