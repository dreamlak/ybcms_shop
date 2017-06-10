/**
 * alimama广告辅助
 * @authors ZP (zhanpeng.zhu@360che.com)
 * @date    2016-06-21 10:52:01
 * @version 2.0
 */

!function(){
	function ansycView(id,tanix){
		var place = document.querySelector('#PAGE_AD_' + id);	
		if(place){
			_mmW.q({
			    aid:"mm_112435607_14478061_" + id,
			    serverbaseurl:"afpeng.alimama.com/",
			    destid:"PAGE_AD_" + id,
			    async:tanix ? 0 : 1,
			    adNone:function(){
			    	place.style.display = 'none';	
			    }
			})
		}
	};
	window.ansycView = ansycView;
	if(window.ads_material && Array.isArray(ads_material) && ads_material.length){
		ads_material.forEach(function(item,index){
			var id = item.id;	
			ansycView(id,item.tanix);
		})
	};
}();