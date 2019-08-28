$(document).ready(function() {
    var $chartPlaceholder = $('#chart_placeholder');
    var $inputCode = $('#input_code');

    var width = $chartPlaceholder.width();
    var height = (width/3).toFixed();
    $.post( "chart.php?width=" + width + "&height=" + height, {"data": data}, function( data ) {
        $chartPlaceholder.html( data );
    });
    $inputCode.select2({theme: "bootstrap"});
    //$inputCode.on('select2:select', function () {
    //    console.log(this);
    //});
});

$("#settings_save").on('click', function() {
    var $btn = $(this);
    $btn.button('loading');
    var settings = {
        "showLastPrice" : $("#settings_last").is(':checked'),
        "depth" : $("#settings_depth").val()
    };
    $.ajax({
        type: "POST",
        url: "index.php",
        data: {
            "settings": JSON.stringify(settings)
        },
        success: function(data) {
            $btn.button('reset');
            $('#settingsModal').modal('hide');
        }
    });
});

$(window).resize(function() {
    var $chartPlaceholder = $('#chart_placeholder');
    var width = $chartPlaceholder.width();
    var height = (width/2).toFixed();
    $.post( "chart.php?width=" + width + "&height=" + height, {"data": data}, function( data ) {
        $chartPlaceholder.html( data );
    });
});