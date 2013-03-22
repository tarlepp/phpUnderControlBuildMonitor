<?php
/**
 * Main php functionality for all AJAX requests
 */
use \phpUnderControlBuildMonitor\Core\System;

// We want to show all errors.
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

// Require system init file
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "init.php";

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>phpUnderControl Build Monitor</title>

        <base href="<?php echo System::$baseHref; ?>">

        <meta name="author" content="Tarmo Leppänen">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="phpUnderControl Build Monitor see https://github.com/tarlepp/phpUnderControlBuildMonitor"/>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

        <link href='http://fonts.googleapis.com/css?family=Cuprum:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
        <link href="libs/colorbox/css/colorbox.css" rel="stylesheet" media="screen">
        <link href="libs/jquery-ui/css/smoothness/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" media="screen">
        <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="libs/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
        <link href="css/screen.css" rel="stylesheet" media="screen">
        <link href="css/responsive.css" rel="stylesheet" media="screen">

        <script type="text/javascript">
            var baseHref = '<?php echo System::$baseHref; ?>';
            var csrfToken = '<?php echo System::$csrfToken; ?>';
        </script>

    </head>
    <body>

        <div id="wrap">
            <div id="header" class="header container-fluid">
                <div class="navbar navbar-inverse navbar-fixed-top">
                    <div class="navbar-inner">
                        <div class="container-fluid">
                            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>

                            <a class="brand" href="index.html">phpUnderControl Build Monitor</a>

                            <div class="responsiveWidth"></div>

                            <div id="mainNavigation" class="nav-collapse collapse">
                                <ul class="nav">
                                </ul>

                                <ul class="nav pull-right">
                                    <li>
                                        <a href="#" id="settingsLink">Settings</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="container" class="container-fluid container-builds">

            </div>

            <div id="push"></div>
        </div>

        <div id="footer">
            <div class="container-fluid">
                <ul>
                    <li><a href="https://github.com/tarlepp/phpUnderControlBuildMonitor" target="_blank">GitHub</a></li>
                    <li class="muted">&middot;</li>
                    <li><a href="https://github.com/tarlepp/phpUnderControlBuildMonitor/issues" target="_blank">Issues</a></li>
                    <li class="muted">&middot;</li>
                    <li><a href="https://github.com/tarlepp/" target="_blank">Tarmo Leppänen</a></li>
                </ul>
            </div>
        </div>

        <script src="libs/jquery/jquery-1.9.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.1.1.min.js"></script>
        <script src="libs/jquery-ui/js/jquery-ui-1.10.2.custom.min.js"></script>
        <script src="libs/bootstrap/js/bootstrap.min.js"></script>
        <script src="libs/bootbox/bootbox.min.js"></script>
        <script src="libs/jFeed/jquery.jfeed.pack.js"></script>
        <script src="libs/handlebars/handlebars.js"></script>
        <script src="libs/timeago/jquery.timeago.js"></script>
        <script src="libs/colorbox/js/jquery.colorbox-min.js"></script>

        <script src="js/monitor.js"></script>

        <script id="template-build" type="text/x-handlebars-template">
            <div class="build {{class}} {{getStatusClass description}}">
                <div class="title navbar">
                    <ul class="nav pull-right">
                        <li><a href="#" class="remove" data-project="{{formatTitle title}}" title="Remove this project"><i class="icon-remove icon-white"></i></a></li>
                    </ul>
                    <h2><a href="{{link}}" target="_blank" title="Open project into new tab">{{formatTitle title}}</a></h2>
                </div>
                <div class="content">
                    <div class="status pull-left"></div>
                    <div class="image pull-right">
                        <a id="href_{{index}}" href="#" class="colorbox" title="{{description}}"><img id="image_{{index}}" src="#" /></a>
                    </div>
                    <div class="info">
                        <h2><time class="timeago" datetime="{{updated}}">{{updated}}</time></h2>
                        <h3>{{description}}</h3>
                    </div>
                    <div style="clear: both"></div>
                </div>
            </div>
        </script>

        <script id="template-build-row" type="text/x-handlebars-template">
            <div class="row-fluid">
                <div class="span12">
                    <div class="row-fluid">
                        {{#each builds}}
                            {{{content}}}
                        {{/each}}
                    </div>
                </div>
            </div>
        </script>

        <script id="template-error-exception" type="text/x-handlebars-template">
            <div class="error-bootbox">
                <div class="alert alert-error">
                    <h3>Error</h3>
                    <p>
                        {{message}}
                    </p>
                    <h3>Information</h3>
                    <pre class="pre-scrollable text-mini">File: {{file}}<br />Line: {{line}}</pre>
                    <h3>Stack trace</h3>
                    <pre class="pre-scrollable text-mini">{{trace}}</pre>
                </div>
            </div>
        </script>

        <script id="template-error-common" type="text/x-handlebars-template">
            <div class="error-bootbox">
                <div class="alert alert-error">
                    <h3>Error</h3>
                    <p>
                        {{message}}
                    </p>
                    <h3>Information</h3>
                    <pre class="pre-scrollable text-mini">HTTP status {{status}}</pre>
                    <h3>Response</h3>
                    <pre class="pre-scrollable text-mini">{{responseText}}</pre>
                </div>
            </div>
        </script>

        <script id="template-setup" type="text/x-handlebars-template">
            <form id="setupForm" class="form-horizontal">
                <div class="control-group">
                    <label class="control-label" for="feedUrl">Feed URL</label>
                    <div class="controls">
                        <div id="generic" class="popover-container" style="width: 200px;" data-placement="bottom"></div>
                        <div class="input-append">
                            <input type="text" id="feedUrl" name="feedUrl" class="popover-container" data-placement="bottom" value="{{feedUrl}}" />
                            <button id="fetchProjects" class="btn" type="button">Fetch projects</button>
                        </div>
                        <span class="help-block">Enter phpUnderControl build feed url.</span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="buildsPerRow">Builds per row</label>
                    <div class="controls controls-slider">
                        <span class="uneditable-input">{{buildsPerRow}}</span>
                        <div id="buildsPerRowSlider" class="slider popover-container" data-placement="bottom" data-min="1" data-max="4"></div>
                        <input type="text" id="buildsPerRow" name="buildsPerRow" placeholder="" value="{{buildsPerRow}}" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="refreshInterval">Refresh interval</label>
                    <div class="controls controls-slider">
                        <span class="uneditable-input">{{refreshInterval}}</span>
                        <div id="refreshIntervalSlider" class="slider popover-container" data-placement="bottom" data-min="1" data-max="30"></div>
                        <input type="text" id="refreshInterval" name="refreshInterval" placeholder="" value="{{refreshInterval}}" />
                        <span class="help-block">Refresh interval in minutes</span>
                    </div>
                </div>
                <div id="setupBuilds">
                    <div class="control-group">
                        <label class="control-label">Select projects to show</label>
                        <div class="controls">
                            <div id="projectsToShow" class="popover-container" data-placement="top" style="width: 12px;"></div>
                            <div id="projects">
                            {{#each projects}}
                                <label>
                                    <input type="checkbox" name="projectsToShow[]" value="{{this.name}}"{{#if this.checked}} checked="checked"{{/if}} />
                                    {{this.name}}
                                </label>
                            {{/each}}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </script>

        <script id="template-setup-projects" type="text/x-handlebars-template">
            {{#each projects}}
            <label>
                <input type="checkbox" name="projectsToShow[]" value="{{this.name}}"{{#if this.checked}} checked="checked"{{/if}} />
                {{this.name}}
            </label>
            {{/each}}
        </script>

    </body>
</html>