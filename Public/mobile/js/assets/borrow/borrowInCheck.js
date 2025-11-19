layui.use(['layer', 'form', 'table'], function () {
    var layer = layui.layer
        , table = layui.table
        , form = layui.form;

    //初始化
    form.render();
    //初始化下方导航栏菜单
    console.log(getThisDate(1));
    menuListSpread();
    $("#borrow_in_time").datetimePicker({});



    //监听通知-确认借入
    form.on('submit(borrowInCheck)', function (data) {
        var params = data.field;
        if (!params.borrow_in_time) {
            $.toptip('请补充确认借入时间', 'error');
            return false;
        }
        params.status = BORROW_STATUS_GIVE_BACK;
        submit(params, borrowInCheckUrl,mobile_name+'/Borrow/borrowInCheckList');
        return false;
    });


    //监听通知-不借入
    form.on('submit(notBorrowInCheck)', function (data) {
        layer.open({
            id: 'notBorrowInChecks',
            type: 1,
            title: '【<span class="rquireCoin">*</span> 不借入原因】',
            area: ['80%', '40%'],
            offset: 'auto',
            anim: 5,
            resize: false,
            scrollbar: false,
            isOutAnim: true,
            closeBtn: 1,
            content: $('#end_reason')
        });
        var params = data.field;

        form.on('submit(NotToBorrow)', function (data) {
            var field=data.field;
            if(!field.end_reason){
                $.toptip('请先补充不借入的原因', 'error');
                return false;
            }
            params.status = BORROW_STATUS_NOT_APPLY;
            params.end_reason=field.end_reason;
            submit(params, borrowInCheckUrl,mobile_name+'/Borrow/borrowInCheckList');
        });





        return false;
    });






});