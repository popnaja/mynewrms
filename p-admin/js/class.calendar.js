function mycd_height(adj){
    $(document).ready(function(){
        var box = $(".mycd-box");
        var vh = $(window).height();
        var navh = $(".mycd-nav").outerHeight(true);
        box.height(vh-adj-navh);
    });
}
function mycd_change(){
    $(document).ready(function(){
        //change month
        var but = $(".mycd-c-month");
        var url = $("#ajax_req").val();
        but.on("click",function(){
            if(!$(this).hasClass("mycd-disable")){
                var data = {};
                data['request'] = "mycd_change_month";
                data['year'] = $(this).attr("year");
                data['month'] = $(this).attr("month");
                data['type'] = $(this).attr('cdtype');
                post_ajax(data,url)
            }
        });
        
        //change type
        var tbut = $(".mycd-switch-type");
        tbut.on("click",function(){
            if(!$(this).hasClass("mycd-active")){
                var data = {};
                data['request'] = "mycd_change_type";
                data['type'] = $(this).attr('cdtype');
                data['year'] = $(this).attr("year");
                data['month'] = $(this).attr("month");
                post_ajax(data,url)
            }
        });
        
    });
}

