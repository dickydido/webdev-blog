<?php

session_start();

$link = new mysqli("localhost", "root", "root", "webdev-blog");

if ($link->connect_error) {
    exit('Could not connect');
}

$sql = "SELECT * FROM wp_players WHERE RoomCode='".$_SESSION['game_roomcode']."'";

if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$result = mysqli_query($link, $sql);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

$finished = 'true';

// echo '<pre>';
// print_r($rows);
// echo '</pre>';

foreach($rows as $row) {
    if (!$row['Answer']) {
        mysqli_free_result($result);
        $finished = 'false';
        echo $finished;
    }
}

mysqli_free_result($result);

echo $finished;

// $players = [];

// if (mysqli_num_rows($result) > 0) {
//     //Store output of each row into array.
//     while($row = mysqli_fetch_assoc($result)) {
//         $players[] = $row['Name'];
//     }
//     mysqli_free_result($result);
// }

// if ($players) {
//     echo 'Players: ' . implode(', ', $players);
// } else {
//     echo 'Players: 0 found';
// }

?>
