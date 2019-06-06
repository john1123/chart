$(document).ready(function() {
    var $inputCode = $('#input_code');
    var $inputText = $('#input_text');
    var $inputSubmit = $('#input_submit');

    if ($inputCode.val() == '') {
        $inputSubmit.prop('disabled',true);
    }
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