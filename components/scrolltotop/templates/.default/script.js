var scrollToTopInit = function() {
    var $button = $('#scrolltotop');

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 200) {
            $button.addClass('show');
        } else {
            $button.removeClass('show');
        }
    });

    $button.on('click', function() {
        $('html').stop().animate({scrollTop: position}, speed);
    });
};

$(document).ready(function($) {
    scrollToTopInit();
});