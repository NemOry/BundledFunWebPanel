<?php

require_once("../../includes/initialize.php");

global $session;

if(!$session->is_logged_in()){
    redirect_to("login.php");
}

$category = $_GET['category'];

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>BundledFun Quiz Page</title>
        <link rel="stylesheet" href="../css/jquery.mobile-1.1.1.min.css" />
        <link rel="stylesheet" href="my.css" />
        <link href="../css/jquery.toastmessage.css" rel="stylesheet"/>
        <script src="../js/jquery-1.8.2.js"></script>
        <script src="../js/jquery.mobile-1.1.1.min.js"></script>
        <script src="my.js"></script>
        <script src="../js/jquery.toastmessage.js"></script>
    </head>
    <body>
        <!-- Home -->
        <div data-role="page" data-theme="a" id="quiz_page">
            <div data-theme="a" data-role="header">
                <h3>
                    BundledFun
                </h3>
            </div>
            <div data-role="content">
                <div data-role="fieldcontain">
                    <label for="menu">
                        Menu:
                    </label>
                    <select id="menu" data-native-menu="false" name="" data-mini="true">
                        <option value="Choose">
                            Choose
                        </option>
                        <option value="Pause">
                            Pause
                        </option>
                        <option value="Resume">
                            Resume
                        </option>
                        <option value="Restart">
                            Restart
                        </option>
                        <option value="Report">
                            Report Current Question
                        </option>
                        <option value="Quit">
                            Quit
                        </option>
                        <option value="Logout">
                            Logout
                        </option>
                    </select>
                </div>

                <div data-role="navbar" data-iconpos="top">
                    <ul>
                        <li>
                            <a id="timeLeft" data-theme="a" data-icon="">
                                Timer:
                            </a>
                        </li>
                        <li>
                            <a id="score" data-theme="" data-icon="">
                                Score
                            </a>
                        </li>
                    </ul>
                </div>

                <h5 id="textQuestion" style="text-align:center"></h5>

                <div style="text-align:center">
                    <img id="imageQuestion" style="max-height:400px;" src="" />
                </div>

                <div style="text-align:center">
                    <video id="videoQuestion" loop="loop" controls="controls" poster="../images/bundledfun-header.jpg" preload="auto" style="max-height:400px;">
                        <source src="" type="video/mp4"/>
                    </video>
                </div>

                <center>
                    <audio id="audioQuestion" loop="loop" controls="controls" preload="auto">
                        <source src="" type="audio/mp3" />
                        Your browser does not support the audio tag.
                    </audio>
                </center>

                <a id="btnA" data-role="button" data-icon="check" data-iconpos="left" onclick="compare('a');">
                    A
                </a>
                <a id="btnB" data-role="button" data-icon="check" data-iconpos="left"  onclick="compare('b');">
                    B
                </a>
                <a id="btnC" data-role="button" data-icon="check" data-iconpos="left"  onclick="compare('c');">
                    C
                </a>
                <a id="btnSkip" data-role="button" data-icon="arrow-r" data-iconpos="left"  onclick="compare('skip');">
                    Skip
                </a>
                <a id="btnBack" data-role="button" data-icon="arrow-r" data-iconpos="left"  onclick="window.location.href='categories.php'">
                    Back
                </a>
            </div>
        </div>

        <audio id="correctSound" preload="auto" >
            <source src="../sounds/correct.wav" type="audio/mp3" />
        </audio>

        <audio id="wrongSound" preload="auto" >
            <source src="../sounds/wrong.wav" type="audio/mp3" />
        </audio>

        <script>

        var category = "<?php echo $_GET['category']; ?>";

        var skipMode = false;

        var timer = 0;
        var timeCounter = 0;
        var countdownTimer;

        var interval_holder = 0;
        var questions_loaded = false;

        var correctSound;
        var wrongSound;

        var currentQuestion = new Object();
        var questions = [];
        var skippedQuestions = [];

        var totalScore = 0;
        var score;
        var timeLeft;
        var correctedAnswers = 0;
        var totalTimeElapsed = 0;

        var textQuestion;
        var videoQuestion;
        var imageQuestion;
        var audioQuestion;

        var btnA;
        var btnB;
        var btnC;
        var btnSkip;

        var groupURL = "http://<?php echo $_SERVER['SERVER_NAME']; ?>/BundledFun/public/groups";
        var group_name = "<?php echo Group::get_by_id($session->user_group_id)->name; ?>";
        var group_id = "<?php echo $session->user_group_id; ?>";

        (function($) {

            $.fn.changeButtonText = function(newText) {

                return this.each(function() {

                    $this = $(this);

                    if( $this.is('a') ) {
                        $('span.ui-btn-text', $this).text(newText);
                        return;
                    }

                    if( $this.is('input') ) {
                        $this.val(newText);
                        var ctx = $this.closest('.ui-btn');
                        $('span.ui-btn-text', ctx).text(newText);
                        return;
                    }
                });
            };
        })(jQuery);

        $(document).ready(function(){

            correctSound    = document.getElementById("correctSound");
            wrongSound      = document.getElementById("wrongSound");

            textQuestion    = document.getElementById("textQuestion");
            videoQuestion   = document.getElementById("videoQuestion");
            imageQuestion   = document.getElementById("imageQuestion");
            audioQuestion   = document.getElementById("audioQuestion");

            btnA            = document.getElementById("btnA");
            btnB            = document.getElementById("btnB");
            btnC            = document.getElementById("btnC");
            btnSkip         = document.getElementById("btnSkip");

            score           = document.getElementById("score");
            timeLeft        = document.getElementById("timeLeft");

            $('#menu').change(function() 
            {
                var option = $("#menu option:selected").text();

                if(option.indexOf("Restart") != -1)
                {
                    restart();
                }
                else if(option.indexOf("Pause") != -1)
                {
                    pause();
                }
                else if(option.indexOf("Resume") != -1)
                {
                    resume();
                }
                else if(option.indexOf("Report") != -1)
                {
                    window.location.href = "report.php?question="+currentQuestion.text+"&answer="+currentQuestion.answer;
                }
                else if(option.indexOf("Quit") != -1)
                {
                    window.location.href = "categories.php";
                }
                else if(option.indexOf("Logout") != -1)
                {
                    window.location.href = "../logout.php";
                }
            });

            $.getJSON('../../includes/jsons/get_questions.php?group_id=' + group_id, function(data)
            {
                $.each(data, function(key, val)
                {
                    var question = new Object();
                    question.id = val.id;
                    question.group_id = val.group_id;
                    question.text = val.text;
                    question.difficulty = val.difficulty;
                    question.answer = val.answer;
                    question.choice_a = val.choice_a;
                    question.choice_b = val.choice_b;
                    question.choice_c = val.choice_c;
                    question.file = val.file;
                    question.type = val.type;
                    question.points = val.points;
                    question.timer = val.timer;

                    if(category == "mixed")
                    {
                        questions.push(question);
                    }
                    else
                    {
                        if(val.difficulty == category)
                        {
                            questions.push(question);
                        }
                    }
                });

                questions_loaded = true;
            });
        });

        interval_holder = window.setInterval(function ()
        {
            if (questions_loaded)
            {
                clearInterval(interval_holder);

                if(questions.length <= 2)
                {
                    $(btnSkip).addClass('ui-disabled');
                }

                if(confirm("Start Quiz?"))
                {
                    start();
                }
                else
                {
                    window.location.href = "../index.php";
                }
            }
        }, 50);

        function randomize ( myArray )
        {
          var i = myArray.length;
          if ( i == 0 ) return false;
          while ( --i )
          {
             var j = Math.floor( Math.random() * ( i + 1 ) );
             var tempi = myArray[i];
             var tempj = myArray[j];
             myArray[i] = tempj;
             myArray[j] = tempi;
           }
        }

        function setupTimer()
        {
            countdownTimer = window.setInterval(function()
            {
                totalTimeElapsed++;

                $("#timeLeft").changeButtonText("Time Left: " + (timer - timeCounter));

                timeCounter++;

                if((timeCounter - 1) == timer)
                {
                    nextQuestion();
                }

            } , 1000);
        }

        function start()
        {
            console.log("questions: " + questions.length + ", skippedQuestions: " + skippedQuestions.length);

            if(questions.length > 0)
            {
                randomize(questions);
                currentQuestion = questions[0];

                if(questions.length <= 2)
                {
                    $(btnSkip).addClass('ui-disabled');
                }

                displayQuestion();
                setupTimer();
            }
            else
            {
                alert("there are no available questions for this category");
                window.location.href = "categories.php";
                return;
            }
        }

        function restart()
        {
            window.location.reload();
        }

        function pause()
        {
            $(btnA).addClass('ui-disabled');
            $(btnB).addClass('ui-disabled');
            $(btnC).addClass('ui-disabled');
            $(btnSkip).addClass('ui-disabled');
            window.clearInterval(countdownTimer);
            videoQuestion.pause();
            audioQuestion.pause();
        }

        function resume()
        {
            $(btnA).removeClass('ui-disabled');
            $(btnB).removeClass('ui-disabled');
            $(btnC).removeClass('ui-disabled');
            $(btnSkip).removeClass('ui-disabled');
            setupTimer();
            videoQuestion.play();
            audioQuestion.play();
        }

        function finish()
        {
            clearInterval(countdownTimer);
            var save = confirm("Quiz Finished.\n\nsave score?");
            if(save)
            {
                $.ajax({
                    type:"GET",
                    url:"../../includes/jsons/save_score.php",
                    data: { user_id: "<?php echo $session->user_id ?>", user_access_token: "<?php echo User::get_by_id($session->user_id)->access_token ?>", score: totalScore, correct_answers : correctedAnswers, time_elapsed : totalTimeElapsed},
                    success: function(result)
                    {
                        if(result.length > 5)
                        {
                            alert("saved");
                            window.location.href = "categories.php";
                        }
                        else
                        {
                            alert(result);
                            window.location.href = "categories.php";
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        alert("error");
                        window.location.href = "categories.php";
                    }
                });

                return false;
            }
            else
            {
                window.location.href = "categories.php";
            }
        }

        function displayQuestion()
        {
            videoQuestion.pause();
            audioQuestion.pause();
            
            $("#imageQuestion").hide();
            $("#videoQuestion").hide();
            $("#audioQuestion").hide();

            textQuestion.innerHTML = currentQuestion.text;

            $("#btnA").changeButtonText(currentQuestion.choice_a);
            $("#btnB").changeButtonText(currentQuestion.choice_b);
            $("#btnC").changeButtonText(currentQuestion.choice_c);

            var question_file = groupURL + "/" + group_name + "/files/questions/"  + currentQuestion.file;

            if(currentQuestion.type == "video")
            {
                videoQuestion.src = question_file;
                videoQuestion.load();
                videoQuestion.play();
                $("#videoQuestion").show();
            }
            else if(currentQuestion.type == "image")
            {
                imageQuestion.src = question_file;
                 $("#imageQuestion").show();
            }
            else if(currentQuestion.type == "audio")
            {
                audioQuestion.src = question_file;
                audioQuestion.load();
                audioQuestion.play();
                $("#audioQuestion").show();
            }

            timer = currentQuestion.timer;
            timeCounter = 0;
        }

        function nextQuestion(skip)
        {
            if(skipMode)
            {
                if(skippedQuestions.length > 0)
                {
                    randomize(skippedQuestions);
                    currentQuestion = skippedQuestions[0];
                }
                else
                {
                    finish();
                    return;
                }
            }
            else
            {
                randomize(questions);
                currentQuestion = questions[0];
            }

            displayQuestion();
        }

        function compare(btn)
        {
            if(btn == "a")
            {
                if($("#btnA .ui-btn-text").text() == currentQuestion.answer)
                {
                    totalScore = parseInt(totalScore) + parseInt(currentQuestion.points);
                    correctedAnswers++;
                    showCorrect();
                }
                else
                {
                    showWrong();
                }
            }
            else if(btn == "b")
            {
                if($("#btnB .ui-btn-text").text() == currentQuestion.answer)
                {
                   totalScore = parseInt(totalScore) + parseInt(currentQuestion.points);
                   correctedAnswers++;
                   showCorrect();
                }
                else
                {
                    showWrong();
                }
            }
            else if(btn == "c")
            {
                if($("#btnC .ui-btn-text").text() == currentQuestion.answer)
                {
                    totalScore = parseInt(totalScore) + parseInt(currentQuestion.points);
                    correctedAnswers++;
                    showCorrect();
                }
                else
                {
                    showWrong();
                }
            }
            else if(btn == "skip")
            {
                skippedQuestions.push(currentQuestion);
            }

            $("#score").changeButtonText("Score: " + totalScore);

            if(skipMode)
            {
                if(skippedQuestions.length > 0)
                {
                    skippedQuestions.shift();
                }
            }
            else
            {
                questions.shift();
            }

            if(questions.length <= 2)
            {
                $(btnSkip).addClass('ui-disabled');
            }

            if(questions.length == 0 && skippedQuestions.length > 0) // there are more questions
            {
                skipMode = true;
            }

            if(questions.length == 0 && skippedQuestions.length == 0)
            {
                finish();
                return;
            }

            nextQuestion();
        }

        function showWrong()
        {
            wrongSound.play();
            $().toastmessage('showToast', {
                text     : 'Wrong. The correct answer is: ' + currentQuestion.answer,
                sticky   : false,
                type     : 'error',
                stayTime : 3000,
            });
        }

        function showCorrect()
        {
            correctSound.play();
            $().toastmessage('showToast', {
                text     : 'Correct.',
                sticky   : false,
                type     : 'success',
                stayTime : 1000,
            });
        }

        </script>
    </body>
</html>