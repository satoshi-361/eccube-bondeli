$(document).ready(function () {
    $('.dish_category ul li').click(function(){
        $(".dish_category ul li").removeClass('selected');
        $(this).addClass('selected');
    });

    $('.works-category h1 span').click(function(){
        $('.works-category h1 span').removeClass('border-down');
        $(this).addClass('border-down');
    });

    $('.sp-menu img').click(function(){
      $('.sp-menubar').css('display', 'block');
      $('body').css('overflow-y', 'none')

      $('.sp-menubar .close-btn').click(function(){
          $('.sp-menubar').css('display', 'none');
      });
  });

    var height = $('.pic-wrapper').height();
    $('.search-part').css('margin-top', height);

    var slider_width = $('.slider-container').width();
    $('.slider-wrapper .slider').css('width', slider_width);


    var swiper = new Swiper(".mySwiper1", {
        spaceBetween: 30,
        speed:1000,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        breakpoints: {
          750: {
              slidesPerView: 1,
          },
          760: {
              slidesPerView:3,
          },
          1260: {
              slidesPerView: 3,
          },
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      });

      var swiper = new Swiper(".mySwiper", {
        spaceBetween:15,
        slidesPerView:2,
        speed: 2000,
        loop:true,
        autoplay: {
          delay: 6000,
          disableOnInteraction: false,
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      });

      var swiper = new Swiper(".mySwiper2", {
        speed: 1000,
        parallax: true,
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      });

      var swiper = new Swiper(".mySwiper3", {
        speed: 1000,
        parallax: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
          },
        breakpoints: {
          768: {
              sidesPerView: 1,
          },
          1260: {
              slidesPerView: 2,
          },
          1920: {
              slidesPerView: 2,
          }
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      });

    //   count number 

        var countdownNumberEl = document.getElementById('countdown-number');
            var countdown = 1;

            countdownNumberEl.textContent = countdown;

            setInterval(function() {
            countdown = ++countdown > 4 ? 1 : countdown;

            countdownNumberEl.textContent = countdown;
            }, 6000);

    

});
    function fadeLeft(){
        $('.fadeLeftTrigger').each(function() { //fadeUpTriggerというクラス名が
                var elemPos = $(this).offset().top - 10; //要素より、50px上の
                var scroll = $(window).scrollTop();
                var windowHeight = $(window).height();
                if (scroll >= elemPos - windowHeight) {
                    $(this).css('transform', 'translateX(100%)'); // 画面内に入ったらfadeUpというクラス名を追記
                } else {
                    $(this).css('transform', 'translateX(0%)'); // 画面内に入ったらfadeUpというクラス名を追記
                }
            });
        }
        function fadeIn() {
            $('.fadeInTrigger').each(function() { //fadeUpTriggerというクラス名が
                var elemPos = $(this).offset().top - 10; //要素より、50px上の
                var scroll = $(window).scrollTop();
                var windowHeight = $(window).height();
                if (scroll >= elemPos - windowHeight) {
                    $(this).addClass('fadeIn'); // 画面内に入ったらfadeUpというクラス名を追記
                } else {
                    $(this).removeClass('fadeIn'); // 画面外に出たらfadeUpというクラス名を外す
                }
            });
        }

        $(window).scroll(function(){
           fadeLeft();
           fadeIn();
        });

        $(function() {
            $("#datepicker_start1").datepicker({
                dateFormat: "yy年mm月dd日",
            });
        });

        