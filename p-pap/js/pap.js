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
function lay_guide(paper,grip1,bleed,grip2){
    $(document).ready(function(){
        var grip = grip1+grip2;
        var cbleed = 0.3;
        var input = $(".lay-input input");
        var rim = 1;
        var ch,cw,ih,iw,master,mw,ml,selinfo,divname,oh,ow,pdiv;
        var lay,tlay,remain,sinfo,pid;
        var in_to_cm = 2.54;
        var n = paper.length;
        var cover_thick = $("#cover_thick");
        var cut = $("select[name='pdiv[]']");
        var box = $(".lay-box-c, .lay-box-i, .lay-box-cr, .lay-box-ir, .lay-box-o, .lay-box-or");
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
        var grip_input = $("#grip1,#grip2");
        grip_input.on("change",function(){
            grip1 = parseFloat($("#grip1").val());
            grip2 = parseFloat($("#grip2").val());
            grip = grip1+grip2;
            cal_guide();
        });
        cal_guide();
        //custom box
        $(".size-custom").on("click",function(e){
            e.preventDefault();
            my_float_box("custom-size",true);
            var but = $("#edit-custom");
            but.off("click");
            but.on("click",function(){
                if(!valNoBlank('c_height')||!valNoBlank('c_width')){
                    
                } else {
                    my_float_box("custom-size",false);
                    oh = parseFloat($("#c_height").val());
                    ow = parseFloat($("#c_width").val());
                    $(".size-custom span").html(oh+" x "+ow);
                    cal_custom();
                }
            });
        });
        function cal_custom(){
            oh = parseFloat($("#c_height").val());
            ow = parseFloat($("#c_width").val());
            if(oh>0&&ow>0){
                for(var i=0;i<n;i++){
                    master = $.parseJSON(paper[i]['psize']);
                    check_cut(i);
                    check_lay(oh,ow);
                    $(".lay-custom").eq(i).html(tlay);
                    $(".lay-o-rem").eq(i).html(remain);
                    $(".lay-box-o").eq(i).attr("data",sinfo.toString()).attr("cdata",selinfo.toString());

                    check_lay(ow,oh);
                    $(".lay-custom-r").eq(i).html(tlay);
                    $(".lay-o-rem-r").eq(i).html(remain);
                    $(".lay-box-or").eq(i).attr("data",sinfo.toString()).attr("cdata",selinfo.toString());
                }
            }
        }
        function check_cut(i){
            pdiv = cut.eq(i).val();
            if(pdiv==2){
                mw = master.length/pdiv*in_to_cm-grip; //คำนวณเป็นcm - กริ๊ป
                ml = master.width*in_to_cm;
                divname = master.width+"x"+master.length+"(ผ่าครึ่ง)";
            } else {
                mw = master.width*in_to_cm-grip; //คำนวณเป็นcm - กริ๊ป
                ml = master.length*in_to_cm;
                divname = master.width+"x"+master.length;
            }
        }
        function check_lay(h,w){
            lay = Math.floor(mw/h)*Math.floor(ml/w);
            tlay = "("+Math.floor(mw/h)+"x"+Math.floor(ml/w)+") "+lay;
            remain = Math.round((1-lay*(h*w)/(mw*ml))*100)+"%";
            sinfo = [mw,ml,h,w,lay,grip1,grip2];
            selinfo = [divname,pid,pdiv,lay];
        }
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
                    pid = paper[i]['op_id'];
                    check_cut(i);
                    
                    check_lay(ch,cw);
                    $(".lay-cover").eq(i).html(tlay);
                    $(".lay-c-rem").eq(i).html(remain);
                    $(".lay-box-c").eq(i).attr("data",sinfo.toString()).attr("cdata",selinfo.toString());
                    check_lay(ih,iw);
                    $(".lay-inside").eq(i).html(tlay);
                    $(".lay-i-rem").eq(i).html(remain);
                    $(".lay-box-i").eq(i).attr("data",sinfo.toString()).attr("cdata",selinfo.toString());
                    
                    //reverse
                    check_lay(cw,ch);
                    $(".lay-cover-r").eq(i).html(tlay);
                    $(".lay-c-rem-r").eq(i).html(remain);
                    $(".lay-box-cr").eq(i).attr("data",sinfo.toString()).attr("cdata",selinfo.toString());
                    check_lay(iw,ih);
                    $(".lay-inside-r").eq(i).html(tlay);
                    $(".lay-i-rem-r").eq(i).html(remain);
                    $(".lay-box-ir").eq(i).attr("data",sinfo.toString()).attr("cdata",selinfo.toString());
                }
            }
            cal_custom();
        }
        
        box.on("click",function(){
            box.removeClass("box-active");
            $(this).addClass("box-active");
            var da = $(this).attr("data").split(",");
            //show info to select
            var seldata = $(this).attr("cdata").split(",");
            var cclass = $(this).children("span").attr("class");
            if(cclass.search("inside")>0){
                $("#show-lay-cover h4").text("เนื้อใน lay บน "+seldata[0]+" ได้ "+seldata[3]);
                $("#lay-sel").html("<input type='button' value='เลือก' />");
                $("#lay-sel input").on("click",function(){
                    var data = seldata;
                    $("#inside_paper").val(data[1]);
                    $("#inside_div").val(data[2]);
                    $("#inside_lay").val(data[3]);
                });
            } else if(cclass.search("cover")>0) {
                $("#show-lay-cover h4").text("ปก lay บน "+seldata[0]+" ได้ "+seldata[3]);
                $("#lay-sel").html("<input type='button' value='เลือก' />");
                $("#lay-sel input").on("click",function(){
                    var data = seldata;
                    $("#cover_paper").val(data[1]);
                    $("#cover_div").val(data[2]);
                    $("#cover_lay").val(data[3]);
                });
            } else {
                $("#show-lay-cover h4").text("Custom lay บน "+seldata[0]+" ได้ "+seldata[3]);
            }
            show_lay(parseFloat(da[0]),parseFloat(da[1]),parseFloat(da[2]),parseFloat(da[3]),parseFloat(da[4]),parseFloat(da[5]),parseFloat(da[6]));
        });
    });
}
function show_lay(mh,mw,h,w,no,gr,gr2){
    var tg = $("#show-lay");
    var pw = tg.outerWidth();
    var rto = pw/mw;
    var ph,nh,nw,ngr,ngr2;
    ph = (mh+gr)*rto;
    nh = h*rto;
    nw = w*rto;
    ngr = gr*rto;
    ngr2 = gr2*rto;
    //var data = [mh,mw,h,w,no,gr,pw,ph,nh,nw,ngr];
    //console.log(data.toString());
    
    //set paper
    tg.outerWidth(pw).outerHeight(ph);
    tg.html("");
    //grip
    tg.append("<div class='paper-grip'></div>");
    var grip = $(".paper-grip");
    grip.outerWidth(pw).outerHeight(ngr);
    //grip2
    tg.append("<div class='paper-grip2'></div>");
    var grip2 = $(".paper-grip2");
    grip2.outerWidth(pw).outerHeight(ngr2);
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
function reflex_adj(){
$(document).ready(function(){
    $(".tb-adj-cost").trigger("change");
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
    var adjcost = $("#"+id+" .tb-adj-cost");
    var adjkg = $("#"+id+" .tb-adj-paperkg");
    var adjpaper = $("#"+id+" .tb-adj-paper");
    var i,nmg,cost,info;
    //adj paper cost
    adjpaper.on("change",function(){
        i = adjpaper.index($(this));
        info = $.parseJSON($(this).attr("info"));
        $.each(info,function(k,v){
            info[k] = parseFloat(v);
        });
        var radj = $(this).val()*3100/info['width']/info['length']/info['weight'];
        adjkg.eq(i).val(numformat(radj,3));
    });
    //adj paper by kg
    adjkg.on("change",function(){
        i = adjkg.index($(this));
        info = $.parseJSON($(this).attr("info"));
        $.each(info,function(k,v){
            info[k] = parseFloat(v);
        });
        var kgc = $(this).val()/3100*info['width']*info['length']*info['weight'];
        adjpaper.eq(i).val(kgc).trigger("change");
    });
    //adj cost per u
    adjcost.on("change",function(){
        i = adjcost.index($(this));
        var ncost,nvari,frame;
        info = $.parseJSON($(this).attr("info"));
        $.each(info,function(k,v){
            info[k] = parseFloat(v);
        });
        nvari = info['cost']+parseFloat($(this).val());
        ncost = nvari*info['amount'];
        if(info['min']>0){
            ncost = Math.max(info['min'],ncost);
        }
        if(info['frame']>0){ //incase การพิมพ์ ต้องนำมาคูณจำนวนกรอบด้วย
            frame = Math.ceil(info['frame']);
            ncost = frame*ncost;
        }
        ncost = Math.round(ncost);
        var diff = ncost-stfloat(cos.eq(i).text());
        var st = stfloat($("#"+id+" .tb-tt-stcost").text());
        var nst = st+diff;
        $("#"+id+" .tb-tt-stcost").text(numformat(nst,0));
        cos.eq(i).text(numformat(ncost,0));
        mg.eq(i).trigger("change");
    });
    //adj margin total
    mgtt.on("change",function(){
        mg.val($(this).val());
        update_mg();
        tt.html(ntt());
    });
    mg.on("change",function(){
        i = mg.index($(this));
        nmg = 1+$(this).val()/100;
        cost = cos.eq(i).html();
        pr.eq(i).html(numformat(nmg*stfloat(cost),0));
        tt.html(ntt());
    });
    function update_mg(){
        $.each(mg,function(){
           i = mg.index($(this));
           nmg = 1+$(this).val()/100;
           cost = cos.eq(i).html();
           pr.eq(i).html(numformat(nmg*stfloat(cost),0));
        });
    }
    function ntt(){
        var ntt = 0;
        $.each(pr,function(){
            ntt += stfloat($(this).html());
        });
        return numformat(ntt,0);
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
function search_customer(){
$(document).ready(function(){
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
});
}
function quote_function(){
$(document).ready(function(){
    //hide binding
    $("#type").on("change",function(){
       hid_cover();
    });
    function hid_cover(){
        var type = parseInt($("#type").val());
        var bind = $("#bind-sec");
        if(type===10||type===69){
            bind.show();
       } else {
            bind.hide();
            $("#binding").val(0);
       }
    }
    
    //filter paper
    var comp = $("[name='comp_type[]']");
    var size_sel = $("[name='paper_size[]']");
    var label = $(".comp-page label");
    $.each(label,function(i,v){
        var type = comp.eq(i).val();
        change_pagelabel(type,i);
    });
    comp.on("change",function(){
        //check select job size
        var size = $("#sid").val();
        var ctype = $(this).val();
        var index = comp.index($(this));
        change_pagelabel(ctype,index);
        if(size==0){
            pg_dialog("คำเตือน","โปรดเลือกขนาดชิ้นงานก่อน");
            $(this).val("0");
        } else {
            filter_paper(size,ctype,index);
        }
    });
    function change_pagelabel(ctype,index){
        if($.inArray(parseInt(ctype),[3,4,5,7])!=-1){ //ชิ้นงาน ใบพาด แจ็คเก็ด สายคาด
            label.eq(index).html("จำนวน(แผ่น)");
        } else {
            label.eq(index).html("จำนวน(หน้า)");
        }
    }
    //paper size change
    size_sel.on("change",function(){
        var i = size_sel.index($(this));
        var ctype = comp.eq(i).val();
        var size = $(this).val();
        if($.inArray(parseInt(ctype),[1,2,3])!=-1){
            confirm_dialog("คำเตือน","ต้องการเปลี่ยนขนาดกระดาษจาก Master Lay ใช่หรือไม่",sel_ok,sel_cancel);
        } else {
            filter_papern(size,i);
        }
        function sel_ok(){
            filter_papern(size,i);
            $(".qlayinfo").eq(i).css({"display":"block"});
        }
        function sel_cancel(){
            filter_paper($("#sid").val(),ctype,i);
        }
    });
    
    //paper type change
    var sel_type =  $("[name='paper_type[]']");
    sel_type.on("change",function(){
        var index = sel_type.index($(this));
        var size = size_sel.eq(index).val();
        var type = $(this).val();
        if(type!=0){
            filter_gram(".tg_pweight",size,type,index);
        }
    });
    
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
    });
});
}
function filter_papern(size,index){
    var t;
    var data = {};
    var url = $("form").attr("action");
    data['request'] = "filter_papern";
    data['size']  = size;
    data['index'] = index;
    pg_loading(true);
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(res) {
            pg_loading(false);
            //console.log(res);
            //type
            $(".tg_ptype").eq(index).html(res);

            //gram
            var ctype = $("#paper_type_"+index);
            ctype.off("change");
            ctype.on("change",function(){
                t = $(this).val();
                var size = $("#paper_size_"+index).val();
                if(t!=="0"){
                    filter_gram(".tg_pweight",size,t,index);
                }
            });
        },
        error: function(err){
            console.log("ERROR"+JSON.stringify(err));
            pg_loading(false);
        }
    });
}
function filter_paper(sid,type,index){
    var t;
    var data = {};
    data['request'] = "filter_paper";
    data['sid'] = sid;
    data['index'] = index;
    var url = $("form").attr("action");
    pg_loading(true);
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(res) {
            var size_sel = $("[name='paper_size[]']");
            var lay = $("[name='paper_lay[]']");
            var cut = $("[name='paper_cut[]']");
            var psize,play,pcut;
            //console.log(res);
            //show size,lay,cut
            if(type==1){
                psize = res['cover_paper'];
                play = res['cover_lay'];
                pcut = res['cover_div'];
            } else if(type==2||type==3||type==6) {
                psize = res['inside_paper'];
                play = res['inside_lay'];
                pcut = res['inside_div'];
            } else {
                if(res['clay']!=""){
                    $.each(res['clay'],function(k,v){
                        if(v[0]==type){
                            psize = v[1];
                            play = v[3];
                            pcut = v[2];
                        }
                    });
                } else {
                    size_sel.eq(index).val(0);
                    lay.eq(index).val("");
                    cut.eq(index).val(0);
                    pg_loading(false);
                    return false;
                }
            }
            size_sel.eq(index).val(psize);
            lay.eq(index).val(play);
            cut.eq(index).val(pcut);
            //type
            filter_papern(psize,index);

            //gram
            var ctype = $("#paper_type_"+index);
            ctype.off("change");
            ctype.on("change",function(){
                t = $(this).val();
                var size = $("#paper_size_"+index).val();
                if(t!=="0"){
                    filter_gram(".tg_pweight",size,t,index);
                }
            });
            pg_loading(false);
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
    data['index'] = i;
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
function unit_label(){
$(document).ready(function(){
    var sel = $("[name='vunit[]']");
    var label = $(".prod-unit-label");
    sel.on("change",function(){
        var i = sel.index($(this));
        label.eq(i).html($(this).children("option:selected").text());
    });
});
}