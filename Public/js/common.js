var UA = navigator.userAgent.toLowerCase();
var isIE = (document.all && window.ActiveXObject && !window.opera) ? true : false;
var isGecko = UA.indexOf('webkit') != -1;
var DMURL = document.location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '') + '/';
DTPath = window.location.host;
var AJPath = (DTPath.indexOf('://') == -1 ? DTPath : (DTPath.indexOf(DMURL) == -1 ? DMURL : DTPath)) + 'ajax.php';
if (isIE) try {
    document.execCommand("BackgroundImageCache", false, true);
} catch (e) {
}
var xmlHttp;
var Try = {
    these: function () {
        var returnValue;
        for (var i = 0; i < arguments.length; i++) {
            var lambda = arguments[i];
            try {
                returnValue = lambda();
                break;
            } catch (e) {
            }
        }
        return returnValue;
    }
}
var admin_name = '/A';

function get_now_cookie(Name) {
    var search = Name + "="//查询检索的值
    var returnvalue = "";//返回值
    if (document.cookie.length > 0) {
        sd = document.cookie.indexOf(search);
        if (sd != -1) {
            sd += search.length;
            end = document.cookie.indexOf(";", sd);
            if (end == -1)
                end = document.cookie.length;
            //unescape() 函数可对通过 escape() 编码的字符串进行解码。
            returnvalue = unescape(document.cookie.substring(sd, end))
        }
    }
    return returnvalue;
}

function makeRequest(queryString, php, func, method) {
    xmlHttp = Try.these(
        function () {
            return new XMLHttpRequest()
        },
        function () {
            return new ActiveXObject('Msxml2.XMLHTTP')
        },
        function () {
            return new ActiveXObject('Microsoft.XMLHTTP')
        }
    );
    method = (typeof method == 'undefined') ? 'post' : 'get';
    if (func) xmlHttp.onreadystatechange = eval(func);
    xmlHttp.open(method, method == 'post' ? php : php + '?' + queryString, true);
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.send(method == 'post' ? queryString : null);
}

function Dd(i) {
    return document.getElementById(i);
}

function Ds(i) {
    Dd(i).style.display = '';
}

function Dh(i) {
    Dd(i).style.display = 'none';
}

function Dsh(i) {
    Dd(i).style.display = Dd(i).style.display == 'none' ? '' : 'none';
}

function Df(i) {
    Dd(i).focus();
}

function cDialog() {
    $('#Dmid').remove();
    $('#Dtop').fadeOut('fast', function () {
        $('#Dtop').remove();
        Es();
    });
}

function Dconfirm(c, u, w, s) {
    if (!c) return;
    var s = s ? s : 0;
    var w = w ? w : 350;
    var d = u ? "window.location = '" + u + "'" : 'cDialog()';
    c = c + '<br style="margin-top:5px;"/><input type="button" class="btn" value=" ' + L['ok'] + ' " onclick="' + d + '"/>&nbsp;&nbsp;<input type="button" class="btn" value=" ' + L['cancel'] + ' " onclick="cDialog();"/>';
    mkDialog('', c, '', w, s);
}

var tID = 0;

function Tab(ID) {
    var tTab = Dd('Tab' + tID);
    var tTabs = Dd('Tabs' + tID);
    var Tab = Dd('Tab' + ID);
    var Tabs = Dd('Tabs' + ID);
    if (ID != tID) {
        tTab.className = 'tab';
        Tab.className = 'tab_on';
        tTabs.style.display = 'none';
        Tabs.style.display = '';
        tID = ID;
        try {
            Dd('tab').value = ID;
        } catch (e) {
        }
    }
}

function checkall(f, t) {
    var t = t ? t : 1;
    for (var i = 0; i < f.elements.length; i++) {
        var e = f.elements[i];
        if (e.type != 'checkbox') continue;
        if (t == 1) e.checked = e.disabled ? false : (e.checked ? false : true);
        if (t == 2) e.checked = true;
        if (t == 3) e.checked = false;
    }
}

