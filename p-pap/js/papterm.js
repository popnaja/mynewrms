function mod_slug(){
    $(document).ready(function(){
        var input = $("#slug");
        input.on("change",function(){
            var slug = $(this).val().toUpperCase();
            slug = slug.replace(/\W/g,"");
            slug = slug.substring(0,5);
            $(this).val(slug);
        });
    });
}


