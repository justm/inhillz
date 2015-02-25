<!DOCTYPE html>
<?php 

?>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo ROOT_URL . 'style/content.main/favicon.ico'?>"/>
        <link rel="stylesheet" href="<?php echo ROOT_URL . 'style/main.dev.css'?>"/>
        <?php 

        //** Javascript 
        
        ?><script type="text/javascript" src="<?php echo APP_URL . 'javascript/jquery-1.11.0.min.js'?>"></script>
    </head>
    <body>
	<?php 
	
	  echo $content;
	
	?>
        <?php 
            //** THIRD PART ASSETS
            include dirname(__FILE__ ) . '/assets.php';
        ?>
        <script type="text/javascript" src="<?php echo ROOT_URL . 'style/bootstrap/js/bootstrap.js'?>"></script>
        <script type="text/javascript" src="<?php echo APP_URL . 'javascript/main.dev.js'?>"></script>
        <script type="text/javascript">
            $('*[data-toggle="tooltip"]').tooltip();
        </script>
    </body>
</html>