function stoinp(s, i, p) {
    if (Dd(i).value) {
        var p = p ? p : ',';
        var a = Dd(i).value.split(p);
        for (var j = 0; j < a.length; j++) {
            if (s == a[j]) return;
        }
        Dd(i).value += p + s;
    } else {
        Dd(i).value = s;
    }
}

function select_op(i, v) {
    var o = Dd(i);
    for (var i = 0; i < o.options.length; i++) {
        if (o.options[i].value == v) {
            o.options[i].selected = true;
            break;
        }
    }
}

function Dmsg(str, i, s, t) {
    var t = t ? t : 5000;
    var s = s ? true : false;
    try {
        if (s) {
            window.scrollTo(0, 0);
        }
        Ds('d' + i);
        Dd('d' + i).innerHTML = '<img src="/sbsys/images/check_error.gif" width="12" height="12" align="absmiddle"/> ' + str + sound('tip');
        Dd(i).focus();
    } catch (e) {
    }
    window.setTimeout(function () {
        Dd('d' + i).innerHTML = '';
        Dh('d' + i);
    }, t);
}

function Inner(i, s) {
    try {
        Dd(i).innerHTML = s;
    } catch (e) {
    }
}

function InnerTBD(i, s) {
    try {
        Dd(i).innerHTML = s;
    } catch (e) {
        Dd(i).parentNode.outerHTML = Dd(i).parentNode.outerHTML.replace(Dd(i).innerHTML, s);
    }
}

function Go(u) {
    window.location = u;
}

function confirmURI(m, f) {
    if (confirm(m)) Go(f);
}

function showmsg(m, t) {
    var t = t ? t : 5000;
    var s = m.indexOf(L['str_delete']) != -1 ? 'delete' : 'ok';
    try {
        Dd('msgbox').style.display = '';
        Dd('msgbox').innerHTML = m + sound(s);
        window.setTimeout('closemsg();', t);
    } catch (e) {
    }
}

function closemsg() {
    try {
        Dd('msgbox').innerHTML = '';
        Dd('msgbox').style.display = 'none';
    } catch (e) {
    }
}

function sound(f) {
    return '<div style="float:left;"><embed src="flash/' + f + '.swf" quality="high" type="application/x-shockwave-flash" height="0" width="0" hidden="true"/></div>';
}

function Eh(t) {
    var t = t ? t : 'select';
    if (isIE) {
        var arVersion = navigator.appVersion.split("MSIE");
        var IEversion = parseFloat(arVersion[1]);
        if (IEversion >= 7 || IEversion < 5) return;
        var ss = document.body.getElementsByTagName(t);
        for (var i = 0; i < ss.length; i++) {
            ss[i].style.visibility = 'hidden';
        }
    }
}

function Es(t) {
    var t = t ? t : 'select';
    if (isIE) {
        var arVersion = navigator.appVersion.split("MSIE");
        var IEversion = parseFloat(arVersion[1]);
        if (IEversion >= 7 || IEversion < 5) return;
        var ss = document.body.getElementsByTagName(t);
        for (var i = 0; i < ss.length; i++) {
            ss[i].style.visibility = 'visible';
        }
    }
}

function FCKLen(i) {
    var i = i ? i : 'content';
    var o = FCKeditorAPI.GetInstance(i);
    var d = o.EditorDocument;
    var l;
    var c;
    if (document.all) {
        c = d.body.innerText;
        c = c.replace(/&nbsp;/ig, "");
        c = c.replace(/(^\s+)|(\s+$)/ig, "");
        l = c.length;
    } else {
        var r = d.createRange();
        r.selectNodeContents(d.body);
        c = r.toString();
        c = c.replace(/&nbsp;/ig, "");
        c = c.replace(/(^\s+)|(\s+$)/ig, "");
        l = c.length;
    }
    return l;
}

function FCKXHTML(i) {
    var i = i ? i : 'content';
    return FCKeditorAPI.GetInstance(i).GetXHTML(true);
}

