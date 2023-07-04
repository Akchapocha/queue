<div class="window_queue">

    <div class="window_info">
        <?php
            if (isset($window)){
                if (intval($window) > 0){
                    echo '<h1>Окно №'.$window.'</h1>';
                } else {
                    echo '<h1>Данные распределения очереди</h1>';
                }
            } else {
                echo '<h1>Данные распределения очереди</h1>';
            }

            if (isset($queue)){
                echo '<h4 id="AllQueue">Всего в очереди: '.$queue['AllQueue'].'</h4>';
                if ($queue['personalQueue'] !== []){
                    echo '<h4 id="personalQueue">У Вас в очереди: '.$queue['personalQueue']['number_queue'].'</h4>';
                } else {
                    echo '<h4 id="personalQueue">У Вас в очереди: никого</h4>';
                }
            } else {
                echo '<h4 id="AllQueue">Всего в очереди: никого</h4>
                      <h4 id="personalQueue">У Вас в очереди: никого</h4>';
            }

            if (isset($windows)){
                echo '<h4 id="timeout_windows">На перерыве: '.$windows['timeout_windows'].'</h4>
                      <h4 id="lunchtime_windows">На обеде: '.$windows['lunchtime_windows'].'</h4>
                      <h4 id="all_windows_work">Всего работают: '.$windows['all_windows_work'].'</h4>';
            } else {
                echo '<h4 id="timeout_windows">На перерыве: </h4>
                      <h4 id="lunchtime_windows">На обеде: </h4>
                      <h4 id="all_windows_work">Всего работают: </h4>';
            }
        ?>


    </div>

    <div class="window_service">

        <?php
            if (isset($windows)){
                echo '<h1 id="status">Статус окна: <b>'.$windows['status']['status_name'].'</b></h1>';
            } else {
                echo '<h1 id="status">Статус окна: не определен</h1>';
            }
        ?>

        <h4><button id="cs_free">Окно свободно/Вызвать следующего</button></h4>
        <h4><button id="cs_collect">Сборка</button></h4>
        <h4 id="messages"></h4>
        <h4><button id="request_interval">Заросить перерыв</button></h4>
        <h4><button id="request_lunch">Запросить обед</button></h4>
        <h4><button id="escape">Окончание рабочего дня</button></h4>

<!--        <h4><a href="/"><button>На главную</button></a></h4>-->

    </div>



</div>