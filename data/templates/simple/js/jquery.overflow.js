(function ( $ ) {
    $.fn.overflow = function ( options ) {

        var settings = $.extend({
        }, options );

        var offset = 0;
        var pageX = 0;
        var velocity = 0;
        var idInterval;
        var delta;

        var clickHandler = function(e) {
            if ($(e.currentTarget).attr('data-dragging') == 'true') {
                e.preventDefault();
            }
            $(e.currentTarget).attr('data-dragging', false);
        }

        var mouseDownHandler = function(e)
        {
            e.preventDefault();
            $(e.currentTarget).attr('data-dragging', false);
            inner = $(e.currentTarget).innerWidth();
            canvas = $(e.currentTarget).parent()[0].scrollWidth;
            offset = inner - canvas - parseInt($(e.currentTarget).css('padding-right'));
            enable = inner - (canvas - parseInt($($(e.currentTarget).parent()[0]).css('padding-right')) - parseInt($($(e.currentTarget).parent()[0]).css('padding-left'))) < 0;
            console.log(enable)
            if (offset < 0) {
                pageX = e.pageX;
            }
            idInterval = setInterval(function() {
                if (velocity != 0 && enable) {
                    delta = parseInt($(e.currentTarget).css('left'))+velocity;
                    if (delta > 0) {
                        delta = 0;
                    } else if (delta<offset) {
                        delta = offset;
                    }
                    $(e.currentTarget).css({left: delta+'px'});
                }
            }, 33);
        }

        var mouseMoveHandler = function(e)
        {
            e.preventDefault();
            $(e.currentTarget).attr('data-dragging', true);
            if ($(e.currentTarget).attr('data-dragging') == 'true') {
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
            $(e.currentTarget).css({left: 0});
        }

        var touchStartHandler = function(e)
        {
            $(e.currentTarget).attr('data-dragging', false);
            canvas = $(e.currentTarget).innerWidth();
            inner = $(e.currentTarget).parent()[0].scrollWidth;
            offset = canvas - inner - parseInt($(e.currentTarget).css('padding-right'));
            if (offset < 0) {
                pageX = e.originalEvent.touches[0].pageX;
            }
            idInterval = setInterval(function() {
                if (velocity != 0) {
                    delta = parseInt($(e.currentTarget).css('left'))+velocity;
                    if (delta > 0) {
                        delta = 0;
                    } else if (delta<offset) {
                        delta = offset;
                    }
                    $(e.currentTarget).css({left: delta+'px'});
                }
            }, 33);
        }

        var touchMoveHandler = function(e)
        {
            $(e.currentTarget).attr('data-dragging', true);
            if ($(e.currentTarget).attr('data-dragging') == 'true') {
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
            $(e.currentTarget).css({left: 0});
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
            $(this).on('touchend', touchEndHandler);
        });
    }
}( jQuery ));
