/**
 * Created by liuwl on 2019/2/12 0012.
 */
var admin_name = '/A';
var mobile_name = '/M';
$(function () {
    var ua = navigator.userAgent.toLowerCase();
    var isWeixin = ua.indexOf('micromessenger') != -1;
    var isAndroid = ua.indexOf('android') != -1;
    var isIos = (ua.indexOf('iphone') != -1) || (ua.indexOf('ipad') != -1);
    if (!isWeixin) {
        document.head.innerHTML = '<title>抱歉，出错了</title><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0"><link rel="stylesheet" type="text/css" href="https://res.wx.qq.com/open/libs/weui/0.4.1/weui.css">';
        document.body.innerHTML = '<div class="weui_msg"><div class="weui_icon_area"><i class="weui_icon_info weui_icon_msg"></i></div><div class="weui_text_area"><h4 class="weui_msg_title">请在微信客户端打开链接</h4></div></div>';
    }
});

/**
 * 封装验证移动端页面数值的方法
 * @param v 判断的数字
 * @param dom 当前元素
 * @returns 提示语
 */
function checkLegalNumber(v, dom) {
    var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/; //正数及小数
    if (reg.test(v)) {
        if (v > $(dom).parents('.layui-colla-item').find('.range').html() * 10) {
            $(dom).parents('.layui-colla-content').addClass('layui-show');
            $(dom).focus();
            return '填写数值可能超出最大范围，请检查后填写';
        }
    }
}

function getThisDate() {
    var now=new Date();
    return now.getFullYear()+"-"+((now.getMonth()+1)<10?"0":"")+(now.getMonth()+1)+"-"+(now.getDate()<10?"0":"")+now.getDate();
}

/**
 * 获取数据
 */
function getData() {
    var numberType = $("input[type='number']"),
        textareaType = $('body').find('textarea'),
        nameData = [],
        textType = $("input[type='text']"),
        radioType = $("input[type='radio']:checked");
    //输入框所有
    numberType.each(function (k, v) {
        var vname = v.name;
        if (vname.lastIndexOf("[") != -1) {
            vname = vname.substr(0, vname.lastIndexOf("["));
            resultData[vname] = [];
        } else {
            resultData[vname] = 0;
        }
    });

    numberType.each(function (k, v) {
        var vname = v.name;
        if (vname.lastIndexOf("[") != -1) {
            vname = vname.substr(0, vname.lastIndexOf("["));
            if (typeof(nameData[vname]) !== 'undefined') {
                nameData[vname]++;
            } else {
                nameData[vname] = 0;
            }
            resultData[vname][nameData[vname]] = parseFloat($.trim(v.value));
        } else {
            resultData[vname] = parseFloat($.trim(v.value));
        }

    });

    textType.each(function (k, v) {
        var vname = v.name;
        if (vname.lastIndexOf("[") != -1) {
            vname = vname.substr(0, vname.lastIndexOf("["));
            resultData[vname] = [];
        } else {
            resultData[vname] = 0;
        }
    });

    textType.each(function (k, v) {
        var vname = v.name;
        if (vname.lastIndexOf("[") != -1) {
            vname = vname.substr(0, vname.lastIndexOf("["));
            if (typeof(nameData[vname]) !== 'undefined') {
                nameData[vname]++;
            } else {
                nameData[vname] = 0;
            }
            resultData[vname][nameData[vname]] = $.trim(v.value);
        } else {
            resultData[vname] = $.trim(v.value);
        }

    });
    //radio框所有
    radioType.each(function (k, v) {
        resultData[v.name] = v.value;
    });
    textareaType.each(function (k, v) {
        resultData[v.name] = $.trim(v.value);
    });
    return resultData;
}

/**
 * 暂存数据
 */
function saveStorageData(e) {
    //每次按照检测报告的编号来对应保存数据
    var reportNumber = $(".stroageNumber").html();
    resultData = getData();
    resultData['lookslike_desc'] = $("input[name='lookslike_desc']").val();
    resultData['total_desc'] = $("input[name='total_desc']").val();
    localStorage.setItem(reportNumber, JSON.stringify(resultData));
}

/**
 * 改变按钮状态为预览状态
 */
