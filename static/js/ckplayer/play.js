var nowD=0;//目前播放的视频的编号(在数组里的编号)
function playvideo(n){
	nowD=n;
	var flashvars={
		f:videoarr[n],
		c:0,//是否读取文本配置,0不是，1是
		p:1,//视频默认0是暂停，1是播放，2是不加载视频
		e:0,
		loaded: 'loadedHandler',
		my_title:encodeURIComponent(document.title),
		my_url: encodeURIComponent(window.location.href)
	};
	CKobject.embed('/static/js/ckplayer/ckplayer.swf','a1','ckplayer_a1','100%',heights,false,flashvars,html5arr[n]);
	
	//这里是用来改变右边列表背景色
	for(i=0;i<videoarr.length;i++){
		if(i!=nowD){
			$('#vli_'+i).removeClass('action');
		}else{
			$('#vli_'+i).addClass('action');
		}
	}
}
function playerstop(){
	nowD++;
	if(nowD>=videoarr.length-1){
		nowD=0;
	}
	playvideo(nowD);
}
function loadedHandler() {
	if (CKobject.getObjectById('ckplayer_a1').getType()) {
		addPlayListener();
	}
	else {
		addPlayListener();
	}
}
function timeHandler(t) {
	if (t > -1) {
		SetCookie("Time", t);
	}
}
//增加播放监听
function addPlayListener() {
	//说明使用html5播放器
	if (CKobject.getObjectById('ckplayer_a1').getType()) {
		CKobject.getObjectById('ckplayer_a1').addListener('play', playHandler);
	}
	else {
		CKobject.getObjectById('ckplayer_a1').addListener('play', 'playHandler');
	}
}
function playHandler() {
	//alert('因为注册了监听播放，所以弹出此内容，删除监听将不再弹出');
	removePlayListener();
	CKobject.getObjectById('ckplayer_a1').videoSeek(getCookie("Time"));
	addTimeListener();
}
//删除播放监听事件
function removePlayListener() {
	//说明使用html5播放器
	if (CKobject.getObjectById('ckplayer_a1').getType()) {
		CKobject.getObjectById('ckplayer_a1').removeListener('play', playHandler);
	}
	else {
		CKobject.getObjectById('ckplayer_a1').removeListener('play', 'playHandler');
	}
}
//增加时间监听
function addTimeListener() {
	//说明使用html5播放器
	if (CKobject.getObjectById('ckplayer_a1').getType()) {
		CKobject.getObjectById('ckplayer_a1').addListener('time', timeHandler);
	}
	else {
		CKobject.getObjectById('ckplayer_a1').addListener('time', 'timeHandler');
	}
}
//写cookies函数
//两个参数，一个是cookie的名子，一个是值
function SetCookie(name, value){
	var Days = 7; //此 cookie 将被保存 7 天
	var exp = new Date(); //new Date("December 31, 9998");
	exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
	document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}
//取cookies函数
function getCookie(name){
	var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
	if (arr != null) return unescape(arr[2]); return null;
}
//关灯
var box = new LightBox();
function closelights(){
	box.Show();
	CKobject._K_('lists').style.display='none';
	CKobject._K_('a1').style.width=widths+'px';
	CKobject._K_('a1').style.height=heights+'px';
	CKobject.getObjectById('ckplayer_a1').width=widths;
	CKobject.getObjectById('ckplayer_a1').height=heights;
}
//开灯
function openlights(){
	box.Close();
	CKobject._K_('lists').style.display='';
	CKobject._K_('a1').style.width=widths+'px';
	CKobject._K_('a1').style.height=heights+'px';
	CKobject.getObjectById('ckplayer_a1').width=widths;
	CKobject.getObjectById('ckplayer_a1').height=heights;
}