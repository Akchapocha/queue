<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title><?=$title?></title>

        <script src="/js/jquery331.min.js"></script>

        <link href="/css/main.css" rel="stylesheet">

    </head>

    <body>

        <div class="content_queue">
            <?=$cv?>
        </div>

        <?php
            if (isset($js)){
                echo $js;
            }
        ?>

    </body>

</html>