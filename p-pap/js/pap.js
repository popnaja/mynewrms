function response_index(){
$(document).ready(function(){
    adj_pad();
    $(window).on("resize",function(){
        adj_pad();
    });
    function adj_pad(){
        var logo = $(".banner");
        var h = $(window).height();
        //console.log(logo.innerHeight());
        if(logo.innerHeight()>h-110){
            logo.css("margin-top","110px");
        } else {
            var top = (h-logo.innerHeight()-$("footer").height())/2;
            //console.log(top);
            logo.css("margin-top",top);
        }
    }
});
}
function lay_guide(paper,grip,bleed){
    $(document).ready(function(){
        var cbleed = 0.3;
        var input = $(".lay-input input");
        var rim = 1;
        var ch,cw,ih,iw,master,clay,ilay,mw,ml,info,selinfo,divname;
        var in_to_cm = 2.54;
        var ctg = $(".lay-cover");
        var itg = $(".lay-inside");
        var rc  = $(".lay-cover-r");
        var ri  = $(".lay-inside-r");
        var lay_c_rem = $(".lay-c-rem");
        var lay_i_rem = $(".lay-i-rem");
        var lay_c_remr = $(".lay-c-rem-r");
        var lay_i_remr = $(".lay-i-rem-r");
        var n = paper.length;
        var cover_thick = $("#cover_thick");
        var trem;
        var cut = $("select[name='pdiv[]']");
        var box = $(".lay-box-c, .lay-box-i, .lay-box-cr, .lay-box-ir");
        input.on("blur",function(){
            cal_guide();
        });
        cover_thick.on("change",function(){
            rim = parseFloat($(this).val());
            cal_guide();
        });
        cut.on("change",function(){
            box.removeClass("box-active");
            cal_guide();
        });
        cal_guide();
        function cal_guide(){
            var h = parseFloat($("#height").val());
            var w = parseFloat($("#width").val());
            if(h>0&&w>0){
                ch = Math.ceil((h+cbleed*2)*100)/100;
                cw = Math.ceil((w*2+bleed*2+rim)*100)/100;
                ih = Math.ceil((h+bleed*2)*100)/100;
                iw = Math.ceil((w+bleed*2)*100)/100;
                //cover size
                $(".size-cover").html(ch+" x "+cw);
                //inside size
                $(".size-inside").html(ih+" x "+iw);
                
                for(var i=0;i<n;i++){
                    master = $.parseJSON(paper[i]['psize']);
                    var pid = paper[i]['op_id'];
                    var pdiv = cut.eq(i).val();
                    if(pdiv==2){
                        mw = master.length/pdiv*in_to_cm-grip; //คำนวณเป็นcm - กริ๊ป 1cm
                        ml = master.width*in_to_cm;
                        divname = master.width+"x"+master.length+"(ผ่าครึ่ง)";
                    } else {
                        mw = master.width*in_to_cm-grip; //คำนวณเป็นcm - กริ๊ป 1cm
                        ml = master.length*in_to_cm;
                        divname = master.width+"x"+master.length;
                    }
                    clay = Math.floor(mw/ch)*Math.floor(ml/cw);
                    ctg.eq(i).html("("+Math.floor(mw/ch)+"x"+Math.floor(ml/cw)+") "+clay);
                    trem = (1-clay*(ch*cw)/(mw*ml))*100
                    lay_c_rem.eq(i).html(Math.round(trem)+"%**");
                    info = [mw,ml,ch,cw,clay,grip];
                    selinfo = [divname,pid,pdiv,clay];
                    $(".lay-box-c").eq(i).attr("data",info.toString()).attr("cdata",selinfo.toString());
                    
                    ilay = Math.floor(mw/ih)*Math.floor(ml/iw);
                    itg.eq(i).html("("+Math.floor(mw/ih)+"x"+Math.floor(ml/iw)+") "+ilay);
                    trem = (1-ilay*(ih*iw)/(mw*ml))*100
                    lay_i_rem.eq(i).html(Math.round(trem)+"%**");
                    info = [mw,ml,ih,iw,ilay,grip];
                    selinfo = [divname,pid,pdiv,ilay];
                    $(".lay-box-i").eq(i).attr("data",info.toString()).attr("cdata",selinfo.toString());
                    
                    //reverse
                    clay = Math.floor(mw/cw)*Math.floor(ml/ch);
                    rc.eq(i).html("("+Math.floor(mw/cw)+"x"+Math.floor(ml/ch)+") "+clay);
                    trem = (1-clay*(ch*cw)/(mw*ml))*100
                    lay_c_remr.eq(i).html(Math.round(trem)+"%**");
                    info = [mw,ml,cw,ch,clay,grip];
                    selinfo = [divname,pid,pdiv,clay];
                    $(".lay-box-cr").eq(i).attr("data",info.toString()).attr("cdata",selinfo.toString());
                    
                    ilay = Math.floor(mw/iw)*Math.floor(ml/ih);
                    ri.eq(i).html("("+Math.floor(mw/iw)+"x"+Math.floor(ml/ih)+") "+ilay);
                    trem = (1-ilay*(ih*iw)/(mw*ml))*100
                    lay_i_remr.eq(i).html(Math.round(trem)+"%**");
                    info = [mw,ml,iw,ih,ilay,grip];
                    selinfo = [divname,pid,pdiv,ilay];
                    $(".lay-box-ir").eq(i).attr("data",info.toString()).attr("cdata",selinfo.toString());
                }
            }
        }
        
        box.on("click",function(){
            box.removeClass("box-active");
            $(this).addClass("box-active");
            var da = $(this).attr("data").split(",");
            //show info to select
            var seldata = $(this).attr("cdata").split(",");
            var cclass = $(this).children("span").attr("class");
            if(cclass.search("cover")===-1){
                $("#show-lay-cover h4").text("เนื้อใน lay บน "+seldata[0]+" ได้ "+seldata[3]);
                $("#lay-sel").html("<input type='button' value='เลือก' />");
                $("#lay-sel input").on("click",function(){
                    var data = seldata;
                    $("#inside_paper").val(data[1]);
                    $("#inside_div").val(data[2]);
                    $("#inside_lay").val(data[3]);
                });
            } else {
                $("#show-lay-cover h4").text("ปก lay บน "+seldata[0]+" ได้ "+seldata[3]);
                $("#lay-sel").html("<input type='button' value='เลือก' />");
                $("#lay-sel input").on("click",function(){
                    var data = seldata;
                    $("#cover_paper").val(data[1]);
                    $("#cover_div").val(data[2]);
                    $("#cover_lay").val(data[3]);
                });
            }
            show_lay(parseFloat(da[0]),parseFloat(da[1]),parseFloat(da[2]),parseFloat(da[3]),parseFloat(da[4]),parseFloat(da[5]));
        });
    });
}
function show_lay(mh,mw,h,w,no,gr){
    var tg = $("#show-lay");
    var pw = tg.outerWidth();
    var rto = pw/mw;
    var ph,nh,nw,ngr;
    ph = (mh+gr)*rto;
    nh = h*rto;
    nw = w*rto;
    ngr = gr*rto;
    //var data = [mh,mw,h,w,no,gr,pw,ph,nh,nw,ngr];
    //console.log(data.toString());
    
    //set paper
    tg.outerWidth(pw).outerHeight(ph);
    tg.html("");
    //grip
    tg.append("<div class='paper-grip'></div>");
    var grip = $(".paper-grip");
    grip.outerWidth(pw).outerHeight(ngr);
    //page
    for(var i=0;i<no;i++){
        tg.append("<div class='page-lay'></div>");
    }
    var page = $(".page-lay");
    page.outerWidth(nw).outerHeight(nh)
    
}
function view_morecomp(){
    var n = $(".quote-comp").length;
    var hid = $(".quote-comp.form-hide").length;
    var next = n-hid;
    $(".quote-comp").eq(next).removeClass("form-hide");
    if(next===n){
        $("#more-comp-but").hide();
    }
}
function check_comp(e){
    var page = $("input[name^=page]");
    var paper = $("select[name^=paper_type]");
    var weight = $("select[name^=paper_gram]");
    var check = true;
    var v,i,pp,ww;
    $.each(page,function(){
        v = $(this).val();
        i = page.index($(this));
        pp = paper.eq(i);
        ww = weight.eq(i);
        if(v>0&&(pp.val()==0||ww.val()==0)){
            check = false;
            if(pp.val()==0){
                err_hilight(pp);
            }
            if(ww.val()==0){
                err_hilight(ww);
            }
        }
    });
    if(!check){
        e.preventDefault();
        show_submit_error("ez-msg");
    }
}
function update_price(){
    $(document).ready(function(){
        var data = {};
        var url = $("form").attr("action");
        data['request'] = "update_qprice";
        data['q_price'] = $("#q_price").val();
        data['qid'] = $("#qid").val();
        post_ajax(data,url,true);
    });
}
function add_papera(e){
    e.preventDefault();
    var but = $("#add_allo");
    var f,t,a;
    but.on("click",function(){
        f = $("#from").val();
        t = $("#to").val();
        a = $("#allo").val();
    });
}
function quote_adj(id){
    var tt = $("#"+id+" .tb-total-quote");
    tt.on("click",function(){
        $("#q_price").val(stfloat($(this).html()));
    });
    var mgtt = $("#"+id+" .tb-mg-input-tt");
    var mg = $("#"+id+" .tb-mg-input");
    var pr = $("#"+id+" .tb-pr");
    var cos = $("#"+id+" .tb-stcost");
    var i,nmg,cost;
    mgtt.on("change",function(){
        mg.val($(this).val());
        update_mg()
        tt.html(ntt());
    });
    mg.on("change",function(){
        i = mg.index($(this));
        nmg = 1+$(this).val()/100;
        cost = cos.eq(i).html();
        pr.eq(i).html(numformat(nmg*stfloat(cost)));
        tt.html(ntt());
    });
    function update_mg(){
        $.each(mg,function(){
           i = mg.index($(this));
           nmg = 1+$(this).val()/100;
           cost = cos.eq(i).html();
           pr.eq(i).html(numformat(nmg*stfloat(cost)));
        });
    }
    function ntt(){
        var ntt = 0;
        $.each(pr,function(){
            ntt += stfloat($(this).html());
        });
        return numformat(ntt);
    }
}
function stfloat(st){
    return parseFloat(st.replace(",",""));
}
function quote_search(){
    var id = "scid";
    var re = $("#redirect").val();
    auto_complete_input(id,findex,show);
    function show(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            $("#"+id).val(ele.html());
            window.location.replace(re+"?qid="+ele.attr("cid"));
        }
    }
    function findex(find,f1,f2){
        var data = {};
        data['request'] = "find_quote";
        data['f'] = find;
        var url = $("#ajax_req").val();
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data:data,
            success: function(response) {
                f1(response);
            },
            error: function(err){
                f2("ERROR"+JSON.stringify(err));
            }
        });
    }
}

