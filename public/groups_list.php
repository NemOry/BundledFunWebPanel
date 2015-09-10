<?php

require_once("../includes/initialize.php");

global $session;

if($session->is_logged_in())
{
    if($session->user_level > 1)
    {
        redirect_to("categories.php");
    }
    else
    {
        redirect_to("app.php");
    }
}

$groups = Group::get_all();

?>
<!DOCTYPE HTML>
<html>
<head>

    <meta charset="UTF-8" />
    <meta name="Oliver Martinez" content="BundledFun" />
    <title>BundledFun</title>

    <link href="css/fonts.css" rel="stylesheet"/>
    <link href="css/south-street/jquery-ui-1.9.0.custom.css" rel="stylesheet" media="screen" />
    <style>
        #dialog-login-form, #dialog-group-reg-form { font-size: 62.5%; }
        label, input { display:block; color: gray;}
        input.text { margin-bottom:12px; width:95%; padding: .4em; }
        fieldset { padding:0; border:0; margin-top:25px; }
        h1 { margin: .6em 0; }
        .ui-dialog .ui-state-error { padding: .3em; }
        .validateTips { border: 1px solid transparent; padding: 0.3em; color: gray;}
        /* This imageless css button was generated by CSSButtonGenerator.com */
    </style>
    <style>

    body{
        width: 100%;
        display: -webkit-box;
        -webkit-box-pack: center;
        background-color: #F2F2F2;
    }

    #container
    {
        max-width: 960px;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-box-pack: center;
    }

    #top
    {
        display: -webkit-box;
        -webkit-box-orient: horizontal;
        -webkit-box-pack: center;
        background-color: #383838;
        padding: 20px;
        color: white;
        font-family: "RobotoThin";
        border: 1px solid black;
    }

    #middle
    {
        display: -webkit-box;
        -webkit-box-pack: center;
    }

    #txt_search
    {
        width: 500px;   
        font-size: 15px;
        padding: 5px;
    }

    #lbl_search
    {
        font-size: 19px;
    }
        
    table
    {
        padding: 20px;
        width: 80%;
        font-family: "RobotoRegular";
    }

    table thead
    {
        font-family: "RobotoThin";
        font-size: 30px;
    }

    table thead tr
    {

    }

    table thead tr th
    {
        padding: 10px;
    }

    table tbody
    {

    }

    table tbody tr
    {
        margin: 0px;
    }

    table tbody tr td
    {
        text-align: center;
        padding: 30px;
        border-bottom: 1px solid gray;
        margin: 0px;
    }

    .click-button {
        -moz-box-shadow:inset 0px 1px 0px -18px #caefab;
        -webkit-box-shadow:inset 0px 1px 0px -18px #caefab;
        box-shadow:inset 0px 1px 0px -18px #caefab;
        background-color:#b9e356;
        -moz-border-radius:42px;
        -webkit-border-radius:42px;
        border-radius:42px;
        border:1px solid #268a16;
        display:inline-block;
        color:#ffffff;
        font-family:arial;
        font-size:15px;
        font-weight:bold;
        padding:6px 24px;
        text-decoration:none;
        text-shadow:1px 1px 0px #aade7c;
    }.click-button:hover {
        background-color:#a5cc52;
    }.click-button:active {
        position:relative;
        top:1px;
    }

    </style>
</head>

<body>

    <section = "container">

        <section id="top">
            <label id="lbl_search">Search</label>
            <input id="txt_search" type="text" placeholder="group name, organization, school etc.."/>
            <button id="btn_search" class="click-button">Q</button>
        </section>

        <section id="middle">
            <table>
                <tbody>
                    <?php foreach ($groups as $group) { 

                        if($group->id == 0) continue;

                    ?>
                    <tr>
                        <td class="group_banner"><img src="<?php echo 'images/bundled_fun_logo.png'; ?>" height="100" /></td>
                        <td class="group_name"><?php echo $group->name; ?></td>
                        <td class="group_description"><?php echo $group->description; ?></td>
                        <td class="group_button"><a href="#" onclick="register(<?php echo $group->id; ?>);" class="click-button">Register</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </section>

    <div id="dialog-register-to-group" title="Register to this group">
        <p class="validateTips">All form fields are required.</p>
        <form id="registerToGroup">
            <fieldset>
                <input type="hidden" name="group_id" id="group_id"/>
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="a-z and 0-9 characters only" class="text ui-widget-content ui-corner-all" />
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="choose a secure password" value="" class="text ui-widget-content ui-corner-all" />
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="youremail@mailprovider.com" value="" class="text ui-widget-content ui-corner-all" />
            </fieldset>
        </form>
    </div>

    <script src="js/jquery-1.8.2.js"></script>
    <script src="js/jquery-ui-1.9.0.custom.js"></script>
    <script>

    function register(id)
    {
        $("#group_id").val(id);
        $("#dialog-register-to-group").dialog("open");
    }

    function updateTips( t ) 
    {
        tips.text( t ).addClass( "ui-state-highlight" );
        setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    function checkLength( o, n, min, max ) 
    {
        if ( o.val().length > max || o.val().length < min )
        {
            o.addClass( "ui-state-error" );
            updateTips( "Length of " + n + " must be between " +
                min + " and " + max + "." );
            return false;
        }
        else
        {
            return true;
        }
    }

    function checkRegexp( o, regexp, n ) 
    {
        if ( !( regexp.test( o.val() ) ) ) 
        {
            o.addClass( "ui-state-error" );
            updateTips( n );
            return false;
        } 
        else 
        {
            return true;
        }
    }

    $( "#dialog-register-to-group" ).dialog({
        autoOpen: false,
        height: 550,
        width: 400,
        modal: true,
        buttons: 
        {
            "Register": function()
            {
                $.ajax({
                    type:"GET",
                    url:"register.php",
                    data: $('#registerToGroup').serialize(),
                    success: function(result){
                        if(result == "success")
                        {
                            window.location.href = "mobile_website/categories.php";
                        }
                        else
                        {
                            alert(result);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        alert("error");
                    }
                });

                return false;
            },
            Cancel: function()
            {
                $( this ).dialog( "close" );
            }
        },
        close: function()
        {
            allFields.val( "" ).removeClass( "ui-state-error" );
        }
    });

    </script>

</body>
</html>