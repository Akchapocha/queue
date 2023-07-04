$( document ).ready(function() {

    $( document ).keypress(function(e) {

        if(e.which == 13) {
            var printer = {printer: 'printPDF'};

            $.ajax({
                type: 'POST',
                // async: true,
                async: false,
                url: "/printer",
                data: printer,
                dataType: 'json',
                success: function (data) {
                    if (data){
                        var win = window.open('about:blank', '','width=300, height=300, left=800, top=400');
                        win.document.write(data);
                        setTimeout(function () { win.close();}, 3000);
                    }
                }
            });

        }

    });

});