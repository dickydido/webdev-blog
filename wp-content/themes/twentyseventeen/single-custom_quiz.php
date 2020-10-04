<?php
session_start();

$quiz_rounds        = get_field('quiz_rounds');
$rcount             = 1;
$date               = date('dmY');

if ($_SESSION['restart']) {
    unset($_SESSION['restart']);
}

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


// Sets game info in a session so it can be accessed from multiple files, then sends info to the database.
if (!$_SESSION['game_roomcode'] || $_SESSION['game_title'] != $post_title) {
    $_SESSION['game_roomcode'] = $random_code;
    $_SESSION['game_title'] = $post_title;
    $sql = "INSERT INTO wp_game (DateCreated, ID, RoomCode, GameTitle) VALUES ('$date', '$post_id', '$random_code', '$post_title')";
    if (mysqli_query($link, $sql)) {

    } else {
        echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
    }
} else {
    $started = true;
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

?>

<div class="container">
    <h1><?=$post_title?></h1>
</div>


<section class="quiz <?=$post_title?> <?=$started ? 'quiz-started' : ''?>">
    <p id="welcome">Players: <?=$players ? $players : ''?></p>
    <p>Room code: <?=$_SESSION['game_roomcode']?></p>
    <button id="show-players">Update Players</button>
    <button id="start-quiz">Start Quiz</button>
    <button id="start-new-quiz" class="restart">Restart Quiz</button>
    <div id="answers-checker"></div>
    <div style="clear:both"></div>
    <div class="quiz-question">
        <div class="container">
            <div class="row">
                <?php
                $sql = "SELECT * FROM wp_game WHERE RoomCode = '".$_SESSION['game_roomcode']."'";
                $result = mysqli_query($link, $sql);
                if (mysqli_num_rows($result) != 0) {
                    $game_info      = mysqli_fetch_assoc($result);
                    $game_round     = $game_info['Round'];
                    $game_q         = $game_info['Question'];
                    $i              = $game_round - 1;
                    $quiz_round     = $quiz_rounds[$i];
                    $round_title    = $quiz_round['round_title'];
                    $questions      = $quiz_round['questions'];
                } else {
                    echo '<p class="error">This game has expired. Please restart.</p>';
                }
                // Free result set
                mysqli_free_result($result);
                if (isset($_SESSION['update_question'])) {
                    unset($_SESSION['update_question']);
                    if ($game_q == count($questions)) {
                        if ($game_round != count($quiz_rounds)) {
                            $game_round++;
                            $i++;
                            $game_q = 1;

                            $sql = "UPDATE wp_game SET Round='".$game_round."', Question='".$game_q."' WHERE RoomCode='".$_SESSION['game_roomcode']."'";
                            if (mysqli_query($link, $sql)) {

                            } else {
                                echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                            }
                        }
                    } else {
                        $game_q++;

                        $sql = "UPDATE wp_game SET Question='".$game_q."' WHERE RoomCode='".$_SESSION['game_roomcode']."'";
                        if (mysqli_query($link, $sql)) {

                        } else {
                            echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                        }

                        if ($game_q == count($questions)) {
                            // Set a session to end the game after the final question.
                            if ($game_round == count($quiz_rounds)) {
                                $_SESSION['end_game'] = true;
                                $sql = "UPDATE wp_game SET GameEnd='Ended' WHERE RoomCode='".$_SESSION['game_roomcode']."'";
                                if (mysqli_query($link, $sql)) {

                                } else {
                                    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                                }
                            }
                        }
                    }
                }

                // Redeclare variables that may have been updated in the above conditional.
                $quiz_round     = $quiz_rounds[$i];
                $round_title    = $quiz_round['round_title'];
                $questions      = $quiz_round['questions'];
                ?>
                <div class="col-12 quiz-round">
                    <h2>Round <?=$game_round?>. <?=$round_title ? $round_title : ''?></h2>
                </div>
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
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php

get_footer();

mysqli_close($link);

?>