function Tb(d, t, p, c) {
    for (var i = 1; i <= t; i++) {
        if (d == i) {
            Dd(p + '_t_' + i).className = c + '_2';
            Ds(p + '_c_' + i);
        } else {
            Dd(p + '_t_' + i).className = c + '_1';
            Dh(p + '_c_' + i);
        }
    }
}

function is_captcha(v) {
    if (v == L['str_captcha']) return false;
    if (v.match(/^[a-z0-9A-Z]{1,}$/)) {
        return v.match(/^[a-z0-9A-Z]{4,}$/);
    } else {
        return v.length > 1;
    }
}

function ext(v) {
    return v.substring(v.lastIndexOf('.') + 1, v.length).toLowerCase();
}

function PushNew() {
    $('#destoon_push').remove();
    s = document.createElement("script");
    s.type = "text/javascript";
    s.id = "destoon_push";
    s.src = DTPath + "api/push.js.php?refresh=" + Math.random() + ".js";
    document.body.appendChild(s);
}

function set_cookie(n, v, d) {
    var e = '';
    var f = d ? d : 365;
    e = new Date((new Date()).getTime() + f * 86400000);
    e = "; expires=" + e.toGMTString();
    document.cookie = CKPrex + n + "=" + v + ((CKPath == "") ? "" : ("; path=" + CKPath)) + ((CKDomain == "") ? "" : ("; domain=" + CKDomain)) + e;
}

function get_cookie(n) {
    var v = '';
    var s = CKPrex + n + "=";
    if (document.cookie.length > 0) {
        o = document.cookie.indexOf(s);
        if (o != -1) {
            o += s.length;
            end = document.cookie.indexOf(";", o);
            if (end == -1) end = document.cookie.length;
            v = unescape(document.cookie.substring(o, end));
        }
    }
    return v;
}

function del_cookie(n) {
    var e = new Date((new Date()).getTime() - 1);
    e = "; expires=" + e.toGMTString();
    document.cookie = CKPrex + n + "=" + escape("") + ";path=/" + e;
}

function substr_count(str, exp) {
    if (str == '') return 0;
    var s = str.split(exp);
    return s.length - 1;
}

function lang(s, a) {
    for (var i = 0; i < a.length; i++) {
        s = s.replace('{V' + i + '}', a[i]);
    }
    return s;
}

document.onkeydown = function (e) {
    var k = typeof e == 'undefined' ? event.keyCode : e.keyCode;
    if (k == 37) {
        try {
            if (Dd('destoon_previous').value && typeof document.activeElement.name == 'undefined') Go(Dd('destoon_previous').value);
        } catch (e) {
        }
    } else if (k == 39) {
        try {
            if (Dd('destoon_next').value && typeof document.activeElement.name == 'undefined') Go(Dd('destoon_next').value);
        } catch (e) {
        }
    } else if (k == 38 || k == 40 || k == 13) {
        try {
            if (Dd('search_tips').style.display != 'none' || Dd('search_tips').innerHTML != '') {
                SCTip(k);
                return false;
            }
        } catch (e) {
        }
    }
};
//jinlong create by 2017/03/08
//去除输入框两边的空格
function trim(str, is_global) {
    var result;
    result = str.replace(/(^\s+)|(\s+$)/g, "");
    if (is_global.toLowerCase() == "g") {
        result = result.replace(/\s/g, "");
    }
    return result;
}

/********实时更新input框的状态，ajax实时获取关键字的数据*******/
function getListByKeyword(type, value) {
    var ty = $.trim(type);
    var val = $.trim(value);
    if (!ty) {
        return false;
    }
    if (!val) {
        return false;
    }
    var url = admin_name + "/Lookup/getListByKeyword";
    var param = {};
    param['type'] = ty;
    param['value'] = val;
    $.post(url, param, function (data) {
        if (type == 'assets_name') {
            $('#assetsTable').html('');
            $('#assetsTable').append(data);
        } else if (type == 'category_name') {
            $('#categoryTable').html('');
            $('#categoryTable').append(data);
        } else if (type == 'depart_name') {
            $('#departTable').html('');
            $('#departTable').append(data);
        }
    });
}

