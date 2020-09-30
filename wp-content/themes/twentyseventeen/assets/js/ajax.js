$(document).ready(function() {

    $('#show-players').click(function() {

        $('#welcome').load("../../wp-content/themes/twentyseventeen/getplayers.php");
    });

    $('#see-results').click(function(e) {
        e.preventDefault();
        $('#answers-checker').load("../../wp-content/themes/twentyseventeen/seeresults.php");

        setTimeout( function() {
            if ($('#answers-checker').html() == 'true') {
                window.location = $('#see-results').attr("href");
            } else {
                var r = confirm('test');
                if (r == true) {
                    window.location = $('#see-results').attr("href");
                }
            }
        }, 300);
    });

    // $('#see-results').click(function(e) {
    //
    //     // $('#answers-checker').load("../../wp-content/themes/twentyseventeen/seeresults.php");
    // });

});
