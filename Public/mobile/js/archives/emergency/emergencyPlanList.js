//初始化下方导航栏菜单
menuListSpread();

$(".jumpDetail").click(function () {
	var id = $(this).attr('data-id');
    window.location.href = mobile_name+'/Emergency/showEmergencyPlan/id/'+id;
});