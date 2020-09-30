<?php
session_start();

$quiz_rounds        = get_field('quiz_rounds');
$rcount             = 1;
$date               = date('dmY');

get_header();

if (have_posts()) {
    the_post();
    $post_title = get_the_title();
    $post_id    = get_the_ID();
}

$link = mysqli_connect("localhost", "root", "root", "webdev-blog");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MYSQL: " . mysqli_connect_error();
    exit();
}

$sql = "DELETE FROM wp_game WHERE DateCreated <> $date";
if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$sql = "DELETE FROM wp_players WHERE DateCreated <> $date";
if (mysqli_query($link, $sql)) {

} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}

$characters = strtoupper('0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ');
$characters_length = strlen($characters);
$random_code = "";
for ($i = 0; $i < 4; $i++) {
    $random_code .= $characters[rand(0, $characters_length - 1)];
}

$sql = "SELECT Name FROM wp_players WHERE RoomCode='".$_SESSION['game_roomcode']."'";

$result = mysqli_query($link, $sql);

$players = [];

if (mysqli_num_rows($result) > 0) {
    //Store output of each row into array.
    while($row = mysqli_fetch_assoc($result)) {
        $players[] = $row['Name'];
    }
    mysqli_free_result($result);
}

if ($players) {
    $players = implode(', ', $players);
}

$started = true;

// Sets game room code in a session so it can be accessed from multiple files.
if (!$_SESSION['game_roomcode']) {
    $_SESSION['game_roomcode'] = $random_code;
    $sql = "INSERT INTO wp_game (DateCreated, ID, RoomCode, GameTitle) VALUES ('$date', '$post_id', '$random_code', '$post_title')";
    if (mysqli_query($link, $sql)) {
        // $last_id = mysqli_insert_id($link);
    } else {
        echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
    }
    // $_SESSION['game_id'] = $last_id;
    $started = false;
}

$sql = "SELECT * FROM wp_game WHERE RoomCode = '".$_SESSION['game_roomcode']."'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$game_round = $row['Round'];
$game_q     = $row['Question'];
// Free result set
mysqli_free_result($result);
?>

<div class="container">
    <h1><?=$post_title?></h1>
</div>


<section class="quiz <?=$post_title?> <?=$started ? 'quiz-started' : ''?>">
    <p id="welcome">Players: <?=$players ? $players : ''?></p>
    <p>Room code: <?=$_SESSION['game_roomcode']?></p>
    <button id="show-players">Update Players</button>
    <button id="start-quiz">Start Quiz</button>
    <button id="start-new-quiz">Restart Quiz</button>
    <div style="clear:both"></div>
    <div class="quiz-question">
        <div class="container">
            <div class="row">
                <?php if ($game_round <= count($quiz_rounds)) : ?>
                    <?php
                        $i              = $game_round - 1;
                        $quiz_round     = $quiz_rounds[$i];
                        $round_title    = $quiz_round['round_title'];
                        $questions      = $quiz_round['questions'];
                    ?>
                    <div class="col-12 quiz-round">
                        <h2><?=$round_title?></h2>
                    </div>
                    <?php if ($game_q <= count($questions)) : ?>
                        <?php
                            $j              = $game_q - 1;
                            $question       = $questions[$j];
                            $question_type  = $question['question_type'];
                            $question_text  = $question['question_text'];
                            $points         = $question['point_value'];
                            $correct_ans    = $question['correct_answer'];

                        ?>

                        <?php if ($question_type == 'multiple') : ?>
                            <?php
                                $answers = [$question['correct_answer']];
                                foreach ($question['wrong_answers'] as $wrong_answer) {
                                    array_push($answers, $wrong_answer['wrong_answer']);
                                }



                                // $answer_one     = implode(array_splice($answers, rand(0, 3), 1));
                                // $answer_two     = implode(array_splice($answers, rand(0, 2), 1));
                                // $answer_three   = implode(array_splice($answers, rand(0, 1), 1));
                                // $answer_four    = $answers['0'];
                                $count = count($answers) - 1;
                                $x = 0;
                                $alpha = ['a', 'b', 'c', 'd', 'e', 'f'];

                            ?>
                            <div class="col-12 question-<?=$question_type?>">
                                <h3>Question <?=$game_q?>. <?=$question_text?></h3>
                                <ul>

                                <?php foreach ($answers as $answer) : ?>
                                    <?php
                                        if ($count == 1) {
                                            $answer = $answers[1];
                                        } else {
                                            $answer = implode(array_splice($answers, rand(0, $count), 1));
                                        }
                                    ?>
                                    <li class="answer"><?=$answer?></li>
                                    <?php
                                        $count--;
                                        $x++;
                                    ?>
                                <?php endforeach; ?>
                                </ul>
                                <a href="<?=site_url('results')?>" id="see-results" class="btn">See Results</a>
                                <div id="answers-checker"></div>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <span>End of Round</span>
                        <form action="" method="post">
                            <input type="submit" name="new-round" value="Next Round" />
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php

get_footer();

mysqli_close($link);

?>
