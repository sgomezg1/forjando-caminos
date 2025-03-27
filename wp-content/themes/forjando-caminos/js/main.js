$(document).ready(function() {
    $(".open-close-faq").click(function(){
        const dataFaq = $(this).attr("data-faq");
        const tituloFaq = $(`h5[data-faq="${dataFaq}"]`);
        const textoFaq = $(`p[data-faq="${dataFaq}"]`);
        const contenedorFaq = $(`div[data-faq="${dataFaq}"]`);
        if (textoFaq.hasClass("alto-automatico")) {
            $(this).removeClass("color-aguamarina");
            tituloFaq.removeClass("color-aguamarina");
            textoFaq.removeClass(["alto-automatico", "borde-superior-texto-faq"]);
            contenedorFaq.removeClass(["color-blanco", "fondo-azul-faq"]);
            $(this).text("-");
            return;
        }
        $(this).addClass("color-aguamarina");
        tituloFaq.addClass("color-aguamarina");
        textoFaq.addClass(["alto-automatico", "borde-superior-texto-faq"]);
        contenedorFaq.addClass(["color-blanco", "fondo-azul-faq"]);
        $(this).text("+");
    });

    if (window.innerWidth <= 1025) {
        $('.contenedor-labor-flex').slick({
            lazyLoad: 'ondemand',
            infinite: false,
            dots: true,
            arrows: false,
            customPaging: function(slider, i) {
                return '<span class = "slider-custom"></span>';
            },
            autoplay: true,
            autoplaySpeed: 2000,
            responsive: [
                {
                    breakpoint: 11920,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                }, {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }, {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
        $('.contenedor-nuestro-legado').slick({
            lazyLoad: 'ondemand',
            infinite: false,
            dots: false,
            arrows: false,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    }
});