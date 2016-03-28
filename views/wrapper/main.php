<!DOCTYPE html><?php 

?><html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo ROOT_URL . 'style/content.main/favicon.ico'?>"/>
        <link rel="stylesheet" href="<?php echo ROOT_URL . 'style/main.dev.css'?>"/>
        <script type="text/javascript" src="<?php echo APP_URL . 'javascript/jquery-1.11.0.min.js'?>"></script>
    </head>
    <body>
        <nav class="navbar navbar-default" role="navigation" id="mh">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mh-navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a id="main-logo" class="navbar-brand" href="http://inhillz.com/en/diary"></a>
                </div>
                <div class="collapse navbar-collapse" id="mh-navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li><?php
                            // echo '<a href="#">' .Orchid::t( 'Your account', 'user') . '</a>';
                        ?></li>
                        <li><?php
                            // echo '<a href="#">' .Orchid::t( 'Your account', 'user' ) . '</a>';
                        ?></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container" id="main-container">
            <div class="row"><?php 

              echo $content;

            ?></div>
        </div>
        <script type="text/javascript" src="<?php echo ROOT_URL . 'style/bootstrap/js/bootstrap.js'?>"></script>
        <script type="text/javascript" src="<?php echo APP_URL . 'javascript/alerts.js'?>"></script>
        <script type="text/javascript" src="<?php echo APP_URL . 'javascript/main.dev.js'?>"></script>
        <script type="text/javascript">
            $('*[data-toggle="tooltip"]').tooltip();
        </script>
    </body>
</html>
