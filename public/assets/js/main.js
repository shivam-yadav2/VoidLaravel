(function($) {
    "use strict";
  
    const $documentOn = $(document);
    const $windowOn = $(window);
  
    $documentOn.ready( function() {
  
      /* ================================
       Mobile Menu Js Start
    ================================ */
    
      $('#mobile-menu').meanmenu({
        meanMenuContainer: '.mobile-menu',
        meanScreenWidth: "1199",
        meanExpand: ['<i class="far fa-plus"></i>'],
    });

       $('#mobile-menus').meanmenu({
        meanMenuContainer: '.mobile-menus',
        meanScreenWidth: "19920",
        meanExpand: ['<i class="far fa-plus"></i>'],
    });

     $documentOn.on("click", ".mean-expand", function () {
        let icon = $(this).find("i");

        if (icon.hasClass("fa-plus")) {
            icon.removeClass("fa-plus").addClass("fa-minus"); 
        } else {
            icon.removeClass("fa-minus").addClass("fa-plus"); 
        }
    });

    /*----------------------------------------------
        # Background Color
        ----------------------------------------------*/
        $("[data-bg-color]").each(function () {
            $(this).css("background-color", $(this).attr("data-bg-color"));
        });

        /*----------------------------------------------
        # Background Image
        ----------------------------------------------*/
        $("[data-background]").each(function () {
            $(this).css("background-image", "url(" + $(this).attr("data-background") + ")");
        });

        /*----------------------------------------------
        # Width
        ----------------------------------------------*/
        $("[data-width]").each(function () {
            $(this).css("width", $(this).attr("data-width"));
        });

        /*----------------------------------------------
        # Text Color
        ----------------------------------------------*/
        $("[data-text-color]").each(function () {
            $(this).css("color", $(this).attr("data-text-color"));
        });
        

    /* ================================
        Sidebar Toggle & Sticky Item Logic
        ================================ */

        // Open offcanvas
        $(".sidebar__toggle").on("click", function () {
        $(".offcanvas__info").addClass("info-open");
        $(".offcanvas__overlay").addClass("overlay-open");

        // Hide sticky item
        $(".sidebar-sticky-item").fadeOut().removeClass("active");
        });

        // Close offcanvas
        $(".offcanvas__close, .offcanvas__overlay").on("click", function () {
        $(".offcanvas__info").removeClass("info-open");
        $(".offcanvas__overlay").removeClass("overlay-open");

        // Show sticky item
        $(".sidebar-sticky-item").fadeIn().addClass("active");
        });

        /* ================================
        Body Overlay Js Start
        ================================ */

        $(".body-overlay").on("click", function () {
        $(".offcanvas__area").removeClass("offcanvas-opened");
        $(".df-search-area").removeClass("opened");
        $(".body-overlay").removeClass("opened");

        // Show sticky item when overlay clicked
        $(".sidebar-sticky-item").fadeIn().addClass("active");
        });

        /* ================================
        Offcanvas Link Click (Optional)
        ================================ */

        $(".offcanvas a").on("click", function () {
        $(".sidebar-sticky-item").fadeIn().addClass("active");
    });

    
      /* ================================
       Sticky Header Js Start
    ================================ */

       $windowOn.on("scroll", function () {
        if ($(this).scrollTop() > 250) {
          $("#header-sticky").addClass("sticky");
        } else {
          $("#header-sticky").removeClass("sticky");
        }
      });      
      
       /* ================================
       Video & Image Popup Js Start
    ================================ */

      $(".img-popup").magnificPopup({
        type: "image",
        gallery: {
          enabled: true,
        },
      });

      $(".video-popup").magnificPopup({
        type: "iframe",
        callbacks: {},
      });
  
      /* ================================
       Counterup Js Start
    ================================ */

      $(".count").counterUp({
        delay: 15,
        time: 4000,
      });
  
      /* ================================
       Wow Animation Js Start
    ================================ */

      new WOW().init();
  
      /* ================================
       Nice Select Js Start
    ================================ */

    if ($('.single-select').length) {
        $('.single-select').niceSelect();
    }

      /* ================================
       Parallaxie Js Start
    ================================ */

      if ($('.parallaxie').length && $(window).width() > 991) {
          if ($(window).width() > 768) {
              $('.parallaxie').parallaxie({
                  speed: 0.55,
                  offset: 0,
              });
          }
      }

      /* ================================
      Hover Active Js Start
    ================================ */

    $(".service-list-items-2").hover(
		// Function to run when the mouse enters the element
		function () {
			// Remove the "active" class from all elements
			$(".service-list-items-2").removeClass("active");
			// Add the "active" class to the currently hovered element
			$(this).addClass("active");
		}
	);


    /* ================================
      Custom Accordion Js Start
    ================================ */

    if ($('.accordion-box').length) {
        $(".accordion-box").on('click', '.acc-btn', function () {
            var outerBox = $(this).closest('.accordion-box');
            var target = $(this).closest('.accordion');
            var accBtn = $(this);
            var accContent = accBtn.next('.acc-content');

            if (target.hasClass('active-block')) {
                // Already open, so close it
                accBtn.removeClass('active');
                target.removeClass('active-block');
                accContent.slideUp(300);
            } else {
                // Close all others
                outerBox.find('.accordion').removeClass('active-block');
                outerBox.find('.acc-btn').removeClass('active');
                outerBox.find('.acc-content').slideUp(300);

                // Open clicked one
                accBtn.addClass('active');
                target.addClass('active-block');
                accContent.slideDown(300);
            }
        });
    }

     /* ================================
      Testimonial Slider Js Start
    ================================ */

    if ($('.testimonial-slider').length > 0) {
    const testimonialSlider2 = new Swiper(".testimonial-slider", {
        spaceBetween: 30,
        speed: 1300,
        loop: true,
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
        },
       pagination: {
            el: ".dot",
            clickable: true,
        },
        breakpoints: {
            1199: {
                slidesPerView: 2,
            },
            991: {
                slidesPerView: 2,
            },
            767: {
                slidesPerView: 2,
            },
            575: {
                slidesPerView: 1,
            },
            0: {
                slidesPerView: 1,
            },
        },
    });
    }

     
    
    //>> Project box Slider Start <<//
      if($('.template-slider').length > 0) {
        const TemplateSlider = new Swiper(".template-slider", {
            slidesPerView: 'auto',
            spaceBetween: 20,
            freemode: true,
            centeredSlides: true,
            loop: true,
            speed: 6000,
            allowTouchMove: false,
            autoplay: {
                delay: 1,
                disableOnInteraction: true,
            },
        });
    }

    //>> Template Slider2 Start <<//
    if($('.template-slider-2').length > 0) {
        const TemplateSlider2 = new Swiper(".template-slider-2", {
            slidesPerView: 'auto',
            spaceBetween: 20,
            freemode: true,
            centeredSlides: true,
            loop: true,
            speed: 6000,
            allowTouchMove: false,
            autoplay: {
                delay: 1,
                disableOnInteraction: true,
            },
        });
    }


      /*----------------------------------------------
        # Image Trail Animation
        ----------------------------------------------*/
        if ($(".image-trail-animation").length && typeof gsap !== "undefined") {

            const MathUtils = {
                lerp: (a, b, n) => (1 - n) * a + n * b,
                distance: (x1, y1, x2, y2) => Math.hypot(x2 - x1, y2 - y1)
            };

            $(".image-trail-animation").each(function () {

                const section = this;
                const imagesWrapper = section.querySelector(".image-trail-images");
                if (!imagesWrapper) return;

                const images = [...imagesWrapper.querySelectorAll("img")];

                let mouse = { x: 0, y: 0 };
                let lastMouse = { x: 0, y: 0 };
                let cachedMouse = { x: 0, y: 0 };

                const threshold = 80;
                let zIndex = 1;
                let imgIndex = 0;

                const getMousePos = (e) => {
                    const rect = section.getBoundingClientRect();
                    return {
                        x: e.clientX - rect.left,
                        y: e.clientY - rect.top
                    };
                };

                section.addEventListener("mousemove", (e) => {
                    mouse = getMousePos(e);
                });

                class ImageTrail {
                    constructor() {
                        requestAnimationFrame(() => this.render());
                    }

                    render() {

                        const distance = MathUtils.distance(
                            mouse.x, mouse.y,
                            lastMouse.x, lastMouse.y
                        );

                        cachedMouse.x = MathUtils.lerp(
                            cachedMouse.x || mouse.x,
                            mouse.x,
                            0.15
                        );

                        cachedMouse.y = MathUtils.lerp(
                            cachedMouse.y || mouse.y,
                            mouse.y,
                            0.15
                        );

                        if (distance > threshold) {
                            this.showImage();
                            lastMouse = { ...mouse };
                        }

                        requestAnimationFrame(() => this.render());
                    }

                    showImage() {

                        const img = images[imgIndex];
                        if (!img) return;

                        gsap.killTweensOf(img);

                        const rect = img.getBoundingClientRect();

                        gsap.set(img, {
                            opacity: 1,
                            scale: 1,
                            zIndex: zIndex++,
                            x: cachedMouse.x - rect.width / 2,
                            y: cachedMouse.y - rect.height / 2
                        });

                        gsap.to(img, {
                            duration: 0.8,
                            ease: "expo.out",
                            x: mouse.x - rect.width / 2,
                            y: mouse.y - rect.height / 2
                        });

                        gsap.to(img, {
                            duration: 0.9,
                            delay: 0.3,
                            opacity: 0,
                            scale: 0.25,
                            ease: "power2.out"
                        });

                        imgIndex = (imgIndex + 1) % images.length;
                    }
                }

                new ImageTrail();

            });
        }

    /* ================================
        Mouse Cursor Animation Js Start
    ================================ */

    // if ($(".mouseCursor").length > 0) {
    //     function itCursor() {
    //         var myCursor = jQuery(".mouseCursor");
    //         if (myCursor.length) {
    //             if ($("body")) {
    //                 const e = document.querySelector(".cursor-inner"),
    //                     t = document.querySelector(".cursor-outer");
    //                 let n, i = 0, o = !1;
    //                 window.onmousemove = function(s) {
    //                     if (!o) {
    //                         t.style.transform = "translate(" + s.clientX + "px, " + s.clientY + "px)";
    //                     }
    //                     e.style.transform = "translate(" + s.clientX + "px, " + s.clientY + "px)";
    //                     n = s.clientY;
    //                     i = s.clientX;
    //                 };
    //                 $("body").on("mouseenter", "button, a, .cursor-pointer", function() {
    //                     e.classList.add("cursor-hover");
    //                     t.classList.add("cursor-hover");
    //                 });
    //                 $("body").on("mouseleave", "button, a, .cursor-pointer", function() {
    //                     if (!($(this).is("a", "button") && $(this).closest(".cursor-pointer").length)) {
    //                         e.classList.remove("cursor-hover");
    //                         t.classList.remove("cursor-hover");
    //                     }
    //                 });
    //                 e.style.visibility = "visible";
    //                 t.style.visibility = "visible";
    //             }
    //         }
    //     }
    //     itCursor();
    // }

    /* ================================
        Back To Top Button Js Start
    ================================ */
    $windowOn.on('scroll', function() {
        var windowScrollTop = $(this).scrollTop();
        var windowHeight = $(window).height();
        var documentHeight = $(document).height();

        if (windowScrollTop + windowHeight >= documentHeight - 10) {
            $("#back-top").addClass("show");
        } else {
            $("#back-top").removeClass("show");
        }
    });

    $documentOn.on('click', '#back-top', function() {
        $('html, body').animate({ scrollTop: 0 }, 800);
        return false;
    });

    /* ================================
       Search Popup Toggle Js Start
    ================================ */

    // if ($(".search-toggler").length) {
    //     $(".search-toggler").on("click", function(e) {
    //         e.preventDefault();
    //         $(".search-popup").toggleClass("active");
    //         $("body").toggleClass("locked");
    //     });
    // }

 
	
    /* ================================
       Smooth Scroller And Title Animation Js Start
    ================================ */
    if ($('#smooth-wrapper').length && $('#smooth-content').length) {
        gsap.registerPlugin(ScrollTrigger, ScrollSmoother, SplitText);

        gsap.config({
            nullTargetWarn: false,
        });

        let smoother = ScrollSmoother.create({
            wrapper: "#smooth-wrapper",
            content: "#smooth-content",
            smooth: 2,
            effects: true,
            smoothTouch: 0.1,
            normalizeScroll: false,
            ignoreMobileResize: true,
        });
    }

        /* ================================
       Text Anim Js Start
    ================================ */

    if (
    typeof SplitText !== "undefined" &&
        document.querySelectorAll(".split-title").length > 0
        ) {
    document.querySelectorAll(".split-title").forEach((title) => {

        // split by words + chars (IMPORTANT)
        const split = new SplitText(title, {
        type: "words,chars"
        });

        // add class to chars
        split.chars.forEach((char) => {
        char.classList.add("char");
        });

        // GSAP animation
        gsap.to(split.chars, {
        scrollTrigger: {
            trigger: title,
            start: "top 90%",
            toggleActions: "play none none none"
        },
        duration: 0.8,
        clipPath: "inset(0% 0% -15% 0%)",
        x: 0,
        opacity: 1,
        ease: "power4.out",
        stagger: 0.03
        });

    });
    }

     if (typeof gsap !== "undefined") {
          gsap.registerPlugin(ScrollTrigger, SplitText);

          let mm = gsap.matchMedia();

          mm.add("(min-width: 1200px)", () => {

              let splits = [];

              // ===== tz-sub-tilte =====
              $('.tz-sub-tilte').each(function (index, el) {

              let split = new SplitText(el, {
                  type: "lines,words,chars",
                  linesClass: "split-line"
              });

              splits.push(split);

              gsap.set(split.chars, {
                  opacity: 0,
                  x: 7
              });

              gsap.to(split.chars, {
                  scrollTrigger: {
                  trigger: el,
                  start: "top 90%",
                  end: "top 60%",
                  scrub: 1
                  },
                  x: 0,
                  opacity: 1,
                  duration: 0.7,
                  stagger: 0.2
              });
              });

              // ===== tz-itm-title =====
              $('.tz-itm-title').each(function (index, el) {

              let split = new SplitText(el, {
                  type: "lines,words,chars",
                  linesClass: "split-line"
              });

              splits.push(split);

              gsap.set(split.chars, {
                  opacity: 0.3,
                  x: -7
              });

              gsap.to(split.chars, {
                  scrollTrigger: {
                  trigger: el,
                  start: "top 92%",
                  end: "top 60%",
                  scrub: 1
                  },
                  x: 0,
                  opacity: 1,
                  duration: 0.7,
                  stagger: 0.2
              });
              });

              // ðŸ”¥ MOST IMPORTANT PART
              ScrollTrigger.refresh();

              // ðŸ”¥ cleanup on breakpoint change
              return () => {
              splits.forEach(split => split.revert());
              ScrollTrigger.getAll().forEach(st => st.kill());
              };

          });
    }

      let splits = []; // 👈 add this line আগে

$('.tz-itm-title').each(function (index, el) {

    let split = new SplitText(el, {
        type: "lines,words,chars",
        linesClass: "split-line"
    });

    splits.push(split);

    gsap.set(split.chars, {
        opacity: 0.3,
        x: -7
    });

    gsap.to(split.chars, {
        scrollTrigger: {
            trigger: el,
            start: "top 92%",
            end: "top 60%",
            scrub: 1
        },
        x: 0,
        opacity: 1,
        duration: 0.7,
        stagger: 0.2
    });
});
    
   if ($(".wa_split_up").length) {

        var wa_split_up = $(".wa_split_up");

        gsap.registerPlugin(SplitText, ScrollTrigger);

        wa_split_up.each(function (index, el) {

            el.split = new SplitText(el, {
                type: "lines,words,chars",
                linesClass: "split-line",
            });

            gsap.set(el, { perspective: 400 });

            let delayValue = $(el).attr("data-split-delay") || "0s";
            delayValue = parseFloat(delayValue) || 0;

            gsap.set(el.split.chars, {
                y: 50,
                opacity: 0,
            });

            el.anim = gsap.to(el.split.chars, {
                scrollTrigger: {
                    trigger: el,
                    start: "top 86%",
                    toggleActions: "play none none reverse",
                },
                y: 0,
                opacity: 1,
                duration: 0.3,
                ease: "power1.out",
                stagger: 0.15,
                delay: delayValue,
            });

        });

    }


   if ($(".wa_title_spilt_1").length) {

        gsap.registerPlugin(SplitText, ScrollTrigger);

        document.querySelectorAll(".wa_title_spilt_1").forEach((atEl) => {

            const atSplit = new SplitText(atEl, {
                type: "words,chars",
                wordsClass: "word",
                charsClass: "char",
            });

            let atDuration = parseFloat(atEl.getAttribute("data-speed")) || 1;
            let atDelay = parseFloat(atEl.getAttribute("data-delay")) || 0;

            if (window.innerWidth <= 768) {
                atDuration = atDuration * 0.3;
            }

            gsap.set(atSplit.words, {
                willChange: "transform",
                perspective: 1000,
                transformStyle: "preserve-3d",
            });

            gsap.set(atSplit.chars, {
                willChange: "transform",
                opacity: 0,
                rotateX: -80,
                transformOrigin: "center center -10px",
            });

            gsap.set(atEl, {
                perspective: 1000,
                transformStyle: "preserve-3d",
            });

            gsap.to(atSplit.chars, {
                scrollTrigger: {
                    trigger: atEl,
                    start: "top 80%",
                },
                opacity: 1,
                rotateX: 0,
                duration: atDuration,
                delay: atDelay,
                ease: "power3.out",
                stagger: {
                    each: 0.05,
                    from: "center",
                    grid: "auto",
                },
            });

        });

    }

   // Gallery scroll
    gsap.registerPlugin(ScrollTrigger);

    if (document.querySelector(".gallery")) {
    const pr = ScrollTrigger.matchMedia();

    pr.add("(min-width: 1199px)", () => {
        
        const galleries = document.querySelectorAll(".gallery");
        const wrapper = document.querySelector(".program-wrapper");

        if (!galleries.length || !wrapper) return;

        galleries.forEach((gallery, index) => {
        const isLast = index === galleries.length - 1;

        
        gsap.to(gallery, {
            scale: isLast ? 1 : 0.85, 
            opacity: isLast ? 1 : 0,
            ease: "none",
            scrollTrigger: {
            trigger: gallery,
            start: "top 14%",
            end: "bottom 80%",
            scrub: true,
            pin: true,
            pinSpacing: false,
            endTrigger: wrapper,
            markers: false,
            },
        });
        });

        // Cleanup on resize condition change
        return () => {
        ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
        };
    });
    }

   $('#mobile-menu a, .theme-btn').on('click', function(e) {
    const target = $(this).attr('href');

    if (target && target.startsWith('#') && $(target).length) {
        e.preventDefault();

        const headerHeight = 100;

        $('html, body').animate({
            scrollTop: $(target).offset().top - headerHeight
        }, 800);
    }
});

  
    });
     // End Document Ready Function

	

    /* ================================
       Preloader Js Start
    ================================ */
    // $windowOn.on('load', function() {
    //     $(".preloader").fadeOut(600);
    // });

  
  })(jQuery); // End jQuery