function previewStatus() {
    var editHtmlButton = $(".editHtml").children("button"), confirmHtmlButton = $(".confirmHtml").children("button");
    $("input[type='radio'],input[type='number'],input[type='text'],textarea").attr('disabled', true);
    $("input[type='radio']:checked").each(function (k, v) {
        $(v).attr('disabled', false)
    });
    layui.form.render();

    $('.photoDelete').hide();
    $('#nameplate').hide();
    $('#instrument_view').hide();


    confirmHtmlButton.removeClass("backButton");
    confirmHtmlButton.html('确认无误并提交');
    confirmHtmlButton.attr('lay-filter', 'add');
    editHtmlButton.html('继续修改');
    editHtmlButton.removeClass('backButton');
    editHtmlButton.addClass('editButton');
}

/**
 * 改变按钮状态为修改状态
 */
function editStatus() {
    var editHtmlButton = $(".editHtml").children("button"), confirmHtmlButton = $(".confirmHtml").children("button");
    $("input[type='number'],input[type='text'],textarea").attr('disabled', false);
    $("input[type='radio']").each(function (k, v) {
        if (!$(v).attr('checked')) {
            $(v).attr('disabled', false)
        }
    });
    layui.form.render();
    $('.photoDelete').show();
    $('#nameplate').show();
    $('#instrument_view').show();





    confirmHtmlButton.removeClass("backButton");
    confirmHtmlButton.addClass("confirm");
    confirmHtmlButton.html('预览');
    confirmHtmlButton.attr('lay-filter', 'confirm');
    editHtmlButton.html('返回');
    editHtmlButton.removeClass('editButton');
    editHtmlButton.addClass('backButton');
}

/**
 * 进页面执行一次方法检测是否有暂存数据并对页面的内容增加验证
 */
function checkStroageData() {
    //旧数据
    var reportNumber = $(".stroageNumber").html(),
        oldData = JSON.parse(localStorage.getItem(reportNumber)),
        radioData = {}, lookslikeDesc = $("input[name='lookslike_desc']"),
        numberType = $("input[type='number']"),
        textType = $("input[type='text']"),
        radioType = $("input[type='radio']");
    if (oldData) {
        //如果存在赋值
        numberType.each(function (k, v) {
            var vname = v.name;
            if(vname.lastIndexOf("[") != -1){
                vname = vname.substr(0, vname.lastIndexOf("["));
                $(v).val(oldData[vname][0]);
                oldData[vname].splice($.inArray(oldData[vname][0], oldData[vname]), 1);
            }
        });
        textType.each(function (k, v) {
            var vname = v.name;
            if(vname.lastIndexOf("[") != -1){
                vname = vname.substr(0, vname.lastIndexOf("["));
                $(v).val(oldData[vname][0]);
                oldData[vname].splice($.inArray(oldData[vname][0], oldData[vname]), 1);
            }
        });
        $.each(oldData, function (k, v) {
            if (!$.isArray(v)) {
                radioData[k] = v;
            }
            if(k.indexOf('result[') >= 0){
                if(v != '合格'){
                    var targetdiv = $("input[name='"+k+"']").parent().parent().find('div');
                    targetdiv.show();
                    $(targetdiv).find('textarea').attr('lay-verify','abnormal_remark');
                }
            }
        });
        //radio的赋值统一用layui方法
        if (radioData.lookslike == 2) {
            lookslikeDesc.show();
            lookslikeDesc.attr('lay-verify', 'lookslike')
        } else {
            lookslikeDesc.hide();
            lookslikeDesc.removeAttr('lay-verify');
            radioData.lookslike_desc = '';
        }
        layui.form.val("qualityForm", radioData)
    }
    //所有输入框加入验证
    numberType.each(function (k, v) {
        $(v).attr('lay-verify', 'numberRange');
    });
    //所有radio加入验证
    radioType.each(function (k, v) {
        $(v).attr('lay-verify', 'choose');
    });
}

/**
 * 控制外观选项元素操作
 */
