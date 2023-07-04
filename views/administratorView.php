<?php
//phpinfo();
//?>

<div class="administrator_queue">

    <div class="head_admin_queue">

        <div class="condition_queue">
            <h1>Данные очереди</h1>

            <h4 id="new_queue">Новые: <?=$conditionQueue['new_queue']?></h4>
            <h4 id="called_queue">Вызываются: <?=$conditionQueue['called_queue']?></h4>
            <h4 id="inWork_queue">В работе: <?=$conditionQueue['inWork_queue']?></h4>
            <h4 id="all_queue">Все на <?=date('d.m.Y')?>: <?=$conditionQueue['all_queue']?></h4>

        </div>

        <div class="condition_windows">
            <h1>Данные о состоянии окон выдачи</h1>

            <h4 id="lunchtime_windows">На обеде: <?=$conditionWindows['lunchtime_windows']?></h4>
            <h4 id="wait_lunches">Запрашивают обед: <b></b></h4>

            <h4 id="timeout_windows">На перерыве: <?=$conditionWindows['timeout_windows']?></h4>
            <h4 id="wait_intervals">Запрашивают перерыв: <b></b></h4>

            <h4 id="count_windows_work">Всего работают: <?=$conditionWindows['count_windows_work']?></h4>

        </div>

        <div class="service_queue">
            <h1>Настройки</h1>

            <nav class="management">
                <ul>
                    <li><a href="#" onclick="return false;">обедов</a>
                        <ul>
                            <li><a id="lunch_count" href="#" onclick="return false;">кол-во</a></li>
                            <li><a id="lunch_begin" href="#" onclick="return false;">начало</a></li>
                            <li><a id="lunch_end" href="#" onclick="return false;">окончание</a></li>
                        </ul>
                    </li>

                    <li><a href="#" onclick="return false;">перерывов</a>
                        <ul>
                            <li><a id="interval_count" href="#" onclick="return false;">кол-во</a></li>
                            <li><a id="interval_begin" href="#" onclick="return false;">начало</a></li>
                            <li><a id="interval_end" href="#" onclick="return false;">окончание</a></li>
                        </ul>
                    </li>

                    <li><a href="#" onclick="return false;">курьеров</a>
                        <ul>
                            <li><a id="waiting_timeout" href="#" onclick="return false;">ожидание</a></li>
                        </ul>
                    </li>

                </ul>
            </nav>

        </div>

    </div>

    <div class="queue_in_windows">

        <?php
            $id = 1;
            for ($i = 1; $i <= 7; $i++){
                echo '<div class="string_queue">';

                for ($j = 1; $j <= 8; $j++){
                    echo '<h4 id="'.$id.'"></h4>';
                    $id++;
                }

                echo '</div>';
            }
        ?>

    </div>

</div>