function getFiveMaxSearch(type, value, showId, id, event) {
    event.stopImmediatePropagation();//取消事件冒泡；
    $("#assets_div").hide();
    $("#depart_div").hide();
    $("#category_div").hide();
    $("#" + showId).show();
    if (!value) {
        //输入框为空，获取最多搜索的五条数据
        var url = admin_name + "/Lookup/fiveMaxSearch.html";
        var param = {};
        param['type'] = type;
        $.post(url, param, function (data) {
            if (type == 'assets_name') {
                $('#assetsTable').html('');
                $('#assetsTable').append(data);
            } else if (type == 'category_name') {
                $('#categoryTable').html('');
                $('#categoryTable').append(data);
            } else if (type == 'depart_name') {
                $('#departTable').html('');
                $('#departTable').append(data);
            }
        });
    }
}

function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = year + seperator1 + month + seperator1 + strDate;
    return currentdate;
}

function DoOnMsoNumberFormat(cell, row, col) {
    var result = "";
    if (row > 0 && col == 0)
        result = "\\@";
    return result;
}

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

/*
 /选择分类 搜索功能
 */
function returnCategory(data, position) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    if (position == 1) {
        position = "left"
    } else {
        position = "right"
    }
    var value = {
        url: admin_name + "/Public/getAllCategorySearch" + data,
        effectiveFields: ["catenum", "category"],
        searchFields: ["category"],
        effectiveFieldsAlias: {catenum: "分类编号", category: "分类名称"},
        ignorecase: false,
        showHeader: true,
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "category",
        keyField: "category",
        listAlign: position,
        listStyle: {
            "max-height": "330px", "max-width": "400px",
            "overflow": "auto", "width": "340px", "text-align": "center"
        },
        clearable: false
    };
    return value;
}


/*
 * 选择设备 搜索功能
 * */
