<?php

session_start();

$link = mysqli_connect("localhost", "root", "root", "webdev-blog");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MYSQL: " . mysqli_connect_error();
    exit();
}

$sql = "SELECT * FROM wp_game WHERE RoomCode = '".$_SESSION['roomcode']."'";

if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$result = mysqli_query($link, $sql);
$game_info = mysqli_fetch_assoc($result);

mysqli_free_result($result);

$sql = "SELECT * FROM wp_players WHERE ID = '".$_SESSION['id']."'";

if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$result = mysqli_query($link, $sql);
$player_info = mysqli_fetch_assoc($result);

mysqli_free_result($result);

if ($player_info['Round'] != $game_info['Round'] || $player_info['Question'] != $game_info['Question']) {
    $sql = "UPDATE wp_players SET Question='".$game_info['Question']."', Round='".$game_info['Round']."' WHERE ID='".$_SESSION['id']."'";
    if (mysqli_query($link, $sql)) {

    } else {
        echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
    }
    unset($_SESSION['submit-answer']);
}





?>
