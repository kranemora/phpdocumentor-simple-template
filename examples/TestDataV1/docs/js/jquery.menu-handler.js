(function ( $ ) {
    $.fn.menuHandler = function(options) {
        var settings = $.extend({
            target: 'ul',
        }, options );

        var Collection = [];

        var Menu = function(selector, target) {
            this.open = false;

            this.selector = selector;
            this.target = target;

            this.selector.addClass('close');
            this.selector.removeClass('open');

            selector.on('click', $.proxy(function(e){
                if (this.target.css('display') == 'none') {
                    this.open = true
                    this.target.css({'display':'block'});
                    this.selector.addClass('open');
                    this.selector.removeClass('close');
                } else {
                    this.open = false;
                    this.target.css({'display':'none'});
                    this.selector.addClass('close');
                    this.selector.removeClass('open');
                }
            }, this));

            Collection.push(this);
        };

        $( window ).resize(function() {
            $(Collection).each(function(){
                if (this.selector.css('display') == 'none') {
                    this.target.css({'display': 'block'});
                } else {
                    if (this.open) {
                        this.target.css({'display': 'block'});
                    } else {
                        this.target.css({'display': 'none'});
                    }
                }
            })
        });

        return this.each(function(k, v) {
            new Menu($(this), $(settings.target, $(this).parent()));
        })
        return this;
    }
}( jQuery ));
