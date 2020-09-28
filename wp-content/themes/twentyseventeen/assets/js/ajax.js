$(document).ready(function() {

    $('#show-players').click(function() {

        $('#welcome').load("../../wp-content/themes/twentyseventeen/getplayers.php");
    });

});
