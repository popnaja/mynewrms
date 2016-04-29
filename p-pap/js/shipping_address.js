function search_customer(){
    $(document).ready(function(){
        var url = $("#ajax_req").val();
        var id = "cus";
        var re = $("#redirect").val();
        auto_complete_input(id,findex,show);
        function findex(find,f1,f2){
            var data = {};
            data['request'] = "find_customer";
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
                $("#"+id).val(ele.html());
                window.location.replace(re+"?cid="+ele.attr("cid"));
            }
        }
    });
}

