<?php

session_start();

$link = new mysqli("localhost", "root", "root", "webdev-blog");

if ($link->connect_error) {
    exit('Could not connect');
}

$sql = "SELECT Name FROM wp_players WHERE RoomCode='".$_SESSION['game_roomcode']."'";

$result = mysqli_query($link, $sql);

// $players = [];

if (mysqli_num_rows($result) > 0) {
    //Store output of each row into array.
    while($row = mysqli_fetch_assoc($result)) {
        $players[] = $row['Name'];
    }
    mysqli_free_result($result);
}

if ($players) {
    echo 'Players: ' . implode(', ', $players);
} else {
    echo 'Players: 0 found';
}

?>
