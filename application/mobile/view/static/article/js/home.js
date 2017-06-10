window.addEventListener('load', function(){


    // 焦点图
    var swiper = new Swiper('.home-focus', {
        pagination: '.swiper-pagination',
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        paginationClickable: true,
        spaceBetween: 0,
        centeredSlides: true,
        autoplay:3000,
        autoplayDisableOnInteraction: false,
        loop:true
    });
   

    // 品牌滑动切换
    var brandSwiper = new Swiper('.brand', {
        paginationClickable: true,
        spaceBetween: 0,
        centeredSlides: true,
        onSlideChangeEnd: function(swiper){
          tabSwitchByIndex(swiper.activeIndex, '.brand');
        }
    });
    // 车型排行榜滑动切换
    var modelRankSwiper = new Swiper('.module-tab', {
        paginationClickable: true,
        spaceBetween: 0,
        centeredSlides: true,
        onSlideChangeEnd: function(swiper){
          tabSwitchByIndex(swiper.activeIndex, '.module-tab');
        }
    });

    function tabSwitchByIndex(index, parentClass){
        var nav = document.querySelector(parentClass + ' .tab-title');
        var cur = nav.querySelectorAll('span')[index];
        
        var visible = nav.parentNode.querySelector('.visible'),
            selected = nav.querySelector('.selected'),
            oLeft = cur.getBoundingClientRect().left,
            tabs_slider = nav.parentNode.querySelector('.tabs-slider'),
            oWidth = cur.offsetWidth;

        if(tabs_slider)
        {
            tabs_slider.style.cssText = '-webkit-transform:translate3d('+ oLeft +'px,0,0);transform:translate3d('+ oLeft +'px,0,0);width:'+ oWidth +'px;';
        }

        visible && visible.classList.remove('visible');
        selected && selected.classList.remove('selected');
        cur.classList.add('selected');
        nav.parentNode.querySelector('#'+ cur.dataset['rel']).classList.add('visible');
    }

    function tabSwitch(nav,cur,name){
     var visible = nav.parentNode.querySelector('.visible'),
         selected = nav.querySelector('.selected'),
         oLeft = cur.getBoundingClientRect().left,
         tabs_slider = nav.parentNode.querySelector('.tabs-slider'),
         oWidth = cur.offsetWidth;

         if(tabs_slider)
         {
             tabs_slider.style.cssText = '-webkit-transform:translate3d('+ oLeft +'px,0,0);transform:translate3d('+ oLeft +'px,0,0);width:'+ oWidth +'px;';
         }

         visible && visible.classList.remove('visible');
         selected && selected.classList.remove('selected');
         cur.classList.add('selected');
         nav.parentNode.querySelector('#'+ cur.dataset['rel']).classList.add('visible');
         
         var index = nav.querySelectorAll('span').indexOf(cur);
         if(index != -1){
            if(name == 'brandSwiper')
            {
                brandSwiper.slideTo(index, 0.2, false);
            }
            else if(name == 'modelRankSwiper')
            {
                modelRankSwiper.slideTo(index, 0.2, false);

            }
         }
    }


    // 品牌点击切换
    var brandNav = document.querySelector('.brand .tab-title');
    tabSwitch(brandNav,brandNav.querySelector('.selected'));
    brandNav.children.addEventListener('click',function() {
     tabSwitch(brandNav,this,'brandSwiper');
    });

    // 最新帖子点击切换
    var newsPost = document.querySelector('.news-wrap .tab-title');
    newsPost.children.addEventListener('click',function() {
     tabSwitch(newsPost,this);
    });

    // 车型排行榜点击切换
    var modelRank = document.querySelector('.model-rank .tab-title');
    tabSwitch(modelRank,modelRank.querySelector('.selected'));
    modelRank.children.addEventListener('click',function() {
     tabSwitch(modelRank,this,'modelRankSwiper');

    });

    

    // 图片后加载
    function fadeIn() {
        this.style.opacity = 0;
        var img = this;
        setTimeout(function () {
            img.style.webkitTransition = 'opacity .4s';
            img.style.opacity = 1;
            img.removeEventListener('load', fadeIn);
        }, 0);
    }
    function fadeInLoad(img) {
        img.style.opacity = 0;
        img.addEventListener('load', fadeIn);
    }

    HTMLImageElement.prototype.lazyLoad = function () { 
        var a = this.getAttribute("src"), 
            b = this.getAttribute(".src") || this.getAttribute("itemref") || this.getAttribute("rel"); 
        b && a !== b && (this.setAttribute("src", b), fadeInLoad(this));
        return b; 
    };

     // 懒加载
    function lazyLoad() {
        var images = document.querySelectorAll('img:not([src])');
        images.forEach(function (image) {
            if (!image.getAttribute('.src')) {
                //image.src = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
            } else {
                var h = document.documentElement.clientHeight, bounds = image.getBoundingClientRect();
                if (bounds.bottom > 0 - h && bounds.top < h + h) {
                    image.lazyLoad();
                }
            }
        });
        if (!lazyLoad.bind && document.querySelector('img:not([src])')) {
            lazyLoad.bind = 1;
            window.addEventListener('scroll',lazyLoad.onScroll);
        }
    }
    lazyLoad.onScroll = function () {
        if (lazyLoad.timer) clearTimeout(lazyLoad.timer);
        lazyLoad.timer = setTimeout(lazyLoad, 0);
    };
    lazyLoad.bind = 1, window.addEventListener('scroll', lazyLoad.onScroll), lazyLoad();

});