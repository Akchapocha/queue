$('.date>button').click(function () {

    var getStat = {
        updateStatByDate: $('.date>input').val()
    };

    $.ajax({
        type: 'POST',
        // async: true,
        async: false,
        url: "/stat",
        data: getStat,
        dataType: 'json',
        success: function (data) {

            $('#1').text(data[1]);
            $('#2').text(data[2]);

        }
    });

});