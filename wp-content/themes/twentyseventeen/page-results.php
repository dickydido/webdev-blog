<?php
session_start();

get_header();

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

$game_id = $game_info['ID'];
$post = get_post($game_id);
$quiz_rounds    = get_field('quiz_rounds', $game_id);
$game_round     = $game_info['Round'];
$game_q         = $game_info['Question'];
$i              = $game_round - 1;
$quiz_round     = $quiz_rounds[$i];
$questions      = $quiz_round['questions'];
$j              = $game_q - 1;
$question       = $questions[$j];
$question_text  = $question['question_text'];
$points         = $question['point_value'];
$correct_ans    = $question['correct_answer'];


$sql = "SELECT * FROM wp_players WHERE RoomCode='".$_SESSION['game_roomcode']."'";

if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$result = mysqli_query($link, $sql);
$players_info = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_free_result($result);


?>

<div class="container">
    <p>Room code: <?=$_SESSION['game_roomcode']?></p>
    <table class="results">
        <caption style="caption-side: top; text-align: center; background: #fff;">Question <?=$game_q?>. <?=$question_text?></caption>
        <thead>
            <tr>
                <th>Player</th>
                <th>Answer</th>
                <th>Points</th>
                <th>Total Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($players_info as $player_info) : ?>
                <?php
                    // Add the correct number of points to any correct answers and insert the total into the database.
                    if (!$_SESSION['score_updated'] && $player_info['Answer'] == $correct_ans) {
                        // Stop the score from being updated every page refresh.
                        $_SESSION['score_updated'] = true;

                        $player_info['Score'] += $points;

                        // Update total score in the database.
                        $sql = "UPDATE wp_players SET Score='".$player_info['Score']."' WHERE ID='".$player_info['ID']."'";
                        if (mysqli_query($link, $sql)) {

                        } else {
                            echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                        }
                    }
                ?>
                <tr class="<?=($player_info['Answer'] == $correct_ans) ? 'correct' : 'incorrect'?>">
                    <td><?=$player_info['Name']?></td>
                    <td><?=$player_info['Answer']?></td>
                    <td><?=($player_info['Answer'] == $correct_ans) ? $points :'0'?></td>
                    <td><?=$player_info['Score']?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="<?=get_permalink($game_id)?>" id="next-question" class="btn">Next Question</a>
    <div id="next-ajax" class="display-none"></div>
</div>

<?php

get_footer();

?>
