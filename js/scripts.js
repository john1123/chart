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
    var last = $("#settings_last").is(':checked');
    var depth = $("#settings_depth").val();


    //
    setTimeout(function () {
        $btn.button('reset');
    }, 1000);
});

$(window).resize(function() {
    var $chartPlaceholder = $('#chart_placeholder');
    var width = $chartPlaceholder.width();
    var height = (width/2).toFixed();
    $.post( "chart.php?width=" + width + "&height=" + height, {"data": data}, function( data ) {
        $chartPlaceholder.html( data );
    });
});