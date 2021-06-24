(function ( $ ) {
    $.fn.search = function ( options ) {

        var settings = $.extend({
            json: ''
        }, options );

        var options = {
            includeScore: true,
            useExtendedSearch: true,
            keys: [
                {
                    name: 'fqsen',
                }
            ]
        };

        $.getJSON(settings.json, function(result) {
            list = result;
            fuse = new Fuse(list, options)
        });

        var keyupHandler = function (e) {
            var input = $(e.currentTarget);
            var search = ($(e.currentTarget).val()).trim();
            if(search != '') {
                $.getJSON(settings.json, function(result) {
                    list = result;
                    fuse = new Fuse(list, options)
                    searchResult = fuse.search(search);
                    pathname = $(location).attr('pathname');
                    file = pathname.substring(pathname.lastIndexOf('/'));
                    root = false;
                    if (file == '/index.html' || file == '/') {
                        root = true;
                    }
                    if (searchResult.length > 0) {
                        var panel = '';
                        panel += '<ul>';
                        for (var i = 0; i < searchResult.length; i++) {
                            url = searchResult[i].item.url;
                            if (root) {
                                url = url.replace('../', '');
                            }
                            panel += '<li><a href="' + url + '">'+ searchResult[i].item.name + '<small>'+searchResult[i].item.fqsen +'</small></a></li>'
                        }
                        panel += '</ul>';
                        if( !$('#search-result').length ) {
                            input.after('<div id="search-result"></div>');
                        }
                        $('#search-result').html(panel);
                        $('#search-result li a').overflow();
                        $('#search-result li a').on('click', $.proxy(function(e){
                            if ($(e.currentTarget).attr('data-dragging') == 'false') {
                                input.val('');
                                $('#search-result').remove();
                            }
                        }, input));
                    }
                });
            } else {
                $('#search-result').remove();
            }
        }

        var changeHandler = function (e) {
            var search = ($(e.currentTarget).val()).trim();
            if(search == '') {
                $('#search-result').remove();
            }
        }

        return this.each(function() {
            $(this).keyup(keyupHandler);
            $(this).change(changeHandler);
        });
    }
}( jQuery ));