function quote_function(cp,ip){
$(document).ready(function(){
    //hide cover
    var title = $("#sel-name");
    var but = $("#view-more-but");
    hid_cover();
    $("#type").on("change",function(){
       hid_cover();
    });
    function hid_cover(){
        var type = parseInt($("#type").val());
        var bind = $("#bind-sec");
        var fold = $("#folding_0");
        if(type===10||type===69){
            title.html("เนื้อใน");
            but.show();
            bind.show();
            fold.parent().addClass("form-hide");
       } else {
            $("#paper_type").val("0");
            title.html("ชิ้นงาน");
            but.hide();
            $("#binding").val("0");
            bind.hide();
            if(type===11){
                fold.parent().removeClass("form-hide");
            } else {
                fold.parent().addClass("form-hide");
            }
       }
    }

    //auto search customer
    var id = "scid";
    var target = $("#cid");
    auto_complete_input(id,findex,show);
    function show(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            $("#"+id).val(ele.html());
            target.val(ele.attr("cid"));
            //get new select
            var url = $("form").attr("action");
            var cdata = {};
            cdata['request'] = "get_selcontact";
            cdata['cid'] = ele.attr("cid");
            post_ajax(cdata,url,true);
        }
    }
    function findex(find,f1,f2){
        var data = {};
        data['request'] = "find_cid";
        data['f'] = find;
        data['pauth'] = $("#pauth").val();
        data['uid'] = $("#uid").val();
        var url = $("form").attr("action");
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data:data,
            success: function(response) {
                f1(response);
            },
            error: function(err){
                f2("ERROR"+JSON.stringify(err));
            }
        });
    }

    //fix paper
    if($.type(cp)!=="undefined"){
        //gram
        var ctype = $("#paper_type");
        ctype.on("change",function(){
            var t = $(this).val();
            if(t!=="0"){
                filter_gram(".tg_c_pgram",cp,t);
            }
        });

        //filter gram inside
        var itype = $(".in_ptype").children("select");
        itype.on("change",function(){
            var i = itype.index($(this));
            var t = $(this).val();
            if(t!=="0"){
                filter_gram(".tg_i_pgram",ip,t,i);
            }
        });
    }

    //show margin
    var qprice = $("#q_price");
    qprice.on("change",function(){
        var ttc = $("#ttcost").val();
        var margin = ttc > 0 ? ($(this).val()-ttc)*100/ttc : "*";
        $(".show-margin").text(numformat(margin,2));
    });
    
    //adjust price
    var peru = $("#peru");
    var price = $("#q_price");
    peru.on("change",function(){
        price.val($(this).val()*$("#amount").val());
        price.trigger("change");
    });
    price.on("change",function(){
        peru.val($(this).val()/$("#amount").val());
    })
});
}
function filter_paper(sid){
    var t;
    var data = {};
    data['request'] = "filter_paper";
    data['sid'] = sid;
    var url = $("form").attr("action");
    pg_loading(true);
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(res) {
            pg_loading(false);
            //console.log(res);
            //show size
            $("#csize").val(res[0][0]);
            $("input[name^='isize']").val(res[0][1]);

            //type
            var sel_ctype = $(".tg_c_ptype");
            sel_ctype.html(res[1]);
            //filter type inside
            var sel_itype = $(".tg_i_ptype");
            sel_itype.html(res[2]);


            //gram
            var ctype = $("#paper_type");
            ctype.off("change");
            ctype.on("change",function(){
                t = $(this).val();
                if(t!=="0"){
                    filter_gram(".tg_c_pgram",res[3][0],t);
                }
            });

            //filter gram inside
            var itype = $(".in_ptype").children("select");
            itype.off("change");
            itype.on("change",function(){
                var i = itype.index($(this));
                t = $(this).val();
                if(t!=="0"){
                    filter_gram(".tg_i_pgram",res[3][1],t,i);
                }
            });
        },
        error: function(err){
            console.log("ERROR"+JSON.stringify(err));
            pg_loading(false);
        }
    });
}
function filter_gram(cl,size,type,i){
    var data = {};
    data['request'] = "filter_gram";
    data['size'] = size;
    data['type'] = type;
    var url = $("form").attr("action");
    pg_loading(true);
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(res) {
            pg_loading(false);
            if($.type(i)!=="undefined"){
                $(cl).eq(i).html(res);
            } else {
                $(cl).html(res);
            }
        },
        error: function(err){
            console.log("ERROR"+JSON.stringify(err));
            pg_loading(false);
        }
    });
}

