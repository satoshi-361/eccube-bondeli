function banner_change(){
    var height = $(".pic-wrapper").height();
    var scroll = $(window).scrollTop();
    if(scroll > height ){
    $(".header").css("box-shadow","0 3px 10px rgba(0,0,0,0.3)");
    $(".header").css("height","80px");
    $(".header").css("background","white");
    $(".header").css("color","#212223");
    $(".header a").css("color","#53160A");
    $(".header .menu a").css("background-image","linear-gradient(to right, black, black)");
    $(".header .logo .scrollup").css("display","none");
    $(".header .logo .scrolldown").css("display","block");

    }
    else{
    $(".header").css("box-shadow","none");
    $(".header").css("height","120px");
    $(".header").css("background","transparent");
    $(".header").css("color","white");
    $(".header a").css("color","white");
    $(".header .menu a").css("background-image","linear-gradient(to right, white, white)");
    $(".header .menu li.down li.my-home span").css("background-image","linear-gradient(to right, white, white)");
    $(".header .logo .scrollup").css("display","block");
    $(".header .logo .scrolldown").css("display","none");
    }
}

function sp_banner_change(){
    var height = $(".pic-wrapper").height();
    var scroll = $(window).scrollTop();
    if(scroll > height){
        $(".sp-header").css("background","white");
        $(".sp-header .scrollup").css("display", "none");
        $(".sp-header .scrolldown").css("display", "block");
    }else{
        $(".sp-header").css("background","transparent");
        $(".sp-header .scrollup").css("display", "block");
        $(".sp-header .scrolldown").css("display", "none");
    }


}


$(window).scroll(function(){

    banner_change();
    sp_banner_change();
 });

 