function changeSurfaceStatus(v) {
    var lookslikeDesc = $("input[name='lookslike_desc']");
    if (v == 2) {
        lookslikeDesc.show();
        lookslikeDesc.attr('lay-verify', 'lookslike');
    } else {
        lookslikeDesc.hide();
        lookslikeDesc.removeAttr('lay-verify');
    }
}

/**
 * 轮播参数
 */
function carouselConfig(){
    config = {
        // 分页器
        pagination: '.swiper-pagination',
        autoplay: 3000,//可选选项，自动滑动
        loop:false,//环路
        observer:true,
        observeParents:true,
        autoplayDisableOnInteraction : false
    };
    return config;
}

function show_error_tips(msg,time) {
    var error_msg = '出错了！';
    if($.trim(msg)){
        error_msg = msg;
    }
    var tips = '<div id="show_error_tips">\n' +
        '    <span>'+error_msg+'</span>\n' +
        '</div>';
    $('body').append(tips);
    var closetime = 2000;
    if(time){
        closetime = time;
    }
    $("#show_error_tips").slideDown(60,function () {
        setTimeout(function () {
            $("#show_error_tips").slideUp(60,function () {
                $("#show_error_tips").remove();
            });
        },closetime);
    });
}


/*多选下拉框默认配置(1读取多选框样式配置，2读取需要的工具条，3读取紧凑型)*/
function selectParams($settingNumber) {
    var config;
    switch ($settingNumber) {
        case 1:
            config = {
                //height: "38px",                 //是否固定高度, 数字px | auto
                //direction: "down",
                showCount: 1         //多选的label数量, 0,负值,非数字则显示全部
                //searchType: "dl"
            };
            break;
        case 2:
            config = ['select', 'remove'];
            break;
        case 3:
            config = {show: '', space: '10px'};
            break;
    }
    return config;
}

/**
 * 每个页面下方导航栏菜单展开
 */
function menuListSpread(){
    //菜单阻止冒泡事件
    var menuListObjOther = $(".menuList");
    $(".menu").on("click", function(a){
        menuListObjOther.hide();
        var menuListObj = $(this).siblings(".menuList");
        if(menuListObj.is(":hidden")){
            menuListObj.show();
        }else{
            menuListObj.hide();
        }
        $(document).one("click", function(){
            menuListObj.hide();
        });
        a.stopPropagation();
    });
    menuListObjOther.on("click", function(a){
        a.stopPropagation();
    });
}

/*加载更多 收起 按钮处理*/
$(document).on('click','.moreDetailButton',function () {
    $("."+$(this).attr('data-detailClass')).show();
    $(this).parent().hide();
    $(this).parent('.moreButton').siblings('.moreButton').show();
});


$(document).on('click','.hideDetailButton',function () {
    $("."+$(this).attr('data-detailClass')).hide();
    $(this).parent().hide();

    $(this).parent('.moreButton').siblings('.moreButton').show();
});



//用于验证整个系统的所有联系电话号码输入框
function checkTel(number) {
    var isPhone = /^([0-9]{3,4}-)?[0-9]{7,8}$|^[48]00\d+$/;
    var isMob = /^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/;
    if (isMob.test(number) || isPhone.test(number)) {
        return true;
    } else {
        return false;
    }
}

//生成加密
function _getRandomString(len) {
    len = len || 32;
    var $chars = 'ABCDEFGHIJKLMNPQRSTWXYZabcdefhijkmnprstwxyz123456789';
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

//验证保留5位小数 金额
function check_price(price) {
    var fix_amount = price;
    var fix_amountTest = /^(([1-9]\d*)|\d)(\.\d{1,5})?$/;
    if (fix_amountTest.test(fix_amount)) {
        return true;
    } else {
        return false;
    }
}
//验证数量
function check_num(val) {
    var fix_amount = val;
    var fix_amountTest = /^([1-9]\d*|[0]{1,1})$/;
    if (fix_amountTest.test(fix_amount)) {
        return true;
    } else {
        return false;
    }
}

//验证百分比
function check_Percentage(val) {
    var fix_amount = val;
    var fix_amountTest = /^(100|[1-9]?\d(\.\d{1,5})?)%$/;
    if (fix_amountTest.test(fix_amount)) {
        return true;
    } else {
        return false;
    }
}