function returnAssets(tableName, searchKey, position, otherParams) {
    var params = '', other = '';
    var keyField = 'assets';
    if (tableName) {
        params = '?type=' + tableName;
    }
    if (searchKey) {
        keyField = searchKey;
    }
    if (position == 1) {
        if (searchKey == 'assnum') {
            position = "rigth"
        }
    } else {
        position = "left"
    }
    if (otherParams) {
        other = '&' + otherParams;
    }
    var value = {
        url: admin_name + "/Public/getAllAssetsSearch.html" + params + other,
        //effectiveFields: ["userName", "shortAccount"],
        effectiveFields: ["assnum", "assets", "assorignum", "pinyin"],
        searchFields: ["assets"],
        effectiveFieldsAlias: {assnum: "设备编号", assets: "设备名称", assorignum: "设备原编号", pinyin: "拼音"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "580px",
            "overflow": "auto", "width": "580px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        listAlign: position,
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: keyField,
        keyField: keyField,
        clearable: false
    };
    return value;
}


/*
 * 选择设备 搜索功能
 * */
function returnRepairFormAssets(tableName, searchKey, position, otherParams) {
    var params = '', other = '';
    var keyField = 'assets';
    if (tableName) {
        params = '?type=' + tableName;
    }
    if (searchKey) {
        keyField = searchKey;
    }
    if (position == 1) {
        if (searchKey == 'assnum') {
            position = "rigth"
        }
    } else {
        position = "left"
    }
    if (otherParams) {
        other = '&' + otherParams;
    }
    var value = {
        url: admin_name + "/Public/getAllAssetsSearch.html" + params + other,
        //effectiveFields: ["userName", "shortAccount"],
        effectiveFields: ["assnum", "assets", "assorignum", "model", "serialnum", "address"],
        searchFields: ["assets"],
        effectiveFieldsAlias: {assnum: "设备编号", assets: "设备名称", assorignum: "设备原编号", model: "规格/型号", serialnum: "设备序列号", address: "设备位置"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "580px",
            "overflow": "auto", "width": "580px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        listAlign: position,
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: keyField,
        keyField: keyField,
        clearable: false
    };
    return value;
}

/**
 /建议搜索：设备规格/型号
 */
function returnAssetsModel(position) {
    if (position == 1) {
        position = "left"
    } else {
        position = "right"
    }
    var value = {
        url: admin_name + "/Public/getAssetsModelSearch",
        effectiveFields: ["num", "model"],
        searchFields: ["model"],
        effectiveFieldsAlias: {num: "序号", model: "规格/型号"},
        ignorecase: false,
        showHeader: true,
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "model",
        keyField: "model",
        listAlign: position,
        listStyle: {
            "max-height": "330px", "max-width": "480px",
            "overflow": "auto", "width": "400px", "text-align": "center"
        },
        clearable: false
    };
    return value;
}

/*
 /选择设备编号 搜索功能
 */
function returnRepairFormAssnum(tableName, searchKey, position, otherParams) {
    var params = '', other = '';
    var keyField = 'assets';
    if (tableName) {
        params = '?type=' + tableName;
    }
    if (searchKey) {
        keyField = searchKey;
    }
    if (position == 1) {
        if (searchKey == 'assnum') {
            position = "rigth"
        }
    } else {
        position = "left"
    }
    if (otherParams) {
        other = '&' + otherParams;
    }
    var value = {
        url: admin_name + "/Public/getAllAssetsSearch.html" + params + other,
        effectiveFields: ["assnum", "assets", "assorignum", "model", "serialnum", "address"],
        searchFields: ["assnum"],
        effectiveFieldsAlias: {assnum: "设备编号", assets: "设备名称", assorignum: "设备原编号", model: "规格/型号", serialnum: "设备序列号", address: "设备位置"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "500px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "assnum",
        keyField: "assnum",
        clearable: false
    };
    return value;
}

/*
 /选择设备编号 搜索功能
 */
function returnAssnum(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllAssetsSearch" + data,
        effectiveFields: ["assets", "assnum", "assorignum"],
        searchFields: ["assnum"],
        effectiveFieldsAlias: {assets: "设备名称", assnum: "设备编号", assorignum: "设备原编号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "500px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "assnum",
        keyField: "assnum",
        clearable: false
    };
    return value;
}

/*
 /选择科室 搜索功能
 */
function returnDepartment(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllDepartmentSearch",
        effectiveFields: ["department", "departnum"],
        searchFields: ["department"],
        effectiveFieldsAlias: {department: "科室名称", departnum: "科室编号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "480px",
            "overflow": "auto", "width": "340px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "department",
        keyField: "department",
        clearable: false
    };
    return value;
}


function getOfflineSuppliersName(get) {
    var value = {
        url: admin_name + "/Public/getOfflineSuppliersName" + get,
        effectiveFields: ["sup_name"],
        searchFields: ["sup_name"],
        effectiveFieldsAlias: {sup_name: "公司名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "300px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "sup_name",
        keyField: "sup_name",
        clearable: false
    };
    return value;
}

function getOLSContractContractName(get) {
    var value = {
        url: admin_name + "/Public/getOLSContractContractName" + get,
        effectiveFields: ["contract_name"],
        searchFields: ["contract_name"],
        effectiveFieldsAlias: {contract_name: "合同名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "300px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "contract_name",
        keyField: "contract_name",
        clearable: false
    };
    return value;
}


/*
 /选择计划名称 搜索功能
 */
function returnProject(data) {
    if (data) {
        data = 1;
    } else {
        data = 0;
    }
    var value = {
        url: admin_name + "/Public/getAllPlans?data=" + data,
        effectiveFields: ["patrol_name"],
        searchFields: ["patrol_name"],
        effectiveFieldsAlias: {patrol_name: "计划名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "380px",
            "overflow": "auto", "width": "380px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "patrol_name",
        keyField: "patrol_name",
        clearable: false
    };
    return value;
}

//returnProjectName
function returnProjectName(data) {
    if (data) {
        data = 1;
    } else {
        data = 0;
    }
    var value = {
        url: admin_name + "/Public/getAllPlans?data=" + data,
        effectiveFields: ["patrolnum", "patrolname"],
        searchFields: ["patrolname"],
        effectiveFieldsAlias: {patrolnum: "计划编号", patrolname: "计划名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "480px",
            "overflow": "auto", "width": "480px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "patrolname",
        keyField: "patrolname",
        clearable: false
    };
    return value;
}


/*
 /选择计划制定人 搜索功能
 */
function returnProjectMan(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllPlansMaker",
        effectiveFields: ["username", "telephone"],
        searchFields: ["username"],
        effectiveFieldsAlias: {username: "计划制定人", telephone: "联系电话"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "350px",
            "overflow": "auto", "width": "300px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "username,telephone",
        keyField: "username",
        clearable: false
    };
    return value;
}

/*
 /选择执行人 搜索功能
 */
function returnExecutor(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllExecute",
        effectiveFields: ["username", "telephone"],
        searchFields: ["username"],
        effectiveFieldsAlias: {username: "执行人", telephone: "联系电话"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "350px",
            "overflow": "auto", "width": "300px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "username,telephone",
        keyField: "username",
        clearable: false
    };
    return value;
}


/*
 /科室位置 搜索功能
 */
function returnAddress(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllDepartmentSearch",
        effectiveFields: ["departnum", "address"],
        searchFields: ["address"],
        effectiveFieldsAlias: {departnum: "科室编号", address: "科室位置"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "480px",
            "overflow": "auto", "width": "360px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "address",
        keyField: "address",
        clearable: false
    };
    return value;
}


/*
 /科室负责人 搜索功能
 */
function returnDepartrespon(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllDepartmentRespon",
        effectiveFields: ["departid", "departrespon"],
        searchFields: ["departrespon"],
        effectiveFieldsAlias: {departid: "序号", departrespon: "科室负责人"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "460px",
            "overflow": "auto", "width": "250px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "departid",
        keyField: "departrespon",
        clearable: false
    };
    return value;
}


/*
 /用户 搜索功能
 */
function returnUser(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllUserSearch",
        effectiveFields: ["username", "telephone"],
        searchFields: ["username"],
        effectiveFieldsAlias: {username: "用户名", telephone: "手机号码"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "380px",
            "overflow": "auto", "width": "480px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "username",
        keyField: "username",
        clearable: false
    };
    return value;
}

/*
 /角色 搜索功能
 */
function returnRoles() {
    var value = {
        url: admin_name + "/Public/getAllRoles.html",
        effectiveFields: ["num", "rolename"],
        searchFields: ["rolename"],
        effectiveFieldsAlias: {num: "序号", rolename: "角色名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "380px",
            "overflow": "auto", "width": "480px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "rolename",
        keyField: "rolename",
        clearable: false
    };
    return value;
}

/*
 /用户 搜索功能
 */
function returnACINCategory(data) {
    if (data) {
        data = '?type=1';
    } else {
        data = '';
    }
    var value = {
        url: admin_name + "/Public/getAllACINCategorySearch",
        effectiveFields: ["category"],
        searchFields: ["category"],
        effectiveFieldsAlias: {category: "分类名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "300px",
            "overflow": "auto", "width": "480px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "category",
        keyField: "category",
        clearable: false
    };
    return value;
}


/*
 /字典 搜索设备字典
 */
function returnDicAssets() {
    var value = {
        url: admin_name + "/Public/getAllAssetsDic",
        effectiveFields: ["assets", "category", "unit"],
        searchFields: ["assets"],
        effectiveFieldsAlias: {assets: "设备名称", category: "设备分类", unit: "单位"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "400px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "assets",
        keyField: "assets",
        clearable: false
    };
    return value;
}

function returnDicBrand() {
    var value = {
        url: admin_name + "/Public/getAllBrandDic",
        effectiveFields: ["brand_name"],
        searchFields: ["brand_name"],
        effectiveFieldsAlias: {brand_name: "品牌名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "250px",
            "overflow": "auto", "width": "200px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "brand_name",
        keyField: "brand_name",
        clearable: false
    };
    return value;
}


//入库单号
function getInwareNum() {
    return {
        url: admin_name + "/Public/getInwareNum",
        effectiveFields: ["inware_num"],
        searchFields: ["inware_num"],
        effectiveFieldsAlias: {inware_num: "入库单号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "400px", "max-width": "250px",
            "overflow": "auto", "width": "200px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "inware_num",
        keyField: "inware_num",
        clearable: false
    };
}

//入库单号
function getOutwareNum() {
    return {
        url: admin_name + "/Public/getOutwareNum",
        effectiveFields: ["outware_num"],
        searchFields: ["outware_num"],
        effectiveFieldsAlias: {outware_num: "出库单号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "400px", "max-width": "250px",
            "overflow": "auto", "width": "200px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "outware_num",
        keyField: "outware_num",
        clearable: false
    };
}

//搜索配件字典
function returnPartsDic() {
    var value = {
        url: admin_name + "/Public/getAllPartsDic",
        effectiveFields: ["parts", "parts_model"],
        searchFields: ["parts"],
        effectiveFieldsAlias: {parts: "配件", parts_model: "配件型号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "400px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "parts",
        keyField: "parts",
        clearable: false
    };
    return value;
}

function returnApplyDepartment(id, apply_type) {
    var value = {
        url: admin_name + "/Public/getApplyDepartment?hospital_id=" + id + "&apply_type=" + apply_type,
        effectiveFields: ["department"],
        searchFields: ["department"],
        effectiveFieldsAlias: {department: "申请科室"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "400px",
            "overflow": "auto", "width": "300px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "department",
        keyField: "department",
        clearable: false
    };
    return value;
}

//采购计划中的项目名称
function returnPurPlansProjects() {
    var value = {
        url: admin_name + "/Public/getPurPlansProjects",
        effectiveFields: ["project_name"],
        searchFields: ["project_name"],
        effectiveFieldsAlias: {project_name: "项目名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "250px",
            "overflow": "auto", "width": "250px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "project_name",
        keyField: "project_name",
        clearable: false
    };
    return value;
}

//部门申请项目名称
function returnDepartProjects() {
    var value = {
        url: admin_name + "/Public/getAllDepartProjects",
        effectiveFields: ["project_name"],
        searchFields: ["project_name"],
        effectiveFieldsAlias: {project_name: "项目名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "250px",
            "overflow": "auto", "width": "250px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "project_name",
        keyField: "project_name",
        clearable: false
    };
    return value;
}

function getAllSupplier() {
    var type = 'supplier';
    var value = {
        url: admin_name + "/Public/getAllSupplierOrFactory?type=" + type,
        effectiveFields: ["supplier"],
        searchFields: ["supplier"],
        effectiveFieldsAlias: {supplier: "供应商"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "300px",
            "overflow": "auto", "width": "200px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "supplier",
        keyField: "supplier",
        clearable: false
    };
    return value;
}

function getAllSupplierFactoryOrRepair(type) {
    var value = {
        url: admin_name + "/Public/getAllSupplierFactoryOrRepair?type=" + type,
        effectiveFields: ["sup_name"],
        searchFields: ["sup_name"],
        effectiveFieldsAlias: {sup_name: "名称"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "400px",
            "overflow": "auto", "width": "300px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "sup_name",
        keyField: "sup_name",
        clearable: false
    };
    return value;
}

/*
 /字典 搜索字典类别
 */
function returnDicCategory(type) {
    return {
        url: admin_name + "/Public/getAllAssetsDicCategory?type=" + type,
        effectiveFields: ["dic_category"],
        searchFields: ["dic_category"],
        effectiveFieldsAlias: {dic_category: "字典类别"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "300px",
            "overflow": "auto", "width": "200px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "dic_category",
        keyField: "dic_category",
        clearable: false
    };
}

//搜索配件库
function returnPartsInfo() {
    var value = {
        url: admin_name + "/Public/getAllPartsInfo",
        effectiveFields: ["parts", "parts_model"],
        searchFields: ["parts"],
        effectiveFieldsAlias: {parts: "配件名称", parts_model: "配件型号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "330px", "max-width": "500px",
            "overflow": "auto", "width": "400px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "parts",
        keyField: "parts",
        clearable: false
    };
    return value;
}

//正则匹配去除html标签
function removeHtmlTag(str) {
    var result = '';
    var pattern = new RegExp('<\\s*(\\w+).*>(.+)</\\s*\\1\\s*>', 'ig');
    result = pattern.exec(str);
    if (result) {
        return result[2];
    }
    return str;
}

/**
 * post请求无法直接发送请求下载excel文档，是因为我们在后台改变了响应头的内容：
 * Content-Type: application/vnd.ms-excel
 * 致post请求无法识别这种消息头,导致无法直接下载。
 * 解决方法：
 * 改成使用form表单提交方式即可
 */
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
$.fn.rowspan = function (combined) {
    return this.each(function () {
        var that;
        $('tr', this).each(function (row) {
            $('td:eq(' + combined + ')', this).filter(':visible').each(function (col) {
                if (that != null && $(this).html() == $(that).html()) {
                    rowspan = $(that).attr("rowSpan");
                    if (rowspan == undefined) {
                        $(that).attr("rowSpan", 1);
                        rowspan = $(that).attr("rowSpan");
                    }
                    rowspan = Number(rowspan) + 1;
                    $(that).attr("rowSpan", rowspan);
                    $(this).hide();
                } else {
                    that = this;
                }
            });
        });
    });
};

//layui table 合并相同列
function rowspanTD(data, array, obj, fieldName) {
    $.each(data, function (key, value) {
        $.each(array, function (key2, value2) {
            if (value2[fieldName] === value[fieldName]) {
                if (value2.sum > 0) {
                    $(obj[key]).attr('rowspan', value2.sum);
                    array[key2].sum = 0;
                } else {
                    $(obj[key]).remove();
                }
            }
        });
    });
}


/*多选下拉框默认配置(1读取多选框样式配置，2读取需要的工具条，3读取紧凑型)*/
function selectParams($settingNumber) {
    var config;
    switch ($settingNumber) {
        case 1:
            config = {
                height: "38px",                 //是否固定高度, 数字px | auto
                direction: "down",
                showCount: 1,         //多选的label数量, 0,负值,非数字则显示全部
                searchType: "dl"
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

/**/

/*整个系统列表页面 弹窗风格配置*/
function layerParmas() {
    var config = {
        shade: 0, //关闭遮罩层
        shadeClose: false,// 点击遮罩层不关闭
        maxmin: true //显示最小化按钮
    };
    return config;
}

/**/

/*整个系统页面 时间文件默认配置*/
function dateConfig(element) {
    var config = {
        elem: element,
        calendar: true,
        min: '1970-01-02',
        trigger: 'click'
    };
    return config;
}

/**/

/*有新任务时的提醒效果*/
function newMessage() {
    var messageRange = $(".messageRange");
    messageRange.css('display', 'block');
    shake(messageRange, "red", 10);
    setTimeout(function () {
        messageRange.css('display', 'none');
    }, 5000);
}

function shake(ele, cls, times) {
    var i = 0, t = false, o = ele.attr("class") + " ", c = "", times = times || 2;

    if (t) return;

    t = setInterval(function () {

        i++;

        c = i % 2 ? o + cls : o;

        ele.attr("class", c);

        if (i == 2 * times) {

            clearInterval(t);

            ele.removeClass(cls);

        }

    }, 200);
}

//返回档案盒编号
function returnBoxNum() {
    return {
        url: admin_name + "/Public/getBoxNum",
        effectiveFields: ["box_num"],
        searchFields: ["box_num"],
        effectiveFieldsAlias: {box_num: "档案盒编号"},
        ignorecase: false,
        showHeader: true,
        listStyle: {
            "max-height": "400px", "max-width": "250px",
            "overflow": "auto", "width": "200px", "text-align": "center"
        },
        showBtn: false,     //不显示下拉按钮
        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
        idField: "box_num",
        keyField: "box_num",
        clearable: false
    };
}

Array.prototype.indexOf = function (val) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == val) return i;
    }
    return -1;
};
Array.prototype.remove = function (val) {
    var index = this.indexOf(val);
    if (index > -1) {
        this.splice(index, 1);
    }
};
/**/






