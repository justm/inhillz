<!DOCTYPE html><?php 

?><html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo ROOT_URL . 'style/content.main/favicon.ico'?>"/>
        <?php 
        
            //$this->displaySeo(); 
            
        ?><link rel="stylesheet" href="<?php echo ROOT_URL . 'style/main.dev.css'?>"/>
        <?php 

        //** Javascript 
        
        ?><script type="text/javascript" src="<?php echo APP_URL . 'javascript/jquery-1.11.0.min.js'?>"></script>
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
                    <a id="main-logo" class="navbar-brand" href="http://inhillz.com/"></a>
                </div>
<!--                    <form class="navbar-form" role="search" id="mhsr" action="<?php // echo ENTRY_SCRIPT_URL ?>page/find/" method="GET">
                    <div class="input-group" id="mhsr-inner">
                        <div class="input-group-addon" type="submit">
                            <button type="submit" class="clean btn"><span class="glyphicon glyphicon-search"></span></button>
                        </div>
                        <input type="text" class="form-control" placeholder="<?php // echo Mcore::t( 'Type to find anything' ); ?>">
                        <ul id="searchResp" class="dropdown-menu">
                            <li class="dropdown-header"><?php // echo Mcore::t( 'Searching...' ); ?></li>
                        </ul>
                    </div>
                </form>-->
                <div class="collapse navbar-collapse" id="mh-navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li><?php
                            // echo '<a href="#">' .Mcore::t( 'Your account', 'user') . '</a>';
                        ?></li>
                        <li><?php
                            // echo '<a href="#">' .Mcore::t( 'Your account', 'user' ) . '</a>';
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
        <?php 
            //** THIRD PART ASSETS
            include dirname(__FILE__ ) . '/assets.php';
        ?>
        <script type="text/javascript" src="<?php echo ROOT_URL . 'style/bootstrap/js/bootstrap.js'?>"></script>
        <script type="text/javascript" src="<?php echo APP_URL . 'javascript/alerts.js'?>"></script>
        <script type="text/javascript" src="<?php echo APP_URL . 'javascript/main.dev.js'?>"></script>
        <script type="text/javascript">
            $('*[data-toggle="tooltip"]').tooltip();
        </script>
    </body>
</html>
