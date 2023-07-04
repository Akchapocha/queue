$( document ).ready(function() {

    focusInput();

    /**
     * Функция установки фокуса на полее ввода номера
     */
    function focusInput(){
        document.getElementById("enter_num").focus();
    }


    /**
     * Функция опроса сервера на предмет изменений
     *
     * @param timelap - Временной интервал опроса в милисекундах
     */
    var timelap = 1000;

    var listen = function(){

        var action = {
            monitor: 'getNewValues',
        };

        focusInput();

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/monitor",
            data: action,
            dataType: 'json',
            success: function (data) {

                //     console.log(data);

                if ( !( /^Ближайшие билеты: \d+-?\d+?-?\d+? Всего: \d+ Ожидайте вызова$/.test(data) ) && (data !== 'Вся очередь распределена.') ){

                    var count = data.length - 1;
                    var color = data[count]['status_color'];

                    var i = 0;
                    var j = 1;
                    var k = 1;
                    // var column = 4; /**40 ячеек*/
                    var column = 3;
                    var row = 10;

                    $('.queue_wait>h4').remove();


                    for (i; i <count; i++){

                        $('.queue_wait:nth-child('+j+')').append('<h4><b>'+data[i]['number_queue']+'</b>&rarr;<b>'+data[i]['window']+'</b></h4>');

                        $('.queue_wait:nth-child('+j+')>h4:nth-child('+k+')').css('background-color', color);


                        if (k < row){
                            k++;
                        } else {
                            if (j < column){
                                j++;
                                k = 1;
                            }
                        }

                    }


                } else {

                    var arr = data.split(' ');
                    var countWord = arr.length;

                    $('.queue_wait>h4').remove();

                    // console.log(arr);

                    if (countWord === 7){

                        $('.queue_wait:nth-child(2)').append('<h4></h4>');

                        $('.queue_wait:nth-child(2)').append('<h4>'+arr[0]+'</h4>');

                        $('.queue_wait:nth-child(2)').append('<h4>'+arr[1]+'</h4>');

                        arr[2] = arr[2].split('-').join(', ');

                        $('.queue_wait:nth-child(2)').append('<h4>'+arr[2]+'</h4>');

                        $('.queue_wait:nth-child(2)').append('<h4></h4>');

                        $('.queue_wait:nth-child(2)').append('<h4>'+arr[3]+' '+arr[4]+'</h4>');

                        $('.queue_wait:nth-child(2)').append('<h4></h4>');

                        $('.queue_wait:nth-child(2)').append('<h4>'+arr[5]+'</h4>');

                        $('.queue_wait:nth-child(2)').append('<h4>'+arr[6]+'</h4>');




                    } else {

                        if (countWord === 3){

                            for (i=1; i<=3; i++) {
                                $('.queue_wait:nth-child(2)').append('<h4></h4>');

                            }

                            $('.queue_wait:nth-child(2)').append('<h4>'+arr[0]+'</h4>');

                            $('.queue_wait:nth-child(2)').append('<h4>'+arr[1]+'</h4>');

                            $('.queue_wait:nth-child(2)').append('<h4>'+arr[2]+'</h4>');


                        }

                    }

                }
            }
        });

        focusInput();

        setTimeout(arguments.callee,timelap);
    };


    setTimeout( listen,timelap );


    /**Функция печати талона*/
    $( document ).keypress(function(e) {

        if(e.which == 13) {
            var printer = {
                            printer: 'printPDF',
                            number_cart: $('#enter_num').val()
                        };


            $('#enter_num').val('');

            focusInput();

            $.ajax({
                type: 'POST',
                // async: true,
                async: false,
                url: "/monitor",
                data: printer,
                dataType: 'json',
                success: function (data) {
                    if (data){

                        var win = window.open('about:blank', '','width=300, height=200, left=800, top=400');
                        win.document.write(data);

                        setTimeout(function () { win.close();}, 3000);
                    }
                }
            });

            focusInput();
        }

    });

});