(function ( $ ) {
    $.fn.overflow = function ( options ) {

        var settings = $.extend({
        }, options );

        var offset = 0;
        var pageX = 0;
        var velocity = 0;
        var idInterval;
        var delta;
        var current;

        var clickHandler = function(e) {
            if ($(current).attr('data-dragging') == 'true') {
                e.preventDefault();
            }
            $(current).attr('data-dragging', false);
        }

        var mouseDownHandler = function(e)
        {
            e.preventDefault();
            current = e.currentTarget;
            $(current).attr('data-dragging', false);
            inner = $(current).innerWidth();
            canvas = $(current).parent()[0].scrollWidth;
            offset = inner - canvas - parseInt($(current).css('padding-right'));
            enable = inner - (canvas - parseInt($($(current).parent()[0]).css('padding-right')) - parseInt($($(current).parent()[0]).css('padding-left'))) < 0;
            if (offset < 0) {
                pageX = e.pageX;
            }
            idInterval = setInterval(function() {
                if (velocity != 0 && enable) {
                    delta = parseInt($(current).css('left'))+velocity;
                    if (delta > 0) {
                        delta = 0;
                    } else if (delta<offset) {
                        delta = offset;
                    }
                    $(current).css({left: delta+'px'});
                }
            }, 33);
        }

        var mouseMoveHandler = function(e)
        {
            e.preventDefault();
            $(current).attr('data-dragging', true);
            if ($(current).attr('data-dragging') == 'true') {
                velocity = e.pageX - pageX;
            }
            if (delta == 0 || delta == offset) {
                pageX = e.pageX;
            }
        }

        var mouseUpHandler = function(e)
        {
            e.preventDefault();
            clearInterval(idInterval);
            velocity = 0;
            $(current).css({left: 0});
        }

        var touchStartHandler = function(e)
        {
            current = e.currentTarget;
            $(current).attr('data-dragging', false);
            inner = $(current).innerWidth();
            canvas = $(current).parent()[0].scrollWidth;
            offset = inner - canvas - parseInt($(current).css('padding-right'));
            enable = inner - (canvas - parseInt($($(current).parent()[0]).css('padding-right')) - parseInt($($(current).parent()[0]).css('padding-left'))) < 0;
            if (offset < 0) {
                pageX = e.originalEvent.touches[0].pageX;
            }
            idInterval = setInterval(function() {
                if (velocity != 0 && enable) {
                    delta = parseInt($(current).css('left'))+velocity;
                    if (delta > 0) {
                        delta = 0;
                    } else if (delta<offset) {
                        delta = offset;
                    }
                    $(current).css({left: delta+'px'});
                }
            }, 33);
        }

        var touchMoveHandler = function(e)
        {
            $(current).attr('data-dragging', true);
            if ($(current).attr('data-dragging') == 'true') {
                velocity = e.originalEvent.touches[0].pageX - pageX;
            }
            if (delta == 0 || delta == offset) {
                pageX = e.originalEvent.touches[0].pageX;
            }
        }

        var touchEndHandler = function(e)
        {
            clearInterval(idInterval);
            velocity = 0;
            $(current).css({left: 0});
        }

        return this.each(function() {
            $(this).parent().css({overflow:'hidden'});
            $(this).css({position:'relative', display:'block'});
            $(this).attr('data-dragging', false);
            $(this).on('click', clickHandler);
            $(this).on('mousedown', mouseDownHandler);
            $(this).on('touchstart', touchStartHandler);
            $(this).on('mousemove', mouseMoveHandler);
            $(this).on('touchmove', touchMoveHandler);
            $(this).on('mouseup dragend', mouseUpHandler);
            $(document).on('mouseup dragend', mouseUpHandler);
            $(this).on('touchend', touchEndHandler);
            $(document).on('touchend', mouseUpHandler);
        });
    }
}( jQuery ));
