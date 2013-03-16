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
<html>
    <head>
        <title>phpUnderControl Build Monitor</title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="libs/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
        <link href="libs/colorbox/css/colorbox.css" rel="stylesheet" media="screen">
        <link href='http://fonts.googleapis.com/css?family=Cuprum:400,700,400italic,700italic' rel='stylesheet' type='text/css'>

        <link href="css/screen.css" rel="stylesheet" media="screen">
        <link href="css/responsive.css" rel="stylesheet" media="screen">

        <script type="text/javascript">
            var baseHref = '<?php echo System::$baseHref; ?>';
            var csrfToken = '<?php echo System::$csrfToken; ?>';
        </script>

    </head>
    <body>

        <div id="wrap">
            <div class="header container-fluid">
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
                                        <a href="#">Setup</a>
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
                    <li><a href="https://github.com/tarlepp/" target="_blank">Tarmo Leppanen</a></li>
                </ul>
            </div>
        </div>

        <script src="libs/bootstrap/js/bootstrap.min.js"></script>
        <script src="libs/jquery/jquery-1.9.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.1.1.min.js"></script>
        <script src="libs/jFeed/jquery.jfeed.pack.js"></script>
        <script src="libs/handlebars/handlebars.js"></script>
        <script src="libs/timeago/jquery.timeago.js"></script>
        <script src="libs/colorbox/js/jquery.colorbox-min.js"></script>

        <script src="js/monitor.js"></script>

        <script id="template-build" type="text/x-handlebars-template">
            <div class="build {{class}} {{getStatusClass description}}">
                <div class="title navbar">
                    <ul class="nav pull-right">
                        <li><a href="{{link}}" target="_blank"><i class="icon-search icon-white"></i></a></li>
                    </ul>
                    <h2>{{formatTitle title}}</h2>
                </div>
                <div class="content">
                    <div class="status pull-left"></div>
                    <div class="image pull-right">
                        <a id="href_{{index}}" href="#" class="colorbox" title="{{description}}"><img id="image_{{index}}" src="{{getStatusImage description index}}" /></a>
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
    </body>
</html>