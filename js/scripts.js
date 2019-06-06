$(document).ready(function() {
    var $inputCode = $('#input_code');
    var $inputText = $('#input_text');
    var $inputSubmit = $('#input_submit');
    var $chartPlaceholder = $('#chart_placeholder');

    if ($inputCode.val() == '') {
        $inputSubmit.prop('disabled',true);
    }
    var width = $chartPlaceholder.width();
    var height = (width/3).toFixed();
    $chartPlaceholder.load("/chart.php?code=" + code + "&width=" + width + "&height=" + height + "&depth=" + depth);
    $inputText.autocomplete({
        serviceUrl: 'autocomplete.php',
        paramName: 'search',
        //minChars: 3,
        autoSelectFirst: true,
        onSelect: function(suggestions) {
            console.log(suggestions);
            $inputCode.val(suggestions.code);
            $inputSubmit.prop('disabled',false);
        }
    });
});
$(window).resize(function() {
    var $chartPlaceholder = $('#chart_placeholder');
    var width = $chartPlaceholder.width();
    var height = (width/2).toFixed();
    $chartPlaceholder.load("/chart.php?code=" + code + "&width=" + width + "&height=" + height + "&depth=" + depth);
});