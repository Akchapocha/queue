<?php

?>

<div class="date">

    <input type="date" value="<?=$yesterday?>" max="<?=$dateNow?>">
    <button type="button">Сформировать отчет</button>

</div>

<div class="stat">

    <div class="headStat">
        <h4>Очередь 1</h4>
        <h4>Очередь 2</h4>
    </div>

    <div class="bodyStat">
        <h4 id="1"><?=$queue1?></h4>
        <h4 id="2"><?=$queue2?></h4>
    </div>

</div>
