<?php
session_start();

// Allow the score to be updated again the next time the results page is landed on.
$_SESSION['score_updated'] = false;

$link = mysqli_connect("localhost", "root", "root", "webdev-blog");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MYSQL: " . mysqli_connect_error();
    exit();
}

$sql = "SELECT * FROM wp_game WHERE RoomCode = '".$_SESSION['game_roomcode']."'";

if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$result = mysqli_query($link, $sql);
$game_info = mysqli_fetch_assoc($result);

mysqli_free_result($result);

$game_info['Question']++;

$sql = "UPDATE wp_game SET Question='".$game_info['Question']."' WHERE RoomCode='".$_SESSION['game_roomcode']."'";
if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

?>
