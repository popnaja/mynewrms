function ez_slider(){
    var item = $(".ez-slide-item");
    var frame = $(".ez-slide-frame");
    var cont = $(".ez-slide-cont");
    var windw = frame.width();
    var len = $(".ez-slide-item").length;
    var padding = 0;
    var maxh = 0,h;
    show_dotnav(len);
    //set pic width
    var iw = windw;
    item.css({
        "width":iw,
        "padding-left":"0",
        "padding-right":"0"
    });
    //set cont width
    cont.width(iw*len);
    $(window).resize(function(){
        windw = frame.width();
        iw = windw;
        item.css({
            "width":iw,
            "padding-left":"0",
            "padding-right":"0"
        });
        cont.width(iw*len);
        n = (iw)*len/windw;
        csize = windw;
    });
    //slider
    var n = len;
    var csize = windw;
    var but = $(".ez-slide-prev,.ez-slide-next");
    var c = 0;
    var moving = setInterval(automove,5000);            //automove slide
    var myslide = $("#home-ads");
    myslide.on("swipeleft",function(){
        c++;
        check_c();
    });
    myslide.on("swiperight",function(){
        c--;
        check_c();
    });
    but.on("click",function(){
        c = $(this).hasClass('ez-slide-next')? ++c : --c ;
        check_c();
    });
    var dotnav = $(".ez-dot-nav");
    dotnav.on("click",function(){
        c = $(this).attr("sno");
        check_c();
    });
    var frame = $(".ez-slide-frame");
    frame.on("mouseenter",function(){
        clearInterval(moving);
    });
    frame.on("mouseleave",function(){
        clearInterval(moving);
        moving = setInterval(automove,5000);
    });
    $(window).on("blur",function(){
        clearInterval(moving);
    });
    $(window).on("focus",function(){
        clearInterval(moving);
        moving = setInterval(automove,5000);
    });
    
    function check_c(){
        if(c>(n-1)){
            move((-csize)*(n-1)-30,200);
            move((-csize)*(n-1),100);
            c=n-1;
        } else if(c<0){
            move(30,200);
            move(-5,100);
            move(0,140);
            c=0;
        } else {
            move(-csize*(c),400);
        }
        dotnav.removeClass("dot-nav-active");
        dotnav.eq(c).addClass("dot-nav-active");
    }
    function automove(){
        if(c>=(n-1)){
            c=0;
        } else {
            c++;
        }
        check_c();
    }
}
function show_dotnav(n){
    var list = $(".ez-slide-dot ul");
    var classact;
    for (var i=0; i<n ; i++) {
        classact = (i===0?"dot-nav-active":"");
        list.append("<li class='ez-dot-nav "+classact+"' sno='"+(i)+"'></li>");
    }
}
function move(c,t){
    $('.ez-slide-cont').animate({
        left: c
    },t,'swing');
}