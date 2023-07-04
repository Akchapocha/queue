$( document ).ready(function() {

    /**
     * Функция опроса сервера и обновления данных на странице
     *
     * @param timelap - Временной интервал опроса в милисекундах
     */
    var timelap = 1000;

    var listen = function(){

        var action = {
            administrator: 'getNewValues'
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/administrator",
            data: action,
            dataType: 'json',
            success: function (data) {
                if (data){

                    var new_queue = data['conditionQueue']['new_queue'];
                    var called_queue = data['conditionQueue']['called_queue'];
                    var inWork_queue = data['conditionQueue']['inWork_queue'];
                    var all_queue = data['conditionQueue']['all_queue'];
                    var date = data['conditionQueue']['date'];

                    var timeout_windows = data['conditionWindows']['timeout_windows'];
                    var lunchtime_windows = data['conditionWindows']['lunchtime_windows'];
                    var count_windows_work = data['conditionWindows']['count_windows_work'];
                    var windows_work = data['conditionWindows']['windows_work'];

                    var wait_intervals = data['conditionWindows']['wait_intervals'];
                    var wait_lunches = data['conditionWindows']['wait_lunches'];

                    $('#new_queue').text('Новые: '+new_queue);
                    $('#called_queue').text('Вызываются: '+called_queue);
                    $('#inWork_queue').text('В работе: '+inWork_queue);
                    $('#all_queue').text('Все на '+date+': '+all_queue);

                    $('#timeout_windows').text('На перерыве: '+timeout_windows);
                    $('#lunchtime_windows').text('На обеде: '+lunchtime_windows);
                    $('#count_windows_work').text('Всего работают: '+count_windows_work);

                    $('#wait_intervals>b').text(wait_intervals);
                    $('#wait_lunches>b').text(wait_lunches);

                    var request = 0;

                    if (parseInt(wait_lunches,10) > 0){
                        $('#wait_lunches>b').css('color', 'red');
                        $('#wait_lunches>b').css('cursor', 'pointer');
                        request = 1;
                    } else {
                        $('#wait_lunches>b').css('color', 'black');
                        $('#wait_lunches>b').css('cursor', 'auto');
                    }

                    if (parseInt(wait_intervals,10) > 0){
                        $('#wait_intervals>b').css('color', 'red');
                        $('#wait_intervals>b').css('cursor', 'pointer');
                        request = 1;
                    } else {
                        $('#wait_intervals>b').css('color', 'black');
                        $('#wait_intervals>b').css('cursor', 'auto');
                    }

                    if (request === 1){
                        $('title').text('Есть запросы на обеды или перерывы.');
                    } else {
                        $('title').text('Администратор склада');
                    }

                    var i;
                    var j = 1, k = 1;
                    var column = 8;
                    // var row = 12;/*96 ячеек**/
                    var row = 7;

                    $('.string_queue>h4').children().remove();
                    $('.string_queue>h4').css('background-color','#f0f1f5');

                    for (i = 1; i <= count_windows_work; i++){ /**количество окон*/

                        if (windows_work[i-1]){

                            if ( ( parseInt(windows_work[i-1]['waiting'], 10) > 0 ) && ( parseInt(windows_work[i-1]['confirm'], 10) === 0 ) ){
                                $('.string_queue:nth-child('+j+')>h4:nth-child('+k+')').css('background-color', '#F531D2');
                                $('.string_queue:nth-child('+j+')>h4:nth-child('+k+')').attr('title', windows_work[i-1]['status_name']+'. Запрашивает обед или перерыв.');
                            } else {
                                $('.string_queue:nth-child('+j+')>h4:nth-child('+k+')').css('background-color', windows_work[i-1]['status_color']);
                                $('.string_queue:nth-child('+j+')>h4:nth-child('+k+')').attr('title', windows_work[i-1]['status_name']);
                            }

                            if (windows_work[i-1]['queue']){
                                $('.string_queue:nth-child('+j+')>h4:nth-child('+k+')').append('<h6>'+windows_work[i-1]['number_window']+'&larr;'+windows_work[i-1]['queue']+'</h6>');

                            } else {
                                $('.string_queue:nth-child('+j+')>h4:nth-child('+k+')').append('<h6>'+windows_work[i-1]['number_window']+'</h6>');
                            }

                            if (windows_work[i-1]['status'] >= 3) {
                                $('.string_queue:nth-child(' + j + ')>h4:nth-child(' + k + ')').append('<h6>' + windows_work[i - 1]['duration'] + '</h6>');
                            }

                            if (+windows_work[i-1]['status'] === +1) {
                                $('.string_queue:nth-child(' + j + ')>h4:nth-child(' + k + ')').append('<h6>Очередь: ' + windows_work[i - 1]['priority'] + '</h6>');
                            }


                            if (k < column){
                                k++;
                            } else {

                                if (j < row){
                                    j++;
                                    k = 1
                                }

                            }

                        }
                    }

                }
            }
        });

        setTimeout(arguments.callee, timelap);
    };

    setTimeout( listen, timelap );

    /**
     * Функция вызова одобрения перерыва
     */
    $('#wait_intervals>b').click(function () {

        if ( $(this).text() !== '0' ) {
            var checNumber = RegExp(/^(\d){1,3}$/);
            var number = prompt('Укажите количество запросов на перерыв, которое Вы хотите одобрить');

            if (number.match(checNumber) === null) {
                alert('Введено не корректное количество');
            } else {
                conf('interval', number);
            }
        }

    });

    /**
     * Функция вызова одобрения обеда
     */
    $('#wait_lunches>b').click(function () {

        if ( $(this).text() !== '0' ) {
            var checNumber = RegExp(/^(\d){1,3}$/);
            var number = prompt('Укажите количество запросов на обед, которое Вы хотите одобрить');

            if (number.match(checNumber) === null) {
                alert('Введено не корректное количество');
            } else {
                conf('lunch', number);
            }
        }

    });

    /**
     * Функция одобрения перерыва/обеда
     *
     * @param interval - перерыв/обед
     * @param number - количество
     */
    function conf(interval,number){
        var confInterval = {
            interval:interval,
            confInterval: number
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/administrator",
            data: confInterval,
            dataType: 'json',
            success: function (data) {
                if (data !== 'Одобрение прошло успешно'){
                    alert(data);
                }
            }
        });
    }

    /**
     * Функция одобрения обеда/перерыва при нажатии на иконку окна
     */
    $('.string_queue>h4').click(function () {

        let id = $(this).attr('id');

        let color = $('.string_queue>h4#'+id).css('background-color');
        let numWin = $('.string_queue>h4#'+id+'>h6:first-child').text();

        let confirmed = {numWin: numWin};

        if (color === 'rgb(245, 49, 210)') {

            let conf = confirm('Отпустить на обед/перерыв окно №'+numWin+' ?');

            if (conf === true) {
                $.ajax({
                    type: 'POST',
                    // async: true,
                    async: false,
                    url: "/administrator",
                    data: confirmed,
                    dataType: 'json',
                    success: function (data) {
                        if (data){
                            alert(data);
                        }
                    }
                });
            }

        }

    });


    /**
     * Функция вызова установки количества обедов
     */
    $('#lunch_count').click(function () {
        var set = getSettings();

        var lunchCount = {setLunch_count: prompt('Введите допустимое количество обедов в день (1 - 3)', set['1']['count'])};

        if (lunchCount['setLunch_count'] !== null) {
            setLunchInterval(lunchCount);
        }
    });

    /**
     * Функция вызова установки времени начала обедов
     */
    $('#lunch_begin').click(function () {
        var set = getSettings();

        var lunchBegin = {setLunch_begin: prompt('Введите время начала обедов', set['1']['time_begin'])};

        if (lunchBegin['setLunch_begin'] !== null) {
            setLunchInterval(lunchBegin);
        }
    });

    /**
     * Функция вызова установки времени окончания обедов
     */
    $('#lunch_end').click(function () {
        var set = getSettings();

        var lunchEnd = {setLunch_end: prompt('Введите время окончания обедов', set['1']['time_end'])};

        if (lunchEnd['setLunch_end'] !== null) {
            setLunchInterval(lunchEnd);
        }
    });

    /**
     * Функция вызова установки количества перерывов
     */
    $('#interval_count').click(function () {
        var set = getSettings();

        var intervalCount = {setInterval_count: prompt('Введите допустимое количество перерывов в день (1 - 5)', set['2']['count'])};

        if (intervalCount['setInterval_count'] !== null) {
            setLunchInterval(intervalCount);
        }
    });

    /**
     * Функция вызова установки времени начала перерывов
     */
    $('#interval_begin').click(function () {
        var set = getSettings();

        var intervalBegin = {setInterval_begin: prompt('Введите время начала перерывов', set['2']['time_begin'])};

        if (intervalBegin['setInterval_begin'] !== null) {
            setLunchInterval(intervalBegin);
        }
    });

    /**
     * Функция вызова установки времени окончания перерывов
     */
    $('#interval_end').click(function () {
        var set = getSettings();

        var intervalEnd = {setInterval_end: prompt('Введите время окончания перерывов', set['2']['time_end'])};

        if (intervalEnd['setInterval_end'] !== null) {
            setLunchInterval(intervalEnd);
        }
    });

    /**
     * Функция вызова установки времени ожидания курьеров
     */
    $('#waiting_timeout').click(function () {
        var set = getSettings();

        var waiting_timeout = {setWaiting_timeout: prompt('Введите количество минут для ожидания курьера, (1 - 10 мин.)', set['3']['period'])};

        if (waiting_timeout['setWaiting_timeout'] !== null) {
            setLunchInterval(waiting_timeout);
        }
    });


    /**
     * Функция получения настроек
     */
    function getSettings() {
        var settings = {settings: 'getSettings'};

        var set = '';

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/administrator",
            data: settings,
            dataType: 'json',
            success: function (data) {
                if (data){
                    set = data;
                }
            }
        });

        return set;
    }

    /**
     * Функция установки настроек обедов/перерывов
     *
     * @param values - значения установок
     */
    function setLunchInterval(values) {

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/administrator",
            data: values,
            dataType: 'json',
            success: function (data) {
                if (data){
                    alert(data);
                }
            }
        });

    }





});