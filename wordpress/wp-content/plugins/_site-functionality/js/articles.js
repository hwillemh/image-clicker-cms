/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



(function ($, window, document, undefined) {

    var current_featured;

    var current_article;

    var userHasScrolled = false;

    var video_ani

    var articles

    var current_display_item

    // var populated = false;

    var articles_html = [];

    // var populate_articles_int;

    var article_i = 0;

    $(window).resize(resize);



    $(window).on('beforeunload', function () {

        $(window).scrollTop(0);

    });



    $("body, html").bind("mousewheel", function (e) {


        userHasScrolled = true;

        $("body, html").stop();

    });



    $(document).ready(function () {

        $(this).scrollTop(0);

        $('html, body').scrollTop(0);




        //get_articles();


        init();




    });

    function init() {



        articles = JSON.parse(article_object.articles)

        console.log(articles)

        set_scroll()

        set_slidehow();

        set_audio()

        $('html, body').scrollTop(0);

        //if ($('body').hasClass('single-article')) {

        //    $("body, html").animate({scrollTop: 80}, 3000);

        //} else {
        setTimeout(function () {

            if (!userHasScrolled) {

                var h = $('.article-holder').eq(0).position().top

                h -= $('.hero-holder').height() / 2;

                h += 70;

                $("body, html").animate({scrollTop: h}, 1500);
            }
        }, 2000);
        // }



        $('h1').click(function () {
            $("body, html").stop();
            userHasScrolled = true;
            $("body, html").animate({scrollTop: ($(window).height() / 2) + 200}, 800);
        })

        set_readmore();

        init_social_media_buttons()

    }

    function set_audio() {

        var video = document.getElementById("featured-video")

        var article_audio = document.getElementById("article-audio")

        var podcast_audio = document.getElementById("podcast-audio");


        $('.audio-button, .video-controls .audio, .sound-toggle').click(function () {




            if (!$(this).hasClass('muted')) {

                article_audio.pause();

                $('.audio-button, .video-controls .audio, .sound-toggle').addClass('muted');

                video.muted = true;

            } else {
                
                if ($("#article-audio").attr('src') !=''){
                    article_audio.play();
                }

                

                podcast_audio.pause();
                
                $('.podcast-controls .play-pause').addClass('paused');

                $('.audio-button, .video-controls .audio, .sound-toggle').removeClass('muted');

                video.muted = !video.muted;

            }
        });
/*
        $('.podcast-controls .play-pause').click(function () {

            $(this).toggleClass('paused');
            
            console.log($(this).attr('class'))

            if ($(this).hasClass('paused')) {
                video.muted = true;
                $('.audio-button, .video-controls .audio, .sound-toggle').addClass('muted');
                article_audio.pause();
                podcast_audio.play();
            } else {
                podcast_audio.pause();
            }

        })
        */


    }

    function set_readmore() {

        $('.read-more').click(function (item) {


            if ($('body').hasClass('slideshow')) {
                $('body').removeClass('slideshow');
                $('.article-info-column.column-info').css('transform', 'translateX(0)');

                setTimeout(function () {
                    $('body').addClass('show-article-info');
                    $('.article-info-column.column-info').removeAttr('style');
                }, 400);
            } else {
                $('body').toggleClass('show-article-info');

            }

        });

        $('.close-button').click(function () {
            $('body').removeClass('show-article-info');
        });

        if ($(window).width() > 600) {
            $('.read-more').click();
        }

    }

    function set_slidehow() {

        $('.article-item-feature').click(function (e) {

            if ($(this).hasClass('nsfw') && !$(this).hasClass('unblock')) {
                $('.nsfw').addClass('unblock');
                return;
            }

            if ($(window).width() < 600 || $(window).height() < 600)
                return;






            if (!$('body').hasClass('slideshow')) {


                //$('.article-item-feature video').attr('controls', 'controls');
                $('.article-info-holder').removeClass('show-info');

                $('body').removeClass('show-article-info');

                $('body').addClass('slideshow');

                if ($(current_featured).index() == 0) {

                    var t = $('.article-item:nth-child(2)').offset().top - ($(window).height() / 2) + $('.article-item:nth-child(2)').height()
                    $("body, html").scrollTop(t);
                    $(window).scroll();
                }


                var img_srcset = $(current_display_item).find('img').data('srcset');



                setTimeout(function () {
                    $('.article-item-feature img').attr('srcset', img_srcset)
                }, 700);



            } else {

                $('body').addClass('show-article-info');

                //if (e.target.localName != 'video') {
                $('.article-info-holder').addClass('show-info');

                $('.article-item-feature video').removeAttr('controls');
                $('body').removeClass('slideshow');


                // }


                //var t = $(current_featured).offset().top - ($(window).height() / 2) + $(current_featured).height()
                // $("body, html").scrollTop(t);
                //$(window).scroll();




            }


            //setInterval()
            //resize()
            animate_video_resize();


        });

        $('.close-button').click(function () {
            //$('body').removeClass('slideshow');
            // $('.article-item-feature video').removeAttr('controls');
        });
    }

    function set_scroll() {


        $(window).scroll(function (e) {
            /*
             if (populated == false) {
             populate_articles_int = setInterval(populate_articles, 100);
             populated = true;
             return;
             }
             */



            var st = $(this).scrollTop();



            if (st > 20) {
                $("body").addClass('scrolled');
            } else {
                $("body").removeClass('scrolled');
            }

            if (st > ($(window).height() / 2) - 60) {
                $("body").addClass('scrolled-half');
            } else {
                $("body").removeClass('scrolled-half');
            }


            if (st > $('body').height() - ($(window).height() * 1.2)) {
                $('body').addClass("scroll-end");

            } else {
                $('body').removeClass("scroll-end");

            }


            // Display Item

            display_item(st);


            // Display Article Title

            //display_article(st)



        });
    }

    function display_item(st) {

        var arr = $('.article-item').toArray();



        current_display_item = arr.filter(function (item) {


            var h = ($('body').hasClass('single-article')) ? 0 : $(window).height() / 2;

            if (st > h && $(item).offset().top + $(item).height() - ($(window).height() / 2) > st) {

                return  item;

            }
        })[0];




        if (st < $(window).height() / 2 && $("body").hasClass("slideshow")) {

            current_display_item = arr[0];
        }

        var item_index = $(current_display_item).index();
        var article_index = $(current_display_item).parents(".article-holder").index()


        if (article_index != -1) {


            var slug = (article_index != -1) ? articles[article_index].slug : '';
        }



        // return;


        if (current_article != article_index) {

            current_article = article_index;

            //window.history.pushState({"pageTitle":"hello"},"", articles[current_article].link);


            $('.audio-button').removeClass('show');

            $('.article-podcast').removeClass('show').addClass("button-mode");
            
            
            
            
            $('.audio-button audio, #podcast-audio').attr('src', '');
            
            $('.podcast-controls').removeClass('show');



            if ($("body").hasClass("slideshow")) {
                //return;
            }
            $('.article-info').removeClass('show').stop().removeAttr('style');

            $('.article-info.' + slug).addClass('show').hide().fadeIn(600, function () {
                $(this).removeAttr('style')
            });

            if (current_article > -1 && articles[current_article].article_meta.article_audio[0]) {




                articles[current_article].article_meta.article_audio[0].current_time = $('audio')[0].currentTime;
            }




            if (article_index != -1) {

                if (articles[article_index].article_meta.article_audio && articles[article_index].article_meta.article_audio[0]) {

                    $('.audio-button audio').attr('src', articles[article_index].article_meta.article_audio[0].url)

                    $('.audio-button').addClass('show');



                    var article_audio = document.getElementById("article-audio");
                    
                    

                    article_audio.onloadedmetadata = function () {


                   


                        if (articles[article_index].article_meta.article_audio[0].current_time) {



                            article_audio.currentTime = articles[article_index].article_meta.article_audio[0].current_time;



                        }

                        if (!$('.audio-button').hasClass('muted')) {


                            article_audio.play()

                        }
                    };





                }

                if (articles[article_index].article_meta.article_podcast && articles[article_index].article_meta.article_podcast[0]) {
                    $('.podcast-controls audio').attr('src', articles[article_index].article_meta.article_podcast[0].url)

                    $('.article-podcast').addClass('show');
                }


            }

            current_article = article_index;


        }



        if (current_display_item) {
            $('body').addClass('display-story')
        } else {
            $('body').removeClass('display-story')
            $('.article-info').removeClass('show');

        }

        if (current_featured != current_display_item) {

            if ($("body").hasClass("slideshow") && $(current_display_item).index() == 0) {
                // return;
            }






            current_featured = current_display_item

            $('.article-item-feature video').attr('src', '');

            $('.article-item-feature img').attr('src', '');

            $('.article-item-feature img').attr('srcset', '');


            $('.article-item-feature').css('background-image', '');

            $('.article-item-feature').removeClass('video-portrait')

            $('.article-item-feature').attr("data-format", $(current_display_item).data(''));


            $('.article-item-feature').attr('data-type', '');




            if ($(current_display_item).hasClass('article-type-video')) {

                $('.audio-button').addClass('show');



                $('.article-item-feature').attr('data-type', 'video');


                var video = $(current_display_item).find('video').data('src');



                $('.article-item-feature video').attr('src', video);

                var img_src = $(current_display_item).find('img').attr('src');

                $('.article-item-feature video').attr('poster', img_src);

                var display = $(current_display_item).data('video-display');

                if (display == 1) {
                    $('.article-item-feature').addClass('video-portrait')
                }


                $('.article-item-feature').attr("data-format", $(current_display_item).data('video-display'));



            } else if ($(current_display_item).hasClass('article-type-image')) {

                $('.article-item-feature').attr('data-type', 'image');



                var img_src = $(current_display_item).find('img').attr('src');
                var img_srcset = $(current_display_item).find('img').data('srcset');

                var w = $(current_display_item).find('img').attr('width');
                var h = $(current_display_item).find('img').attr('height');



                if ($('body').hasClass('slideshow')) {
                    $('.article-item-feature img').attr('src', img_src).attr('srcset', img_srcset).attr('width', w).attr('height', h);
                } else {

                    // 

                    $('.article-item-feature img').attr('src', img_src).attr('width', w).attr('height', h);
                }




            } else if ($(current_display_item).hasClass('article-type-chapter')) {

                $('.article-item-feature').attr('data-type', 'chapter');
                $('.article-item-feature').attr('data-type', 'chapter');
                var txt = $(current_display_item).find('.article-text').text();
                $('.article-item-feature .text-holder').text(txt)

            }

            if ($(current_display_item).hasClass('nsfw')) {
                $('.article-item-feature').addClass('nsfw');
            } else {
                $('.article-item-feature').removeClass('nsfw');
            }


            $('.item-credits li, .item-titles li').removeClass('show');

            $('.item-credits li.caption-' + item_index).addClass('show');

            $('.item-titles li.title-' + item_index).addClass('show');

            /*
             if (articles[article_index]['article_meta']['article_item'][item_index]['item_caption']) {
             $('.item-credit').html(articles[article_index]['article_meta']['article_item'][item_index]['item_caption']);
             } else {
             $('.item-credit').html('');
             }
             */

            resize()

        }
    }







    function animate_video_resize() {
        //return;


        if ($("body").hasClass("slideshow")) {
            if (video_ani)
                clearInterval(video_ani);

            var w = $(window).width() - 600;
            var h = $(window).height() * .875

            if (h > w) {
                h = w
            } else {
                w = h;
            }


        } else {
            w = h = 250;
        }



        $('.article-item-feature .video-holder ').animate({width: w, height: h}, 400);



        video_ani = setInterval(resize, 50)

        setTimeout(function () {

            clearInterval(video_ani)
        }, 2500)
    }

    function init_social_media_buttons() {




        $('.ss-btn').click(function (e) {

            if (current_article != -1) {

                var page_url = articles[current_article]['link'];

            } else {
                page_url = window.location.href;
            }

            var onclick = "window.open('https://www.linkedin.com/shareArticle?mini=true&amp;url=" + page_url + "&amp;src=sdkpreparse', 'popup', 'width=400,height=600'); return false;";


            e.preventDefault();

            if ($(this).hasClass('linked-in')) {
                window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + page_url + '&src=sdkpreparse', 'popup', 'width=400,height=600');
            } else if ($(this).hasClass('facebook')) {

                window.open('https://www.facebook.com/sharer/sharer.php?u=' + page_url + '&src=sdkpreparse', 'popup', 'width=400,height=600');

            } else if ($(this).hasClass('twitter')) {
                window.open('http://twitter.com/share?url=' + page_url, 'popup', 'width=400,height=600');

            } else if ($(this).hasClass('email')) {


                // alert();
                if (current_article != -1) {

                    var subject = "Tings Magazine - " + articles[current_article].title.rendered;

                    var credits = articles[current_article]['article_meta']['article-credits'].replace(/<\/p>/g, '%0A');

                    var credits = credits.replace(/<p>/g, '');

                    var credits = credits.replace(/<br \/>/g, '%0A');



                    var body = "Check out in Tings Magazine: ";
                    body += "%0A%0A" + articles[current_article].title.rendered;

                    body += "%0A%0A" + credits;

                    body += "%0A%0A" + articles[current_article].link;


                } else {
                    var subject = "Tings Magazine";
                    var body = "Check out Tings Magazine:";

                    body += "%0A%0A" + window.location.href;
                }

                window.location.href = "mailto:?subject=" + subject + '&body=' + body;

            } else if ($(this).hasClass('sms')) {

                const str = articles[current_article].link

                function copyToClipboard(str) {
                    const el = document.createElement('textarea');
                    el.value = str;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    // document.body.removeChild(el);
                }
                ;

                const url = window.location.href;

                copyToClipboard(str);

                window.history.pushState({"pageTitle": "Tings"}, "", articles[current_article].link);

                alert("Copy the url to share:\r\n" + str);




                window.history.pushState({"pageTitle": "Tings"}, "", url);
            }

        });




    }

    function resize() {

        return;



        $('.article-item-feature .video-holder').removeAttr('style');

        if ($("body").hasClass("slideshow")) {
            // if (video_ani)
            //   clearInterval(video_ani);

            var w = $(window).width() - 600;
            var h = $('.article-item-feature').height();



            if (h > w) {
                h = w
            } else {
                w = h;
            }
            // h = $('figure.image-wrapper img').attr('height')

            var imw = $('figure.image-wrapper img').attr('width');
            var imh = $('figure.image-wrapper img').attr('height');



            var image_width = h * (imw / imh);




        } else {
            w = h = "100%";
            image_width = "100%"
        }

        $('.article-item-feature .video-holder ').css({width: w, height: h});




        $('figure.image-wrapper').width(image_width);
    }


})(jQuery, window, window.document, undefined);
