<?php

session_start();

if ($_SESSION['site'] == 'local') {
    $link = mysqli_connect("localhost", "root", "root", "webdev-blog");
} else {
    $link = mysqli_connect("grh27", "richie_wp1", "S.WBkXfYYziuElP7lmB06", "richie_wp1");
}


if (mysqli_connect_errno()) {
    echo "Failed to connect to MYSQL: " . mysqli_connect_error();
    exit();
}

$sql = "SELECT * FROM wp_players WHERE RoomCode='".$_SESSION['game_roomcode']."'";

if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) != 0) {
    $player_info = mysqli_fetch_assoc($result);
} else {
    echo '<span class="error">No results found</span>';
}

$finished = 'true';

if (!$player_info['Answer']) {
    echo $finished;
} else {
    $finished = 'true';
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
