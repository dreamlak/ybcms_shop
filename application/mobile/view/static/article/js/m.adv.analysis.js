/*
	* @description: 广告位点击统计
	* @authors: 	ZP(zhanpeng.zhu@360che.com)
	* @update: 		2016-03-29 17:10:32
	* @version: 	v1.0
*/
window.addEventListener('load',function(){
	function analysis(i){
		var timeStamp = +new Date();
		var img = document.createElement('img');
		img.src = 'http://stats.360che.com/m_advert_click.htm?page='+ location.href +'&place='+ i +'&ts=' + timeStamp;
	}
	[].forEach.call(document.querySelectorAll('.m-ad-item'),function(item,index){
		item.addEventListener('click',function(e){
			e.preventDefault();
			var url = this.href || this.getAttribute('href');
			analysis(index);
			this.timer && clearTimeout(this.timer);
			this.timer = setTimeout(function(){
				location.href = url;
			},300);
		})
	});
})


