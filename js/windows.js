$( document ).ready(function() {
    /**
     * Функция для смены статуса на "свободен"
     */
    $('#cs_free').click(function () {
        var cs_free = {
            window: $('.window_info>h1').text(),
            newStatus: 'free'
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/window",
            data: cs_free,
            dataType: 'json',
            success: function (data) {
                if (data !== 'Смена статуса прошла успешно'){
                    alert(data);
                }
            }
        });

        $('#cs_free').attr('disabled',true);

    });

    /**
     * Функция для смены статуса на "сборка"
     */
    $('#cs_collect').click(function () {
        var cs_collect = {
            window: $('.window_info>h1').text(),
            newStatus: 'assemble'
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/window",
            data: cs_collect,
            dataType: 'json',
            success: function (data) {
                if (data !== 'Смена статуса прошла успешно'){
                    alert(data);
                }
            }
        });

        $('#cs_collect').attr('disabled',true);

    });


    /**
     * Функция опроса сервера на предмет изменений
     *
     * @param timelap - Временной интервал опроса в милисекундах
     */
    var timelap = 1000;

    var listen = function(){

        var action = {
            window: $('.window_info>h1').text(),
            action: 'getAction'
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/window",
            data: action,
            dataType: 'json',
            success: function (text) {
                if (parseInt(text['action'],10) === 1){
                    getNewValues(action['window']);
                }

                if (text['buttons']) {
                    disableButtons(text['buttons']);
                }

                if (text['waiting']) {
                    $('#messages').text(text['waiting']);
                } else {
                    $('#messages').text('');
                }
            }
        });

        setTimeout(arguments.callee,timelap);
    };

    setTimeout( listen,timelap );


    /**
     * Функция получения новых данных и внесения изменений на страницу "window"
     *
     * @param window - номер окна
     */
    function getNewValues(window) {

        var newVal = {
            window: window,
            values: 'getNewValues'
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/window",
            data: newVal,
            dataType: 'json',
            success: function (values) {
                if (values){
                    // console.log(values);
                    /**замена значений*/
                    var AllQueue = values['queue']['AllQueue'];

                    if (values['queue']['personalQueue']['number_queue']){
                        var personalQueue = values['queue']['personalQueue']['number_queue'];
                    } else {
                        var personalQueue = 'никого';
                    }

                    var timeout_windows = values['windows']['timeout_windows'];
                    var lunchtime_windows = values['windows']['lunchtime_windows'];
                    var all_windows_work = values['windows']['all_windows_work'];

                    var status = values['windows']['status']['status_name'];
                    var color = values['windows']['status']['status_color'];

                    $('#AllQueue').text('Всего в очереди: '+AllQueue);
                    $('#personalQueue').text('У Вас в очереди: '+personalQueue);

                    $('#timeout_windows').text('На перерыве: '+timeout_windows);
                    $('#lunchtime_windows').text('На обеде: '+lunchtime_windows);
                    $('#all_windows_work').text('Всего работают: '+all_windows_work);

                    $('#status>b').text(status);
                    $('#status>b').css('color', color);

                }
            }
        });

    }


    /**
     *Функция деактивации кнопок
     */
    function disableButtons(params) {

        if (parseInt(params['1'],10) === 1){
            $('#cs_free').removeAttr('disabled');
        } else {
            $('#cs_free').attr('disabled',true);
        }

        if (parseInt(params['2'],10) === 1){
            $('#cs_collect').removeAttr('disabled');
        } else {
            $('#cs_collect').attr('disabled',true);
        }

        if (parseInt(params['3'],10) === 1){
            $('#request_interval').removeAttr('disabled');
        } else {
                $('#request_interval').attr('disabled',true);
        }

        if (parseInt(params['4'],10) === 1){
            $('#request_lunch').removeAttr('disabled');
        } else {
            $('#request_lunch').attr('disabled',true);
        }

        if (parseInt(params['5'],10) === 1){
            $('#escape').removeAttr('disabled');
        } else {
            $('#escape').attr('disabled',true);
        }
    }


    /**
     * Функция вызова запроса перерыва
     */
    $('#request_interval').click(function () {
        getInterval('getInterval');
    });


    /**
     * Функция вызова запроса обеда
     */
    $('#request_lunch').click(function () {
        getInterval('getLunch');
    });


    /**
     * Функция запроса перерывов
     *
     * @param interval - перерыв/обед
     */
    function getInterval(interval) {

        var window = {
            window: $('.window_info>h1').text(),
            request: interval
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/window",
            data: window,
            dataType: 'json',
            success: function (data) {
                if ((data === 'Запрос перерыва прошел успешно') || (data === 'Вы уже запросили перерыв')){
                    $('#request_interval').attr('disabled',true);
                    $('#request_lunch').attr('disabled',true);
                }
            }
        });
    }

    /**
     * Функция окончания рабочего времени
     */
    $('#escape').click(function () {

        var window = {
            window: $('.window_info>h1').text(),
            newStatus: 'escape'
        };

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/window",
            data: window,
            dataType: 'json',
            success: function (data) {
                if (data){
                    console.log(data);
                }
            }
        });
    });

});

