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

// Sets game room code in a session so it can be accessed from multiple files.
$_SESSION['game_roomcode'] = $random_code;

echo $_SESSION['game_roomcode'];

$sql = "INSERT INTO wp_game (DateCreated, ID, RoomCode, GameTitle) VALUES ('$date', '$post_id', '$random_code', '$post_title')";

if (mysqli_query($link, $sql)) {
    $last_id = mysqli_insert_id($link);
} else {
    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
}
$_SESSION['id'] = $last_id;

mysqli_close($link);

?>


<div id="wrapper">
    <div id="menu">
        <p id="welcome">Players:</p>
        <button id="show-players">Update Players</button>
        <div style="clear:both"></div>
    </div>

    <section class="quiz <?=$post_title?>">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1><?=$post_title?></h1>
                </div>
                <?php foreach ($quiz_rounds as $quiz_round) : ?>
                    <?php
                        $round_title    = $quiz_round['round_title'];
                        $qcount         = 1;
                        $questions      = $quiz_round['questions'];
                    ?>
                    <div class="col-12 quiz-round">
                        <h2><?=$round_title?></h2>
                    </div>
                    <?php foreach ($questions as $question) : ?>
                        <?php
                            $question_type = $question['question_type'];
                            $question_text = $question['question_text'];
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
                                $i = 0;
                                $alpha = ['a', 'b', 'c', 'd', 'e', 'f'];

                            ?>
                            <div class="col-12 question-<?=$question_type?>">
                                <h3>Question <?=$qcount?>. <?=$question_text?></h3>

                                <?php foreach ($answers as $answer) : ?>
                                    <?php
                                        if ($count == 1) {
                                            $answer = $answers[1];
                                        } else {
                                            $answer = implode(array_splice($answers, rand(0, $count), 1));
                                        }
                                    ?>
                                    <input type="radio" id="answer-<?=$alpha[$i]?>" name="round-<?=rcount?>-question-<?=qcount?>" value="<?=$answer?>">
                                    <label for="answer-<?=$alpha[$i]?>"><?=$answer?></label>
                                    <?php
                                        $count--;
                                        $i++;
                                    ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php $qcount++; ?>
                    <?php endforeach; ?>
                    <?php $rcount++; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<?php

get_footer();

?>
