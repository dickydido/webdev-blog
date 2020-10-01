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
                var r = confirm('Not everyone in the game has answered. Do you want to continue?');
                if (r == true) {
                    window.location = $('#see-results').attr("href");
                }
            }
        }, 300);
    });

    $('#restart-player').click(function() {
        if ($('#answers-checker').length != 0) {
            $('#answers-checker').load("../../wp-content/themes/twentyseventeen/restartquiz.php");
        } else if ($('#ajax-receiver').length != 0) {
            $('#ajax-receiver').load("../wp-content/themes/twentyseventeen/restartquiz.php");
        }

        window.location.reload();
    });

    $('#next-question').click(function(e) {
        e.preventDefault();
        $('#next-ajax').load("../wp-content/themes/twentyseventeen/nextquestion.php");

        setTimeout( function() {
            window.location = $('#next-question').attr("href");
        }, 300);
    });

});
