<?php

    session_start();

    get_header();
    //include_once("/connect-to-db.php");

    if (site_url() == 'http://localhost:8888/webdev-blog') {
        $link = mysqli_connect("localhost", "root", "root", "webdev-blog");
    } else {
        $link = mysqli_connect("grh27", "richie_wp1", "S.WBkXfYYziuElP7lmB06", "richie_wp1");
    }

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MYSQL: " . mysqli_connect_error();
        exit();
    }

    // Remove player database and $_POST data if they hit the restart button.
    if (isset($_SESSION['restart'])) {
        unset($_SESSION['restart']);
        if ($_POST['enter']) {
            $sql = "DELETE FROM wp_players WHERE Name='".$_POST['gamer-name']."' AND RoomCode='".$_POST['roomcode']."'";
            if (mysqli_query($link, $sql)) {

            } else {
                echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
            }

            $_POST = array();
        }
    }

    if (!isset($_SESSION['gamer_name'])) {
        if(isset($_POST['enter'])){
            $sql = "SELECT * FROM wp_players WHERE RoomCode = '".$_POST['roomcode']."' AND Name = '".$_POST['gamer-name']."'";
            $result = mysqli_query($link, $sql);
            if ($_POST['gamer-name'] == "" && $_POST['roomcode'] == "") {
                echo '<span class="error">Please enter a room code and your name.</span>';
            } elseif ($_POST['gamer-name'] == "") {
                echo '<span class="error">Please enter your name.</span>';
            } elseif ($_POST['roomcode'] == "") {
                echo '<span class="error">Please enter a room code.</span>';
            } elseif (mysqli_num_rows($result) != 0) {
                echo '<span class="error">Someone with this name is already in this game, please enter a different name.</span>';
                // Free result set
                mysqli_free_result($result);
            } else {
                // Free result set
                mysqli_free_result($result);
                $sql = "SELECT * FROM wp_game WHERE RoomCode='".$_POST['roomcode']."'";
                $result = mysqli_query($link, $sql);
                if (mysqli_num_rows($result) != 0) {
                    $_SESSION['gamer_name'] = stripslashes(htmlspecialchars($_POST['gamer-name']));
                    $_SESSION['roomcode'] = stripslashes(htmlspecialchars(strtoupper($_POST['roomcode'])));
                } else {
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

    // $sql = "SELECT * FROM wp_players WHERE RoomCode = '".$_SESSION['roomcode']."' AND Name = '".$_SESSION['gamer_name']."'";
    // $result = mysqli_query($link, $sql);

    if (!$_SESSION['gamer_name']) {
        // mysqli_free_result($result);
        loginform();
    } else {
        // mysqli_free_result($result);
        $date = date('dmY');
        if (!$_SESSION['id']) {
            $sql = "INSERT INTO wp_players (DateCreated, Name, RoomCode) VALUES ($date, '".$_SESSION['gamer_name']."', '".$_SESSION['roomcode']."')";
            if (mysqli_query($link, $sql)) {
                $last_id = mysqli_insert_id($link);
            } else {
                echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
            }
            $_SESSION['id'] = $last_id;
        }

        $sql = "SELECT * FROM wp_game WHERE RoomCode = '".$_SESSION['roomcode']."'";
        if (mysqli_query($link, $sql)) {
            $result = mysqli_query($link, $sql);
            $game_info = mysqli_fetch_assoc($result);

            // Free result set
            mysqli_free_result($result);
        } else {
            echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
        }


        $sql = "SELECT * FROM wp_players WHERE ID = '".$_SESSION['id']."'";
        if (mysqli_query($link, $sql)) {
            $result = mysqli_query($link, $sql);
            $player_info = mysqli_fetch_assoc($result);

            mysqli_free_result($result);
        } else {
            echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
        }

        $game_id = $game_info['ID'];
        $post = get_post($game_id);

        ?>
        <section class="quiz <?=$post->post_name?>">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h4>Welcome, <?=$_SESSION['gamer_name']?></h4>
                        <button class="restart"><?=($game_info['GameEnd'] == 'Ended') ? 'Play Again?' : 'Restart'?></button>
                    </div>

                <?php if ($player_info['Round'] != $game_info['Round'] || $player_info['Question'] != $game_info['Question']) : ?>
                    <?php
                        $sql = "UPDATE wp_players SET Question='".$game_info['Question']."', Round='".$game_info['Round']."' WHERE ID='".$_SESSION['id']."'";
                        if (mysqli_query($link, $sql)) {
                            unset($_SESSION['next_question']);
                        } else {
                            echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                        }
                    ?>
                <?php elseif (isset($_SESSION['next_question'])) : ?>
                    <?php if ($game_info['GameEnd'] == 'Ended') : ?>
                        <h4>No more questions. Look at the host's screen to see the final results!</h4>
                    <?php else : ?>
                        <p class="error">You can't go to the next question until the host is ready.</p>
                        <form method="post">
                            <input type="submit" name="next-question" class="player-next-question btn" value="Next Question" />
                        </form>
                    <?php endif; ?>
                <?php elseif (isset($_POST['answer'])) : ?>
                    <?php
                        $sql = "UPDATE wp_players SET Answer='".$_POST['answer']."' WHERE ID='".$_SESSION['id']."'";
                        if (mysqli_query($link, $sql)) {
                            echo "Answer Submitted - " . $_POST['answer'];
                            $_SESSION['next_question'] = true;
                        } else {
                            echo "\nError: ". $sql . "<br>" . mysqli_error($link) . "\n";
                        }
                    ?>
                    <?php if ($game_info['GameEnd'] == 'Ended') : ?>
                        <h4>No more questions. Look at the host's screen to see the final results!</h4>
                    <?php else : ?>
                        <p>Note: You won't be able to go to the next question until the host is ready.</p>
                        <form method="post">
                            <input type="submit" name="next-question" class="player-next-question btn" value="Next Question" />
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php
                    $quiz_rounds    = get_field('quiz_rounds', $game_id);
                    $game_round     = $game_info['Round'];
                    $game_q         = $game_info['Question'];
                    $i              = $game_round - 1;
                    $quiz_round     = $quiz_rounds[$i];
                    $round_title    = $quiz_round['round_title'];
                    $questions      = $quiz_round['questions'];
                ?>
                <div class="col-12 quiz-round<?=$_SESSION['next_question'] ? ' display-none' : ''?>">
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
                    <div class="col-12 question-<?=$question_type?><?=$_SESSION['next_question'] ? ' display-none' : ''?>">
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
                            <input type="submit" name="submit-answer" id="roundanswer-<?=$game_q?>" value="Submit Answer" />
                        </form>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </section>
        <div id="ajax-receiver"></div>

    <?php
    }
    ?>

<?php

mysqli_close($link);

get_footer();

?>
