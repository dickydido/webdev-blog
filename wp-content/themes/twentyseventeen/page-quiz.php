<?php

    session_start();

    get_header();
    //include_once("/connect-to-db.php");

    $link = mysqli_connect("localhost", "root", "root", "webdev-blog");

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MYSQL: " . mysqli_connect_error();
        exit();
    }

    if (!$_SESSION['gamer-name']) {
        if(isset($_POST['enter'])){
            $sql = "SELECT * FROM wp_players WHERE RoomCode = '".$_POST['roomcode']."' AND Name = '".$_POST['gamer-name']."'";
            $result = mysqli_query($link, $sql);
            if ($_POST['gamer-name'] == "" && $_POST['roomcode'] == "") {
                echo '<span class="error">Please enter a room code and your name.</span>';
            } elseif ($_POST['gamer-name'] == "") {
                echo '<span class="error">Please enter your name.</span>';
            } elseif ($_POST['roomcode'] == "") {
                echo '<span class="error">Please enter a room code.</span>';
            } elseif (!isset($_POST['id']) && mysqli_num_rows($result) != 0) {
                echo '<span class="error">Someone with this name is already in this game, please enter a different name.</span>';
                // Free result set
                mysqli_free_result($result);
            } else {
                // Free result set
                mysqli_free_result($result);
                $sql = "SELECT RoomCode FROM wp_game";
                $result = mysqli_query($link, $sql);
                $all_codes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                foreach ($all_codes as $this_code) {
                    if ($this_code["RoomCode"] == strtoupper($_POST['roomcode'])) {
                        $_SESSION['gamer-name'] = stripslashes(htmlspecialchars($_POST['gamer-name']));
                        $_SESSION['roomcode'] = stripslashes(htmlspecialchars(strtoupper($_POST['roomcode'])));
                    }
                }
                if (!$_SESSION['gamer-name']) {
                    echo '<span class="error">Invalid Room Code.</span>';
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
    }

    function loginForm(){

        echo '
        <div id="loginform">
            <form action="" method="post">
                <label for="gamer-name">Name:</label>
                <input type="text" name="gamer-name" id="gamer-name" />
                <label for="roomcode">Room Code:</label>
                <input type="text" name="roomcode" id="roomcode" />
                <input type="submit" name="enter" id="enter" value="Enter" />
            </form>
        </div>
        ';
    }

    $sql = "SELECT * FROM wp_players WHERE RoomCode = '".$_SESSION['roomcode']."' AND Name = '".$_SESSION['gamer-name']."'";
    $result = mysqli_query($link, $sql);
    if (!$_SESSION['gamer-name']) {
        mysqli_free_result($result);
        loginform();
    } else {
        mysqli_free_result($result);
        $date = date('dmY');
        if (!$_SESSION['id']) {
            $sql = "INSERT INTO wp_players (DateCreated, Name, RoomCode) VALUES ($date, '".$_SESSION['gamer-name']."', '".$_SESSION['roomcode']."')";
            if (mysqli_query($link, $sql)) {
                $last_id = mysqli_insert_id($link);
            } else {
                echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
            }
            $_SESSION['id'] = $last_id;
        }

        // echo $_SESSION['id'];

        $sql = "SELECT ID FROM wp_game WHERE RoomCode = '".$_SESSION['roomcode']."'";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $game_id = $row['ID'];
        // Free result set
        mysqli_free_result($result);

        $post = get_post($game_id);
        $quiz_rounds        = get_field('quiz_rounds', $game_id);
        $sql = "SELECT * FROM wp_game WHERE RoomCode = '".$_SESSION['roomcode']."'";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $game_round = $row['Round'];
        $game_q     = $row['Question'];
        // Free result set
        mysqli_free_result($result);

        ?>
        <section class="quiz <?=$post->post_name?>">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h4>Welcome, <?=$_SESSION['gamer-name']?></h4>
                    </div>

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

                            if(isset($_POST['round-'.$game_round.'-question-'.$game_q.'-submit'])){
                                $sql = "UPDATE wp_players SET Answer='".$_POST['answer']."' WHERE ID='".$_SESSION['id']."'";
                                if (mysqli_query($link, $sql)) {
                                    echo "Answer Submitted - " . $_POST['answer'];
                                } else {
                                    echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                                }
                            } else { ?>
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
                                        <h3>Question <?=$game_q?>.</h3>
                                        <form action="" method="post">

                                        <?php foreach ($answers as $answer) : ?>
                                                <?php
                                                    if ($count == 1) {
                                                        $answer = $answers[1];
                                                    } else {
                                                        $answer = implode(array_splice($answers, rand(0, $count), 1));
                                                    }
                                                ?>
                                                <input type="radio" id="round-<?=$game_round?>-question-<?=$game_q?>-answer-<?=$alpha[$x]?>" name="answer" value="<?=$answer?>">
                                                <label for="answer-<?=$alpha[$x]?>"><?=$answer?></label>
                                                <?php
                                                    $count--;
                                                    $x++;
                                                ?>
                                            <?php endforeach; ?>
                                            <input type="submit" name="round-<?=$game_round?>-question-<?=$game_q?>-submit" id="roundanswer-<?=$game_q?>" value="Submit Answer" />
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php } ?>
                        <?php else : ?>
                            <span>End of Round</span>
                            <form action="" method="post">
                                <input type="submit" name="new-round" value="Next Round" />
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    <?php
    }
    ?>

<?php

mysqli_close($link);

get_footer();

?>
