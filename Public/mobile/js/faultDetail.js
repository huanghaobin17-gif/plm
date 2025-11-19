/**
 * Created by chouxiaoya on 2017/3/18.
 */
$(function(){
   $("#getMore").click(function(){
       if($(".assHid").css("display")=="none"){
           //$(".assHid").css("display","block");
           $(".assHid").show();
           $("#getMore").html('收起');
           return false;
       }else{
           $(".assHid").css("display","none");
           //$(".assHid").hide(200);
           $("#getMore").html('更多基本信息');
           return false;
       }
   });
    $("#out").click(function(){
        $('#canNotRep').hide();
        $('#comIn').removeAttr('checked');
        $('#canRep').show();
        $('#out').attr('checked','checked');
    });
    $("#comIn").click(function(){
        $('#canRep').hide();
        $('#out').removeAttr('checked');
        $('#canNotRep').show();
        $('#comIn').attr('checked','checked');
    });
    $("#agree_2").click(function(){
        $("input[name='agree']").val('2');
    });
    $("#agree_1").click(function(){
        $("input[name='agree']").val('1');
    });
    $("#addPJ").click(function(){
        var div = '<div class="ui-form ui-border-t">';
        div += '<div class="ui-form-item ui-border-b">';
        div += '<label style="font-weight: 400">配件名称：</label>';
        div += '<input type="text" placeholder="配件名称" name="partsName[]"/>';
        div += '</div>';
        div += '<div class="ui-form-item ui-border-b">';
        div += '<label style="font-weight: 400">配件型号：</label>';
        div += '<input type="text" placeholder="配件型号" name="partsModel[]"/>';
        div += '</div>';
        div += '<div class="ui-form-item ui-border-b">';
        div += '<label style="font-weight: 400">配件数量：</label>';
        div += '<input type="text" placeholder="配件数量" name="partsNum[]"/>';
        div += '</div>';
        div += '<hr/>';
        div += '</div>';
        $("#pjParent").append(div);
    });
    $("#addPrice").click(function(){
        var index = parseInt($(this).val());
        var cname = $("#cname_"+index).val();
        cname = trim(cname,'g');
        if(cname == ''){
            var dia=$.dialog({
                title:'',
                content:'请填写公司名字',
                button:["确定"]
            });
            return false;
        }
        var cprice = $("#cprice_"+index).val();
        cprice = trim(cprice,'g');
        if(cprice == ''){
            var dia=$.dialog({
                title:'',
                content:'请填写报价',
                button:["确定"]
            });
            return false;
        }else{
            var fix_amountTest=/^(([1-9]\d*)|\d)(\.\d{1,2})?$/;
            if(fix_amountTest.test(cprice)==false){
                var dia=$.dialog({
                    title:'',
                    content:'请输入有效的金额',
                    button:["确定"]
                });
                return false;
            }
        }
        var tr =  '<tr><td><input type="text" placeholder="报价公司..." name="companyName[]" id="cname_'+(index+1)+'" style="width:130px;border: none;padding-left:5px;"/></td>';
            tr += '<td><input type="text" placeholder="价格..." name="price[]" id="cprice_'+(index+1)+'" style="width: 70px;border: none;padding-left: 5px;"/></td>';
            tr += '<td>';
            tr += '<a href="javascript:void(0);" class="blueButton">上传</a><input type="file" name="myFile[]" class="myFileUpload" value="" onchange="uploadFile(this)"/>';
            tr += '</td>';
            tr += '<td>';
            tr += '<input type="checkbox" name="accept[]" value="'+(index+1)+'" class="selectCompany"/>';
            tr += '</td></tr>';
        $("#rePendPrice").append(tr);
        $(this).val(index+1);
    });
    //$(".myFileUpload").change(function(){
    //    alert(5);
    //    var arrs=$(this).val().split('\\');
    //    console.log(arrs);
    //    var filename=arrs[arrs.length-1];
    //    //console.log(filename);
    //    $(this).attr('value',filename);
    //    $(this).prev().html('更改');
    //    //$("input[name='agree']").val('1');
    //});
});
//function checkDesc(){
//    var type = $("input[name='status']:checked").val();
//    if(type == 'out'){
//        var start = $("input[name='start']").val();
//        var end = $("input[name='end']").val();
//        if(!start){
//            alert('请选择外送开始时间');
//            return false;
//        }
//        if(!end){
//            alert('请选择外送结束时间');
//            return false;
//        }
//    }else{
//        var comin = $("input[name='com']").val();
//        if(!comin){
//            alert('请选择上门时间');
//            return false;
//        }
//    }
//    return true;
//}
function checkAgreeAp(){
    var status = $("input[name='status']:checked").val();
    if(status == 5){
        var because = $("textarea[name='disposeDetail']").val();
        because = trim(because,'g');
        if(because == ''){
            var dia=$.dialog({
                title:'',
                content:'请输入维修处理描述',
                button:["确定"]
            });
            return false;
        }
    }
    return true;
}
//去除输入框两边的空格
function trim(str,is_global)
{
    var result;
    result = str.replace(/(^\s+)|(\s+$)/g,"");
    if(is_global.toLowerCase()=="g")
    {
        result = result.replace(/\s/g,"");
    }
    return result;
}
function checkData(){
    if($("#rePendPrice input[type='checkbox']:checked").length == 0){
        var dia=$.dialog({
            title:'',
            content:'请选择其中一个方案',
            button:["确定"]
        });
        return false;
    }
    if($("#rePendPrice input[type='checkbox']:checked").length > 1){
        var dia=$.dialog({
            title:'',
            content:'请选择其中一个方案',
            button:["确定"]
        });
        return false;
    }
    var isPrice = $("#btnPrice").val();
    if(isPrice == undefined){alert(2);
        return true;
    }else{
        var index = $("#addPrice").val();
        for(var i=0;i<index+1;i++){
            var cname = $("#cname_"+i).val();
            cname = trim(cname,'g');
            if(cname == ''){
                var dia=$.dialog({
                    title:'',
                    content:'公司名称不能为空',
                    button:["确定"]
                });
                return false;
            }
            var cprice = $("#cprice_"+i).val();
            cprice = trim(cprice,'g');
            if(cprice == ''){
                var dia=$.dialog({
                    title:'',
                    content:'公司报价不能为空',
                    button:["确定"]
                });
                return false;
            }else{
                var fix_amountTest=/^(([1-9]\d*)|\d)(\.\d{1,2})?$/;
                if(fix_amountTest.test(cprice)==false){
                    var dia=$.dialog({
                        title:'',
                        content:'请输入有效的金额',
                        button:["确定"]
                    });
                    return false;
                }
            }
        }
    }
}
function uploadFile(e){
    var arrs=$(e).val().split('\\');
    console.log(arrs);
    var filename=arrs[arrs.length-1];
    console.log(filename);
    $(e).attr('value',filename);
    $(e).prev().html('更改');
}