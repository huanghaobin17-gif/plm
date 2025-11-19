layui.define(function(exports){
    layui.use(['layer', 'form','suggest','laydate','table','tipsType'], function(){
        var layer = layui.layer,form = layui.form, table = layui.table,suggest = layui.suggest,laydate = layui.laydate,tipsType = layui.tipsType;
        //初始化搜索建议插件
        suggest.search();
        tipsType.choose();
        //转科时间元素渲染
        laydate.render({
            elem: '#transferdate' //指定元素
            ,min: today
        });

        //自定义验证规则
        form.verify({
            transferdate: function (value) {
                value = $.trim(value);
                if(value == ''){
                    return '请选择预计转科日期！';
                }
            }
        });
        /*
         /转科选择科室
         */
        $(".bsSuggest1").bsSuggest({
            url: admin_name+"/Public/getAllDepartmentSearch?type=all&hospital_id="+hospital_id+"&departid="+departid,
            /*effectiveFields: ["userName", "shortAccount"],
             searchFields: [ "shortAccount"],*/
            effectiveFields: ["departid", "departnum","department","departrespon","address","assetsrespon"],
            effectiveFieldsAlias:{departid: "序号",departnum:'科室编号',department:"科室名称",departrespon:"科室负责人",address:"存放地址",assetsrespon:"设备负责人"},
            ignorecase: false,
            showHeader: true,
            listStyle: {
                "max-height": "375px", "max-width": "520px",
                "overflow": "auto", "width": "520px", "text-align": "center"
            },
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "departid",
            keyField: "department",
            clearable: false
        }).on('onDataRequestSuccess', function (e, result) {
            //console.log('onDataRequestSuccess: ', result);
        }).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='departid']").val(data.departid);
            $("input[name='departrespon']").val(data.departrespon);
            $("input[name='address']").val(data.address);
            $("input[name='rightdepart']").val('1');
        }).on('onUnsetSelectValue', function () {
            //不选择正确科室时的操作
            $("input[name='rightdepart']").val('0');
            $("input[name='departrespon']").val('');
            $("input[name='address']").val('');
        });


        /*
         /提交转科申请
         */
        $('#addTra').on('click',function(){
            var flag = $("input[name='rightdepart']").val();
            if(flag == 0){
                layer.msg('请选择转入科室', {icon: 2});
                return false;
            }
            //转出科室ID
            var traOutId = $("input[name='traout']").attr('data-value');
            //转入科室ID
            var departid = $("input[name='departid']").val();
            //转入设备ID
            var assids = $("input[name='assids']").val();
            var atid = $("input[name='atid']").val();
            //转科时间
            var transferdate = $("input[name='transferdate']").val();
            //转科文号
            var docnum = $("input[name='docnum']").val();
            var nowDate = getNowFormatDate();
            if(!transferdate){
                layer.msg('请选择转科时间', {icon: 2});
                return false;
            }
            if(!atid){
                if(nowDate > transferdate){
                    layer.msg('转科时间不能小于当前时间', {icon: 2});
                    return false;
                }
            }
            if(traOutId == departid){
                layer.msg('不能转入同一个科室！', {icon: 2});
                return false;
            }
            //转科原因
            var tranreason = $("textarea[name='tranreason']").val();
            //保存地址
            var address = $("input[name='address']").val();
            var url = admin_name+"/Transfer/add";
            var param = {};
            param['traOutId']  = traOutId;
            param['departid']  = departid;
            param['assids']  = assids;
            param['atid']  = atid;
            param['transferdate'] = transferdate;
            param['tranreason'] = tranreason;
            param['address'] = address;
            param['docnum'] = docnum;

            var checkStatus = table.checkStatus('subsidiaryData');

            var length = checkStatus.data.length;
            var e = /^.*value=[\"\']\w+[\"\'].*$/i;
            if(length>0){
                var assid = '';
                var main_assid='';
                for (var i = 0; i < length; i++) {

                    main_assid+=checkStatus.data[i]['main_assid'].replace(/^.*value=([\"\']\w+[\"\']).*$/,"$1")+',';
                    assid += checkStatus.data[i]['assid'] + ',';
                }
                param.subsidiary_assid=assid.substring(0,assid.length-1);
                param.main_assid=main_assid.substring(0,main_assid.length-1);
            }
            submit($,param,url);
            return false;
        });
    });
    exports('controller/assets/transfer/add', {});
});






