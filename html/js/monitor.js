jQuery(document).ready(function() {
    // Common AJAX setup
    jQuery.ajaxSetup({
        data: {
        },
        error: function(jqXHR, exception) {
            var message = '';

            if (jqXHR.status === 0) {
                message = 'Not connect. Verify Network.';
            } else if (jqXHR.status == 404) {
                message = 'Requested page not found [404].';
            } else if (jqXHR.status == 500) {
                message = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
                message = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
                message = 'Time out error.';
            } else if (exception === 'abort') {
                message = 'Ajax request aborted.';
            } else {
                message = 'Uncaught Error.\n' + jqXHR.responseText;
            }

            makeMessage(message, 'error', {timeout: 5000});
        },
        dataType: "json",
        type: "post"
    });

    Handlebars.registerHelper('getStatusClass', function(description) {
        return (String(description).search('passed') == -1) ? 'alert-error' : 'alert-success';
    });

    Handlebars.registerHelper('formatTitle', function(title) {
        title = String(title);

        return title.substring(0, title.search(" "));
    });

    Handlebars.registerHelper('getStatusImage', function(description, index) {
        var failed = (String(description).search('passed') == -1);

        jQuery.ajax({
            url: 'service.php',
            data: {
                type: 'image',
                failed: failed
            },
            success: function(data) {
                // TODO: implement image replace
            }
        });

        if (!failed) {
            return "images/success/chuck-norris-approved.png";
        } else {
            return index;
        }
    });

    jQuery.getFeed({
        url: 'feed.xml',
        success: function(feed) {
            var wrapClass = 'wrapSuccess';
            var content = jQuery('#container');
            var source = jQuery("#template-build").html();
            var template = Handlebars.compile(source);
            var options = {
                class: 'span3',
                perRow: 4
            };

            var builds = [];

            jQuery.each(feed.items, function(index, item) {
                if ((String(item.description).search('passed') == -1)) {
                    wrapClass = 'wrapError';
                }

                var build = template(jQuery.extend(item, options, {index: index}));

                builds.push({content: build});
            });

            source = jQuery("#template-build-row").html();
            template = Handlebars.compile(source);

            jQuery.each(builds.chunk(options.perRow), function(index, item) {
                content.append(template({builds: item}));
            });

            content.find('.content h2 time.timeago').timeago();

            jQuery('#wrap').removeClass().addClass(wrapClass);
        },
        error: function() {
            alert('error');
        }
    });
});

function makeMessage(text, type, options) {
    // TODO: implement this
}

Array.prototype.chunk = function ( n ) {
    if ( !this.length ) {
        return [];
    }
    return [ this.slice( 0, n ) ].concat( this.slice(n).chunk(n) );
};