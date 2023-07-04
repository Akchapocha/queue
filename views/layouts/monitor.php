<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>

<!--        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>-->

<!--        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">-->
        <script src="/js/jquery331.min.js"></script>
        <link href="/css/monitor.css" rel="stylesheet">

    </head>

    <body>
        <div class="content_queue_monitor">
            <?=$cv?>
        </div>

        <?php
            if (isset($js)){
                echo $js;
            }
        ?>

    </body>
</html>