$( document ).ready(function() {

    /**
     * Функция выбора окна выдачи
     */
    $('#window').click(function () {
        // console.log('Выбор номера окна');

        var params = {action: 'getCountWindow'};
        var inp;

        $.ajax({
            type: 'POST',
            // async: true,
            async: false,
            url: "/login",
            data: params,
            dataType: 'json',
            success: function (data) {
                // console.log(data);
                inp = prompt('Введите номер окна. 1 - ' + data,'1');
                if((inp.match(/\d+/) !== null) & (inp <= data)){
                    var win = {window: inp};
                    $.ajax({
                        type: 'POST',
                        // async: true,
                        async: false,
                        url: "/login",
                        data: win,
                        dataType: 'json',
                        success: function (text) {
                            if (text === 'win'){
                                document.location.href = "/window";
                            }
                        }
                    })

                } else {
                    alert('Введите корректное число от 1 до '+data);
                };
            }
        })
    });

});