function search_job(id,url){
    //autocomplete order-ref
    auto_complete_input(id,findex,show);
    function findex(find,f1,f2){
        var data = {};
        data['request'] = "find_job";
        data['f'] = find;
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data:data,
            success: function(response) {
                f1(response);
            },
            error: function(err){
                f2("ERROR"+JSON.stringify(err));
            }
        });
    }
    function show(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            $("#"+id).attr("oid",ele.attr("cid"));
            $("#"+id).val(ele.text());
        }
    }
}
function search_size(url){
$(document).ready(function(){
    //autocomplete job size
    var id = "search_size";
    var target = $("#sid");
    auto_complete_input(id,findex1,show1,2);
    function findex1(find,f1,f2){
        var data = {};
        data['request'] = "find_size";
        data['f'] = find;
        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data:data,
            success: function(response) {
                f1(response);
            },
            error: function(err){
                f2("ERROR"+JSON.stringify(err));
            }
        });
    }
    function show1(ele){
        if(ele.hasClass("nores")){
            $("#"+id).val("");
        } else {
            target.val(ele.attr("cid"));
            $("#"+id).val(ele.text());
            filter_paper(ele.attr("cid"));
        }
    }
});
}
function del_quote(){
$(document).ready(function(){
    var del = $("#del-quote");
    var url = $("#ajax_req").val();
    del.on("click",function(){
        if(confirm("คุณแน่ใจแล้วที่จะลบรายการใบเสนอราคา")){
            var data = {};
            data['request'] = "delete_quote";
            data['qid'] = $("#qid").val();
            data['redirect'] = $("#redirect").val();
            post_ajax(data,url);
        }
    });
});
}
function adj_role(){
$(document).ready(function(){
   var sel = $("#adj_all");
   var tg = $("select[name='auth[]']");
   sel.on("change",function(){
        tg.val($(this).val());
   });
});
}