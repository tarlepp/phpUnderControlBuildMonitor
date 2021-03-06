jQuery(document).ready(function () {
    var params = {};

    if (location.search) {
        var parts = location.search.substring(1).split('&');

        for (var i = 0; i < parts.length; i++) {
            var nv = parts[i].split('=');
            if (!nv[0]) continue;
            params[nv[0]] = nv[1] || true;
        }
    }

    // Common AJAX setup
    jQuery.ajaxSetup({
        url: baseHref + 'service.php',
        data: jQuery.extend({
            token: csrfToken
        }, params),
        dataType: "json",
        type: "post",
        error: function(jqXHR, exception) {
            handleError(jqXHR, exception);
        }
    });

    var header = jQuery('#header');
    var container = jQuery('#container');
    var timeOuts = [];

    Handlebars.registerHelper('getStatusClass', function (description) {
        return (String(description).search('passed') == -1) ? 'alert-error' : 'alert-success';
    });

    Handlebars.registerHelper('formatTitle', function (title) {
        title = String(title);

        return title.substring(0, title.search(" "));
    });

    jQuery.ajax({
        data: {
            service: 'Setting'
        },
        success: function (/*phpUnderControl.Settings*/settings) {
            makeProjects(settings);
        }
    });

    container.on('mouseover', '.content .colorbox', function () {
        var id = jQuery(this).prop('id');

        timeOuts['image'] = setInterval(function () {
            jQuery('#' + id).colorbox({open: true});

            clearInterval(timeOuts['image']);
        }, 750);
    });

    container.on('mouseout', '.content .colorbox', function () {
        clearInterval(timeOuts['image']);
    });

    container.on('click', '.build a.remove', function() {
        jQuery.ajax({
            data: {
                service: 'Setting',
                action: 'RemoveProject',
                project: jQuery(this).data('project')
            },
            success: function (/*phpUnderControl.Settings*/settings) {
                makeProjects(settings);
            }
        });
    });

    header.on('click', '#refreshLink', function(event) {
        event.preventDefault();

        jQuery.ajax({
            data: {
                service: 'Setting'
            },
            error: function(jqXHR, exception) {
                location.reload();
            },
            success: function (/*phpUnderControl.Settings*/settings) {
                makeProjects(settings);
            }
        });
    });

    header.on('click', '#settingsLink', function(event) {
        event.preventDefault();

        var source = jQuery("#template-setup").html();
        var template = Handlebars.compile(source);

        jQuery.ajax({
            data: {
                service: 'Setting',
                action: 'SettingsDialog'
            },
            success: function (/*phpUnderControl.Settings*/settings) {
                var buttons = [
                    {
                        label: 'Close',
                        class: 'btn pull-left'
                    },
                    {
                        label: 'Save settings',
                        class: 'btn-primary',
                        callback: function () {
                            var form = dialog.find('form');

                            jQuery.ajax({
                                data: {
                                    service: 'Setting',
                                    action: 'SaveSettings',
                                    data: form.serializeJSON()
                                },
                                beforeSend: function () {
                                    form.find('.control-group').removeClass('error');

                                    form.find('.popover-container').each(function () {
                                        var element = jQuery(this).parent().find('.popover');

                                        if (element.length > 0) {
                                            element.remove();
                                        }
                                    });
                                },
                                success: function (/*phpUnderControl.Settings*/settings) {
                                    makeProjects(settings);

                                    dialog.modal('hide');
                                },
                                error: function (jqXHR) {
                                    var data = JSON.parse(jqXHR.responseText);
                                    var message = data.message;

                                    var element = form.find('#' + data.element);
                                    var popoverElement = element.parent().find('.popover-container');

                                    var options = {
                                        content: '',
                                        trigger: 'manual',
                                        html: true
                                    };

                                    popoverElement.popover(options);
                                    popoverElement.data('popover').options.content = message;
                                    popoverElement.popover('show');

                                    if (data.element != 'generic') {
                                        var row = element.parent();

                                        if (row.hasClass('input-append')) {
                                            row = row.parent().parent();
                                        } else {
                                            row = row.parent();
                                        }

                                        row.addClass('error');
                                    }

                                    dialog.find('.modal-body').scrollTop(0);
                                }
                            });

                            return false;
                        }
                    }
                ];

                var dialog = bootbox.dialog(template(settings), buttons, {header: 'Build monitor settings'});

                dialog.find('.controls-slider').each(function () {
                    var controls = jQuery(this);
                    var slider = controls.find('.slider');
                    var input = controls.find('input');
                    var span = controls.find('span.uneditable-input');

                    var valueCurrent = parseInt(input.val(), 10);
                    var valueMin = parseInt(slider.data('min'), 10);
                    var valueMax = parseInt(slider.data('max'), 10);

                    slider.slider({
                        range: 'min',
                        min: valueMin,
                        max: valueMax,
                        value: isNaN(valueCurrent) ? 1 : valueCurrent,
                        slide: function (event, ui) {
                            var value = parseInt(ui.value, 10);

                            input.val(value);
                            span.html(value);
                        }
                    });
                });

                dialog.on('click', '#fetchProjects', function () {
                    var form = dialog.find('form');
                    var button = jQuery(this);
                    var originalText = button.html();

                    jQuery.ajax({
                        data: {
                            service: 'Setting',
                            action: 'GetFeedProjects',
                            url: form.find('#feedUrl').val()
                        },
                        beforeSend: function () {
                            button.html('<em>' + originalText + '...</em>');
                            button.addClass('disabled');

                            form.find('.control-group').removeClass('error');
                            form.find('.popover-container').each(function () {
                                var element = jQuery(this).parent().find('.popover');

                                if (element.length > 0) {
                                    element.remove();
                                }
                            });
                        },
                        success: function (data) {
                            button.html(originalText);
                            button.removeClass('disabled');

                            var source = jQuery("#template-setup-projects").html();
                            var template = Handlebars.compile(source);

                            form.find('#projects').html(template(data));
                        },
                        error: function (jqXHR) {
                            button.html(originalText);
                            button.removeClass('disabled');

                            var data = JSON.parse(jqXHR.responseText);
                            var message = data.message;

                            var element = form.find('#feedUrl');
                            var popoverElement = element.parent().find('.popover-container');

                            var options = {
                                content: '',
                                trigger: 'manual',
                                html: true
                            };

                            popoverElement.popover(options);
                            popoverElement.data('popover').options.content = message;
                            popoverElement.popover('show');

                            element.parent().parent().parent().addClass('error');
                            dialog.find('.modal-body').scrollTop(0);
                        }
                    });
                })
            }
        });
    });

    function makeProjects(/*phpUnderControl.Settings*/settings) {
        clearInterval(timeOuts['projects']);

        jQuery.getFeed({
            url: baseHref + 'service.php',
            data: {
                service: 'Setting',
                action: 'GetFeed',
                feed: settings.feedUrl
            },
            error: function (jqXHR, exception) {
                handleError(jqXHR, exception);
            },
            success: function (/*phpUnderControl.Feed*/feed) {
                var wrapClass = 'wrapSuccess';
                var source = jQuery("#template-build").html();
                var template = Handlebars.compile(source);
                var options = {
                    class: settings.buildClass,
                    perRow: settings.buildsPerRow
                };

                var builds = [];
                var cntFails = 0;
                var cntSuccess = 0;

                jQuery.each(feed.items, function (index, /*phpUnderControl.Feed.item*/item) {
                    if (jQuery.inArray(item.title.substring(0, item.title.search(" ")), settings.projectsToShow) == -1) {
                        return;
                    }

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

                var content = '';

                jQuery.each(builds.chunk(options.perRow), function (index, item) {
                    content += template({builds: item});
                });

                content = jQuery('<div/>').html(content).contents();

                jQuery.ajax({
                    data: {
                        service: 'Image',
                        cntFails: cntFails,
                        cntSuccess: cntSuccess
                    },
                    success: function (/*phpUnderControl.Images*/data) {
                        content.find('.build.alert-error').each(function (index) {
                            var image = data.fails[index] ? data.fails[index] : false;
                            var element = jQuery(this);
                            var imgElement = element.find('img');
                            var hrefElement = element.find('a.colorbox');

                            if (image !== false) {
                                imgElement.attr('src', image);
                                hrefElement.attr('href', image).show();
                            }
                        });

                        content.find('.build.alert-success').each(function (index) {
                            var image = data.success[index] ? data.success[index] : false;
                            var element = jQuery(this);
                            var imgElement = element.find('img');
                            var hrefElement = element.find('a.colorbox');

                            if (image !== false) {
                                imgElement.attr('src', image);
                                hrefElement.attr('href', image).show();
                            }
                        });

                        container.html(content);
                        container.find('.content h2 time.timeago').timeago();
                        container.find('.content .colorbox').colorbox({opacity: 0.8, maxHeight: '90%'});

                        updateTime();
                    }
                });

                jQuery('#wrap').removeClass().addClass(wrapClass).addClass(settings.buildClass.replace("span", "wrap"));

                // Set interval
                timeOuts['projects'] = setInterval(function () {
                    makeProjects(settings);

                    clearInterval(timeOuts['projects']);
                }, settings.refreshInterval * 60 * 1000);
            }
        });
    }

    jQuery.fn.serializeJSON = function () {
        var json = {};
        jQuery.map(jQuery(this).serializeArray(), function (n, i) {
            var _ = n.name.indexOf('[');
            if (_ > -1) {
                var o = json;
                _name = n.name.replace(/\]/gi, '').split('[');
                for (var i = 0, len = _name.length; i < len; i++) {
                    if (i == len - 1) {
                        if (o[_name[i]]) {
                            if (typeof o[_name[i]] == 'string') {
                                o[_name[i]] = [o[_name[i]]];
                            }
                            o[_name[i]].push(n.value);
                        }
                        else o[_name[i]] = n.value || '';
                    }
                    else o = o[_name[i]] = o[_name[i]] || {};
                }
            }
            else {
                if (json[n.name] !== undefined) {
                    if (!json[n.name].push) {
                        json[n.name] = [json[n.name]];
                    }
                    json[n.name].push(n.value || '');
                }
                else json[n.name] = n.value || '';
            }
        });

        return json;
    };
});

function updateTime() {
    var date = new Date();
    var element = jQuery('<time datetime="' + date.toISOString() + '">' + date.toISOString() + '</time>');

    element.timeago();

    jQuery('#lastUpdate').html(element);
}

function isJsonString(string) {
    try {
        JSON.parse(string);
    } catch (e) {
        return false;
    }

    return true;
}

function handleError(jqXHR, exception) {
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
        "label": "Close",
        "class": "btn pull-left"
    }, {header: 'Oh noes, error occurred'});
}

Array.prototype.chunk = function (n) {
    if (!this.length) {
        return [];
    }
    return [ this.slice(0, n) ].concat(this.slice(n).chunk(n));
};