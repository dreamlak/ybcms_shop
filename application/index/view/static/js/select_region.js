//德高定位--------------------------
var deferred = $.Deferred();//创建一个Deferred（延迟）对象,它可以注册多个回调到回调队列
//AMap.Geolocation 定位插件，整合了浏览器定位、精确IP定位、sdk辅助定位多种手段
var geolocation = new AMap.Geolocation({
	enableHighAccuracy: true,//是否使用高精度定位，默认:true
	timeout: 10000, //超过10秒后停止定位，默认：无穷大
	buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
	zoomToAccuracy: true, //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
	buttonPosition: 'RB'
});
//定位
geolocation.getCurrentPosition();
//返回定位信息
AMap.event.addListener(geolocation, 'complete', function(data) {
	if (data && data.addressComponent && data.addressComponent.adcode) {
		var province = data.addressComponent.province;//所在省
		var city = data.addressComponent.city;//所在城市
		var citycode = data.addressComponent.citycode;//所在城市编码
		var district = data.addressComponent.district;//所在区
		var adcodes = data.addressComponent.adcode;//所在区域编码
		var township = data.addressComponent.township;//所在乡镇
		var street = data.addressComponent.street;//所在街道
		var accuracy = data.accuracy;//精度范围，单位：米
		var location_type = data.location_type;//定位结果的来源，可能的值有:'html5'、'ip'、'sdk'
		var formattedAddress = data.formattedAddress;//地址
		var lng = data.position.getLng();//经度
		var lat = data.position.getLat();//纬度
		try {
			//console.log(region_code2);return false;
			deferred.resolve(adcodes);//deferred.resolve(args),结果加入回调队列。可用$.when(deferred).always(),调用回调
		}catch(e) {
			deferred.reject();//回调错误信息
		}
	}
});
//返回定位出错信息
AMap.event.addListener(geolocation, 'error', function(data) {
	deferred.reject();//回调错误信息
});
//------------------------------

$(function () {
	var local_region_code = "522601";
	//获取定位信息
	$.when(deferred).always(function(region_code) {
		if (region_code) {
			local_region_code = region_code;
		}
		//变更配送地址
		var region_chooser = $(".region-chooser-container").regionchooser({
			url:'/common/Area/getRegionJson.html',
			value: local_region_code,
			change: function(value, names, is_last) {
				if (!is_last) return;
				//记录当前地址选择
				local_region_code = value;
				//在此 选择地址后计算运费
				$('.freight-info').html('运费：￥18.00');
			}
		});
	});
	/*
	//鼠标经过、离开地区文字，显示、隐藏地区选择层，点击关闭按钮隐藏地区选择层
	var city_time=null;
	$('.region,.region-chooser-box').mouseover(function(){
	  	clearTimeout(city_time);
	  	$('.region-chooser-box').show();    
	  	$('.region').addClass('active');
	});
	$('.region,.region-chooser-box').mouseout(function(){
	  	city_time=setTimeout(function(){
	  		$('.region-chooser-box').hide();
			$('.region').removeClass('active');
		},200);
	});
	$('.region-chooser-close').click(function(){
		$('.region-chooser-box').hide();	
		$('.region').removeClass('active');
	});
	*/
});