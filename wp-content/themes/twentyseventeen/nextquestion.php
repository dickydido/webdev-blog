<?php
session_start();

// Allow the score to be updated again the next time the results page is landed on.
$_SESSION['score_updated'] = false;

// Set a session to update the questions in the database when the host page is next loaded.
$_SESSION['update_question'] = true;

if ($_SESSION['site'] == 'local') {
    $link = mysqli_connect("localhost", "root", "root", "webdev-blog");
} else {
    $link = mysqli_connect("grh27", "richie_wp1", "S.WBkXfYYziuElP7lmB06", "richie_wp1");
}

if (mysqli_connect_errno()) {
    echo "Failed to connect to MYSQL: " . mysqli_connect_error();
    exit();
}

$sql = "UPDATE wp_players SET Answer='' WHERE RoomCode='".$_SESSION['game_roomcode']."'";
if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

?>
