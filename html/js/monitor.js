jQuery(document).ready(function() {
    // Common AJAX setup
    jQuery.ajaxSetup({
        url: baseHref + 'service.php',
        data: {
            token: csrfToken
        },
        dataType: "json",
        type: "post",
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

            var source = '';
            var templateData = {};

            if (isJsonString(jqXHR.responseText)) {
                var data = JSON.parse(jqXHR.responseText);

                if (/*phpUnderControl.Error*/data.error) {
                    source = jQuery("#template-error-exception").html();

                    templateData = data.error;
                }
            } else {
                source = jQuery("#template-error-common").html();

                templateData = jQuery.extend({}, jqXHR, {message: message})
            }

            var template = Handlebars.compile(source);

            bootbox.dialog(template(templateData), {
                "label" : "Close",
                "class" : "btn"
            });
        }
    });

    var container = jQuery('#container');

    Handlebars.registerHelper('getStatusClass', function(description) {
        return (String(description).search('passed') == -1) ? 'alert-error' : 'alert-success';
    });

    Handlebars.registerHelper('formatTitle', function(title) {
        title = String(title);

        return title.substring(0, title.search(" "));
    });

    jQuery.getFeed({
        url: 'feed.xml',
        success: function(/*phpUnderControl.Feed*/feed) {
            var wrapClass = 'wrapSuccess';
            var source = jQuery("#template-build").html();
            var template = Handlebars.compile(source);
            var options = {
                class: 'span3',
                perRow: 4
            };

            var builds = [];
            var cntFails = 0;
            var cntSuccess = 0;

            jQuery.each(feed.items, function(index, /*phpUnderControl.Feed.item*/item) {
                if ((String(item.description).search('passed') == -1)) {
                    wrapClass = 'wrapError';

                    cntFails++;
                } else {
                    cntSuccess++;
                }

                builds.push({content: template(jQuery.extend(item, options, {index: index}))});
            });

            source = jQuery("#template-build-row").html();
            template = Handlebars.compile(source);

            jQuery.each(builds.chunk(options.perRow), function(index, item) {
                container.append(template({builds: item}));
            });

            container.find('.content h2 time.timeago').timeago();

            jQuery.ajax({
                data: {
                    service: 'Image',
                    cntFails: cntFails,
                    cntSuccess: cntSuccess
                },
                success: function(/*phpUnderControl.Images*/data) {
                    container.find('.build.alert-error').each(function(index) {
                        var image = data.fails[index] ? data.fails[index] : false;
                        var element = jQuery(this);
                        var imgElement = element.find('img');
                        var hrefElement = element.find('a.colorbox');

                        if (image !== false) {
                            imgElement.attr('src', image);
                            hrefElement.attr('href', image).show();
                        }
                    });

                    container.find('.build.alert-success').each(function(index) {
                        var image = data.success[index] ? data.success[index] : false;
                        var element = jQuery(this);
                        var imgElement = element.find('img');
                        var hrefElement = element.find('a.colorbox');

                        if (image !== false) {
                            imgElement.attr('src', image);
                            hrefElement.attr('href', image).show();
                        }
                    });

                    container.find('.content .colorbox').colorbox({opacity: 0.8, maxHeight: '90%'});
                }
            });

            jQuery('#wrap').removeClass().addClass(wrapClass);
        }
    });

    var interval = 0;

    container.on('mouseover', '.content .colorbox', function() {
        var id = jQuery(this).prop('id');

        interval = setInterval(function() {
            jQuery('#' + id).colorbox({open:true});

            clearInterval(interval);
        }, 750);
    });

    container.on('mouseout', '.content .colorbox', function() {
        clearInterval(interval);
    });

    jQuery('#header').on('click', '#setupLink', function(event) {
        jQuery.ajax({
            data: {
                service: 'Setup'
            }
        });
    })
});

function isJsonString(string) {
    try {
        JSON.parse(string);
    } catch (e) {
        return false;
    }

    return true;
}

Array.prototype.chunk = function ( n ) {
    if ( !this.length ) {
        return [];
    }
    return [ this.slice( 0, n ) ].concat( this.slice(n).chunk(n) );
};