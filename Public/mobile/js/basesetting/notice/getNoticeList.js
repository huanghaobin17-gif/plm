//初始化下方导航栏菜单
menuListSpread();

$(".jumpDetail").click(function () {
	var id = $(this).attr('data-id');
    window.location.href = mobile_name+'/Notice/getNoticeList?id='+id+'&action=showNotice';
});