function auto_complete_input(id,findindex,fun,letter){
    var len = typeof letter !== "undefined" ? letter : 3 ;
    var input = $("#"+id);
    var myobj;
    input.after("<ul id='"+id+"-res' class='auto-list'></ul>");
    var target = $("#"+id+"-res");
    var icache=[],results,list,c=-1,index=[];
    input.on("keydown",function(e){
        if(e.which===13){
            e.preventDefault();
        }
    });
    input.on("keyup",function(e){
        var key = e.which;
        var keyarr = [13,27,37,38,39,40]; //8=backspace 27=escape 32=spacebar
        if(keyarr.indexOf(key)!==-1&& typeof list !== "undefined"){
            e.preventDefault();
            var lilen = list.length;
            switch(key){
                case 40:
                    c++;
                    break;
                case 38:
                    c--;
                    break;
                case 13:
                    fun(list.eq(c));
                    break;
                case 27:
                    input.val("");
                    target.html("");
                    c=-1;
                    break;
            }
            if(c>-1&&c<lilen){
                list.removeClass("search-sel");
                list.eq(c).addClass("search-sel");
            } else {
                list.removeClass("search-sel");
                c=-1;
            }
        } else {
            var find = input.val();
            find = find.replace(/^[\s,]+|[\s,]+$/g,"");
            find = find.replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
            if(find.length>=len){
                var arrf = get_asearch(find);
                var f0 = arrf[0];
                //console.log(find);
                //console.log(icache);
                //console.log(check_cache(f0));
                if(check_cache(f0)){
                    if(arrf.length>1){
                        results = search2(arrf);
                    } else {
                        results = search(find);
                    }
                    draw(results);
                } else {
                    update_index(f0);
                }
            } else {
                target.html("");
            }
        }
    });

    function get_asearch(f){
        f = f.replace(/[, ]+$/g,"");
        f = f.replace(/[, ]+/g,",");
        return f.split(",");
    }
    function check_cache(f){
        var res = false;
        if(icache.indexOf(f) !== -1){
            res = true;
        } else {
            var filter;
            $.each(icache,function(i,item){
                filter = new RegExp("^("+item+").*?","i");
                if(f.search(filter)!==-1){
                    res = true;
                }
            });
        }
        return res;
    }
    function search(f){
        var res1,res2,ress;     //1 word
        res1 = $.grep(index,function(n){
            return n.search(RegExp("^("+f+")","i")) !== -1;    // i= case insensitive 
        });
        res2 = $.grep(index,function(n){
            return n.search(RegExp(".+(?="+f+")","i")) !== -1;
        });
        ress = res1.concat(res2.filter(function(item){
            return res1.indexOf(item) < 0;
        }));
        return ress;
    }
    function search2(af){
        var len,x,nf,res1,nf="",sf="",res2,ress;
        len = af.length;
        for(x=0;x<len;x++){
            if(x===0){
                nf += "^("+af[x]+")";       //start and contain
                sf += "(?=.*"+af[x]+")";    //contain every word
            } else {
                nf += "(?=.*"+af[x]+")";
                sf += "(?=.*"+af[x]+")";
                if(x===(len-1)){
                    nf += ".*$";
                    sf += ".*$";
                }
            }
        }
        res1 = $.grep(index,function(n){
           return n.search(RegExp(nf,"i")) !== -1;    // i= case insensitive 
        });
        res2  = $.grep(index,function(n){
           return n.search(RegExp(sf,"i")) !== -1;    // i= case insensitive 
        });
        ress = res1.concat(res2.filter(function(item){
            return res1.indexOf(item) === -1;
        }));
        return ress;
    }
    function draw(ress){
        var html="",ele,id;
        if(ress.length>0){
            for(ele in ress){
                id = myobj[ress[ele]];
                html += "<li class='search-resli' cid='"+id+"'>"+ress[ele]+"</li>";
            }
        } else {
            html += "<li class='search-resli nores'>ไม่พบข้อมูล</li>";
        }
        target.addClass("shown");
        target.html(html);
        c=-1;
        list = $(".search-resli");
        list.on("click",function(){
            fun($(this));
            target.html("");
            c=-1;
        });
    }
    function update_index(find){
        var list = $(".auto-list");
        list.addClass("inload");
        var t_index;
        findindex(find,suc,fail);
        function suc(data){
            list.removeClass("inload");
            icache.push(find);
            myobj = $.extend({},myobj,data);
            t_index = Object.keys(data);
            index = index.concat(t_index.filter(function(item){
                return index.indexOf(item) < 0;
            }));
            //console.log(myobj);
            //console.log(index);
            input.trigger("keyup");
        }
        function fail(data){
            //console.log(data);
            $(".search-resli").removeClass("inload");
        }
    }
    /*
    function up_tags(name){
        var ntag={};
        ntag[name] = kindex[name];
        cprod = $.extend({},cprod,ntag);
        draw_res();
    }
    function draw_res(){
        var tg = $("#"+id+"-list");
        var html = "";
        $.each(cprod,function(k,v){
            html += "<span class='tag-remove' pid='"+v+"'><span class='icon-delete-circle'></span>"+k+"<input type='hidden' name='"+id+"-list[]' value='"+v+"' /></span>";
        });
        ////console.log(html);
        tg.html(html);
        var del = $(".tag-remove");
        del.on("click",function(){
            var deltext = $(this).text();
            delete cprod[deltext];
            $(this).remove();
            ////console.log(cprod);
        });
    }
    */
}


