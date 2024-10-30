<!-- Place the title of the page -->
<div class='censorreact-plugin-page'>
    <div class='censorreact-plugin-logo-div'>
        <img class='censorreact-plugin-logo'  src= '<?= CENSORREACT_PLUGIN_URL ?>assets/images/censorREACT-logo.png' alt='censorREACT logo'>
    </div>
    <hr class="wp-header-end"> 
<?php
global $wpdb;
$plugin_table_name = "censorreact_plugin";
$get_key_from_database = $wpdb->get_results("SELECT `key` FROM `$plugin_table_name`");

$_SESSION['signup-login'] = 'signup';

$email = '';
$password = '';
$confirmPassword = '';
$confirmationCode = '';
$terms = false;

//Check if the form 'login-user' is set
if(isset($_POST['censorreact-login-user'])) {
    //Set the variables to the output of the form
    $email = trim(sanitize_email(strtolower($_POST['censorreact-login-email'])));
    $password = trim($_POST['censorreact-login-password']);

    //Due to sanitizing the string returns as empty if the email is invalid
    //Check if the email is valid otherwise produce an error
    if(strlen($email) === 0) {
        print('<div class="notice-error notice"><p>That isn\'t a valid email.</p></div>');
        return false;
    }

    //Check if password is a valid length
    if(strlen($password) < 8) {
        print('<div class="notice-error notice"><p>That isn\'t a valid password.</p></div>');
        return false;
    }

    censorreact_login_user($email, $password);
}

//Check if the form 'register-user' is set
if(isset($_POST['censorreact-register-user'])) {
    //Set the variables to the output of the form
    $email = trim(sanitize_email(strtolower($_POST['censorreact-register-email'])));
    $password = trim($_POST['censorreact-register-password']);

    //Due to sanitizing the string returns as empty if the email is invalid
    //Check if the email is valid otherwise produce an error
    if(strlen($email) === 0) {
        print('<div class="notice-error notice"><p>You need to enter a valid email.</p></div>');
        return false;
    }

    //Check if password is a valid length
    if(strlen($password) < 8) {
        print('<div class="notice-error notice"><p>You need to enter a valid password.</p></div>');
        return false;
    }

    if(isset($_POST['censorreact-terms'])) {
        $terms = true;
    } else {
        return false;
    }

    //Check that the confirm password field is set otherwise set it as an empty string
    if(isset($_POST['censorreact-register-password-confirm'])) {
        $confirmPassword = trim($_POST['censorreact-register-password-confirm']);
    } else {
        $confirmPassword = '';
    }

    censorreact_register_user($email, $password, $confirmPassword, $confirmationCode, $terms);
}

//Check if the form 'get-key-user' is set
if(isset($_POST['censorreact-get-key-user'])) {
    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    //Get the email from the database
    $get_user_email = $wpdb->get_results("SELECT `email` FROM `$plugin_table_name`");
    
    //Set the variable to the email inside the database already
    //escape email coming from database
    $email = trim(sanitize_email(strtolower($get_user_email[0]->email)));

    //Since it is the get key form there is only one password input so set both to the same value
    $password = trim($_POST['censorreact-get-key-password']);

    if(strlen($password) < 8) {
        print('<div class="notice-error notice"><p>You need to enter a valid password.</p></div>');
        return false;
    }

    censorreact_login_user($email, $password);
}

//Check if the form 'get-key-user' is set
if(isset($_POST['censorreact-confirm-user'])) {

    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    //Get the email from the database
    $get_user_email = $wpdb->get_results("SELECT `email` FROM `$plugin_table_name`");
    
    //Set the variable to the email inside the database already
    $email = trim(sanitize_email(strtolower($get_user_email[0]->email)));

    $confirmationCode = trim(sanitize_text_field($_POST['censorreact-confirmation-code']));
    $terms = true;

    $codeIsNum = intval($confirmationCode);

    if(!$codeIsNum) {
        print('<div class="notice-error notice"><p>You need to enter a valid confirmation code.</p></div>');
        return false;
    }
    
    censorreact_confirm_user($email, $confirmationCode);
}

if(isset($_POST['censorreact-change-email'])) {
    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    $wpdb->query("UPDATE `{$plugin_table_name}` SET `email` = '', `user_confirmed` = 'no', `confirmation_sent` = 'no' WHERE `id` = 1"); 
}

if(isset($_POST['censorreact-resend-code'])) {
    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    //Get the email from the database
    $get_user_email = $wpdb->get_results("SELECT `email` FROM `$plugin_table_name`");
    
    //Set the variable to the email inside the database already
    $email = trim(sanitize_email(strtolower($get_user_email[0]->email)));
    
    censorreact_resend_code($email);
}

function censorreact_resend_code($email) {

    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    //Get the arguments ready to send through to the API
    $args = array(
        'method' => 'POST', 
        'timeout' => 20, 
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'email' => $email,
            'resendCode' => true,
            'referrer' => 'wordpress'
        )),
    );   
    
    //Connect to the API
    $apiReturn = wp_remote_request( 'https://api.censorreact.intygrate.com/v1/register', $args );

    error_log(print_r($apiReturn, true));
    
    $apiBody = json_decode($apiReturn['body']);

    if($apiBody->data === 'code_resent') {
        print("<div class='notice-success notice'><p>" . $apiBody->message . "</p></div>");
        return;
    } 
    
    //if the code isnt success then it will display the error from the API
    print("<div class='notice-error notice'><p>" . $apiBody->message . "</p></div>");
    return;
}

function censorreact_login_user($email, $password) {

    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    //Get the arguments ready to send through to the API
    $args = array(
        'method' => 'POST', 
        'timeout' => 20, 
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'email' => $email, 
            'password' => $password,
            'referrer' => 'wordpress'
        )),
    );   
    
    //Connect to the API
    $apiReturn = wp_remote_request( 'https://api.censorreact.intygrate.com/v1/login', $args );

    error_log(print_r($apiReturn, true));

    $encodedBody = json_decode($apiReturn['body'], true);
    $apiBody = json_decode($apiReturn['body']);

    if($encodedBody['data'] === 'confirm_required') {
        $wpdb->query(" UPDATE `{$plugin_table_name}` SET `confirmation_sent` = 'yes'
        ");

        //Set the key from the API into the database
        $wpdb->query("UPDATE `{$plugin_table_name}` 
            SET `email` = '{$email}' 
            WHERE `id` = 1" 
        );

        return;
    } else if(isset($apiBody->data->key)) {
        $wpdb->query(" UPDATE `{$plugin_table_name}` SET `confirmation_sent` = 'yes'
        ");

        //Set the key from the API into the database
        $wpdb->query("UPDATE `{$plugin_table_name}` 
            SET `email` = '{$email}' 
            WHERE `id` = 1" 
        );
    }

    //Make sure the response from the database was okay otherwise show an error and stop the processing
    if(is_array($apiReturn) && !empty($apiReturn)) {
        $apiBody = json_decode($apiReturn['body']);
        $apiResponse = $apiReturn['response'];
    }
    
    if(array_key_exists('code', $apiResponse)) {
        if($apiResponse['code'] === 200) {
            //Make sure the key is set before moving on, if not then show an error
            if(isset($apiBody->data->key)) {
                //Set the key from the api response to the variable
                $censorReactKey = $apiBody->data->key;
                //Set the key from the API into the database
                $wpdb->query("UPDATE `{$plugin_table_name}` 
                                SET `key` = '{$censorReactKey}', `user_confirmed` = 'yes', `key_is_valid` = 'yes' 
                                WHERE `id` = 1"
                );
                return;
            } 
            //If for some reason the code is a success and the key is still not in there then it will display this error code
            print('<div class="notice-error notice"><p>Your key could not be found. Please try again.</p></div>');
            return;
        }
    }
    //if the code isnt success then it will display the error from the API
    print("<div class='notice-error notice'><p>" . $apiBody->message . "</p></div>");
    return;
}

function censorreact_register_user($email, $password, $confirmPassword, $confirmationCode, $terms) {

    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";
    //Check if the form 'get-key-user' is set
    if(isset($_POST['censorreact-change-email'])) {
        $wpdb->query("UPDATE `{$plugin_table_name}` SET `email` = '', `user_confirmed` = 'no', `confirmation_sent` = 'no' WHERE `id` = 1"); 
    }

    //Make sure they enter a password
    if(!empty($password) && !empty($confirmPassword)) {
        //If the passwords are the same
        if($password === $confirmPassword) {
            //Get the arguments ready to send through to the API
            $args = array(
                'method' => 'POST', 
                'timeout' => 20, 
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'email' => $email, 
                    'password' => $password,
                    'referrer' => 'wordpress',
                    'confirmationCode' => $confirmationCode,
                    'tnc' => $terms
                )),
            );   
            
            //Connect to the API
            $apiReturn = wp_remote_request( 'https://api.censorreact.intygrate.com/v1/register', $args );
            $encodedBody = json_decode($apiReturn['body'], true);
            $apiBody = json_decode($apiReturn['body']);

            if($encodedBody['data'] === 'confirm_required') {
                $wpdb->query(" UPDATE `{$plugin_table_name}` SET `confirmation_sent` = 'yes'");

                //Set the key from the API into the database
                $wpdb->query("UPDATE `{$plugin_table_name}` 
                    SET `email` = '{$email}' 
                    WHERE `id` = 1" 
                );

                return;
            } else if(isset($apiBody->data->key)) {
                $wpdb->query(" UPDATE `{$plugin_table_name}` SET `confirmation_sent` = 'yes'");

                //Set the key from the API into the database
                $wpdb->query("UPDATE `{$plugin_table_name}` 
                    SET `email` = '{$email}' 
                    WHERE `id` = 1" 
                );
            }

            //Make sure the response from the database was okay otherwise show an error and stop the processing
            if(is_array($apiReturn) && !empty($apiReturn)) {
                $apiBody = json_decode($apiReturn['body']);
                $apiResponse = $apiReturn['response'];
            }
            
            if(array_key_exists('code', $apiResponse)) {
                if($apiResponse['code'] === 200) {
                    //Make sure the key is set before moving on, if not then show an error
                    if(isset($apiBody->data->key)) {
                        //Set the key from the api response to the variable
                        $censorReactKey = $apiBody->data->key;
                        //Set the key from the API into the database
                        $wpdb->query("UPDATE `{$plugin_table_name}` 
                            SET `key` = '{$censorReactKey}', `user_confirmed` = 'yes', `key_is_valid` = 'yes' 
                            WHERE `id` = 1"
                        );

                        return;
                    } 

                    //If for some reason the code is a success and the key is still not in there then it will display this error code
                    print('<div class="notice-error notice"><p>Your key could not be found. Please try again.</p></div>');
                    return;
                }
            }
            if($apiBody->data === 'already_confirmed') {
                $_SESSION['signup-login'] = 'redirect';
            }
            //if the code isnt success then it will display the error from the API
            print("<div class='notice-error notice'><p>" . $apiBody->message . "</p></div>");
            return; 
        } 
        //If password doesnt equal confirm password
        print('<div class="notice-error notice"><p>The passwords do not match. Please try again.</p></div>');
        return;
    }
}

function censorreact_confirm_user($email, $confirmationCode) {

    //This global variable connects to the wordpress database
    global $wpdb;
    //Creating the table name for the returned key
    $plugin_table_name = "censorreact_plugin";

    //Get the arguments ready to send through to the API
    $args = array(
        'method' => 'POST', 
        'timeout' => 20, 
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'email' => $email, 
            'referrer' => 'wordpress',
            'confirmationCode' => $confirmationCode,
            'tnc' => true
        )),
    );   
    
    //Connect to the API
    $apiReturn = wp_remote_request( 'https://api.censorreact.intygrate.com/v1/register', $args );

    $encodedBody = json_decode($apiReturn['body'], true);
    $apiBody = json_decode($apiReturn['body']);

    error_log(print_r($apiReturn, true));

    if($encodedBody['data'] === 'confirm_required') {
        $wpdb->query(" UPDATE `{$plugin_table_name}` SET `confirmation_sent` = 'yes'");

        //Set the key from the API into the database
        $wpdb->query("UPDATE `{$plugin_table_name}` 
            SET `email` = '{$email}' 
            WHERE `id` = 1" 
        );

        return;
    } else if($encodedBody['data'] === 'incorrect_code') {
        print('<div class="notice-error notice"><p>' . $apiBody->message . '</p></div>');
        return;
    } else if($encodedBody['data'] === 'already_confirmed') {
        $_SESSION['signup-login'] = 'redirect';
        $wpdb->query("UPDATE `{$plugin_table_name}` SET `email` = '', `user_confirmed` = 'no', `confirmation_sent` = 'no' WHERE `id` = 1");
        print('<div class="notice-error notice"><p>' . $apiBody->message . '</p></div>');
        return;
    }

    if($encodedBody['data'] === 'success') {
        $_SESSION['signup-login'] = 'redirect';
        $wpdb->query("UPDATE `{$plugin_table_name}` SET `email` = '', `user_confirmed` = 'no', `confirmation_sent` = 'no' WHERE `id` = 1");
        print('<div class="notice-success notice"><p>Thank you for confirming your email. You can now login.</p></div>');
        return;
    }
}

//This global variable connects to the wordpress database
global $wpdb;
//Creating the table name for the returned key
$plugin_table_name = "censorreact_plugin";

//Get the key from the database
$get_key_from_database = $wpdb->get_results("SELECT `key` FROM `$plugin_table_name`");
$get_user_email = $wpdb->get_results("SELECT `email` FROM `$plugin_table_name`");
$get_key_valid = $wpdb->get_results("SELECT `key_is_valid` FROM `$plugin_table_name`");
$limit_reached = $wpdb->get_results("SELECT `limit_reached` FROM `$plugin_table_name` WHERE `id` = 1");
$confirmation_sent = $wpdb->get_results("SELECT `confirmation_sent` FROM `$plugin_table_name`");
$user_confirmed = $wpdb->get_results("SELECT `user_confirmed` FROM `$plugin_table_name`");

//If the key is in the database then show that the plugin is activated otherwise print the register form
if(isset($get_key_from_database[0]->key) && $user_confirmed[0]->user_confirmed == 'yes') {
?>  
    <div id="censorreact-activated-page" class="censorreact-container">
        <div id="censorreact-activated-page-inner"> 
            <div id="censorreact-user-key">
                <h2>Details</h2>
                <div id="censorreact-user-key-inner">
                    <div id="censorreact-status">
                        <p>Account Status:</p>
                        <?php
                            if ($get_key_valid[0]->key_is_valid === 'yes' && $limit_reached[0]->limit_reached !== 'yes' ) {
                                print("<p class='censorreact-status-pos'>You're all good to go!</p>");
                            } else if ($limit_reached[0]->limit_reached === 'yes') {
                                print"<p class='censorreact-status-neg'>You have reached your monthly limits.</p>";
                            } else if ($get_key_valid[0]->key_is_valid !== 'yes') {
                                print"<p class='censorreact-status-neg'>Your censorREACT key is invalid</p>";
                            } else {
                                print"<p class='censorreact-status-neg'>Something has gone wrong.</p>";
                            }
                        ?>
                    </div>
                    <div id="censorreact-username">
                        <p>Username:</p>
                        <p><?= $get_user_email[0]->email ?></p>
                    </div>
                    <div id="censorreact-key">
                        <p>Key:</p>
                        <p><?= $get_key_from_database[0]->key ?></p>
                    </div>
                </div>
            </div> 
            <div id="censorreact-get-latest">
                <div id="censorreact-get-latest-inner">
                    <h2>Get latest key</h2>
                    <p>Enter your censorREACT login details.</p>
                    <div id='censorreact-settings-page-content'>
                        <div id="censorreact-form-errors-div">
                            <div id='censorreact-show-errors' class=''></div>
                        </div>
                        <div id='censorreact-get-latest-register-form'>            
                            <form name='censorreact-get-key-form' id='censorreact-get-key-form' method='post'>
                                <div class='censorreact-input-group'> 
                                    <label class="censorreact-label">Email</label>
                                    <input class='censorreact-input' type='email' name='censorreact-get-key-email' id='censorreact-get-key-email' placeholder='<?= $get_user_email[0]->email ?>' readonly>
                                </div>
                                <div class='censorreact-input-group'>
                                    <label class="censorreact-label">Password</label>
                                    <input class="censorreact-input" type='password' name='censorreact-get-key-password' id='censorreact-get-key-password' placeholder='Enter Password'>
                                </div>
                                <div class='censorreact-button-div'>
                                    <button type='submit' class='censorreact-btn' name='censorreact-get-key-user'>Get Key</button>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
    
    <div id="censorreact-intygrate-website">
        <p>For more options, use your login in our app at <a href='https://censorreact.intygrate.com/' target="_blank">https://censorreact.intygrate.com/</a></p>
    </div>
<?php
} else if($confirmation_sent[0]->confirmation_sent == 'yes' && $user_confirmed[0]->user_confirmed == 'no') {
    /* This will show the confirmation form */
?>
    <div id='censorreact-confirm'>
        <div id='censorreact-confirm-inner'>
            <div id='censorreact-plugin-subtitle'>
                <h2 id='login-text'>Confirmation</h2>
                <p id='verify-text'>A verification code has been sent to your email. Please enter your code below.</p>
            </div>
            <div id='censorreact-confirm-page-content'>
                <div id='censorreact-confirm-form'>          
                    <div id="censorreact-form-errors-div">
                        <div id='censorreact-show-errors' class=''></div>
                    </div>
                    <form name='censorreact-confirm-form' id='censorreact-confirm-form' method='post'>
                        <div class='censorreact-input-group'> 
                            <label class="censorreact-label">Email</label>
                            <input class="censorreact-input" type='email' name='censorreact-confirm-email' id='censorreact-confirm-email' placeholder='<?= $get_user_email[0]->email ?>' readonly>
                        </div>
                        <div class='censorreact-confirmation-code-input'>
                            <label class="censorreact-label">Confirmation Code</label> 
                            <input class="censorreact-input" type='text' name='censorreact-confirmation-code' id='censorreact-confirmation-code'
                            maxlength='6'
                            placeholder='Confirmation Code'>
                        </div>
                        <div class='censorreact-button-div'>
                            <button type='submit' class='censorreact-btn confirm-btn' name='censorreact-confirm-user'>Confirm</button>
                        </div>
                    </form>
                </div>
                <form name='change-email-form' id='censorreact-change-email-form' method='post'>
                    <button type='submit' class='censorreact-btn change-email-btn' name='censorreact-change-email'>Change Email</button>
                </form>
                <form name='resend-code-form' id='censorreact-resend-code-form' method='post'>
                    <button type='submit' class='resend-code-btn' name='censorreact-resend-code'>Resend Code</button>
                </form>
            </div>
        </div>
    </div>
<?php
} else {
    if(isset($_POST['censorreact-login-signup'])){
        error_log('is set loginsignup');
        if($_POST['censorreact-login-signup'] === 'login'){
            $_SESSION['signup-login'] = 'login';
            error_log('was login');
        } else {
            $_SESSION['signup-login'] = 'signup';
        }
    } 
    
    if (isset($_POST['censorreact-login-user'])){
        $_SESSION['signup-login'] = 'login';
    }
    
    if($_SESSION['signup-login'] === 'redirect') {
        $_SESSION['signup-login'] = 'login';
        error_log('redirect');
    }
?>
    <div id='censorreact-login-signup-div'>
        <form name='censorreact-login-signup' id='censorreact-login-signup' method='post'>
            <button type='submit' class='censorreact-signup-btn <?= ($_SESSION['signup-login'] === 'signup' ? 'censorreact-active-form' : '') ?>' name='censorreact-login-signup' value='signup'>SIGN UP</button>
            <button type='submit' class='censorreact-login-btn <?= ($_SESSION['signup-login'] === 'login' ? 'censorreact-active-form' : '') ?>' name='censorreact-login-signup' value='login'>LOGIN</button>
        </form>
    </div>
    <div id="censorreact-login-signup-page">
        <?php if($_SESSION['signup-login'] === 'login') { ?>
            <div id='censorreact-login'>
                <div id='censorreact-login-inner'>
                    <div id="censorreact-form-errors-div">
                        <div id='censorreact-show-errors' class=''></div>
                    </div>
                    <div id='censorreact-login-form-content'>           
                        <form name='censorreact-login-form' id='censorreact-login-form' method='post' novalidate>
                            <div class='censorreact-input-group'> 
                                <label class="censorreact-label">Email</label>
                                <input class="censorreact-input" type='email' name='censorreact-login-email' id='censorreact-login-email' placeholder='Enter Email'>
                            </div>
                            <div class='censorreact-input-group'>
                                <label class="censorreact-label">Password</label>
                                <input class="censorreact-input" type='password' name='censorreact-login-password' id='censorreact-login-password' placeholder='Enter Password'>
                            </div>
                            <div class='censorreact-button-div'>
                                <button type='submit' class='censorreact-btn censorreact-login-form-btn' name='censorreact-login-user'>Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } else if($_SESSION['signup-login'] === 'signup') { ?>
            <div id='censorreact-signup'>
                <div id='censorreact-signup-inner'>
                    <div id="censorreact-form-errors-div">
                        <div id='censorreact-show-errors' class=''></div>
                    </div>
                    <div id='censorreact-register-form-content'>          
                        <form name='censorreact-register-form' id='censorreact-register-form' method='post' novalidate>
                            <div class='censorreact-input-group'> 
                                <label class="censorreact-label">Email</label>
                                <input class="censorreact-input" type='email' name='censorreact-register-email' id='censorreact-register-email' placeholder='Enter Email'>
                            </div>
                            <div class='censorreact-input-group'>
                                <label class="censorreact-label">Password</label>
                                <input class="censorreact-input" type='password' name='censorreact-register-password' id='censorreact-register-password' placeholder='Enter Password'>
                            </div> 
                            <div class='censorreact-input-group'>
                                <label class="censorreact-label">Confirm password</label> 
                                <input class="censorreact-input" type='password' name='censorreact-register-password-confirm' id='censorreact-register-password-confirm' placeholder='Confirm Password'>
                            </div>
                            <div id="censorreact-terms-div" class='censorreact-input-group'>
                                <input type="checkbox" name="censorreact-terms" id='censorreact-terms'>
                                <label class="censorreact-label" id='censorreact-terms'>I agree to the <a href='https://intygrate.com/terms-of-service' target='_blank'>Intygrate Terms of Service</a></label> 
                            </div>
                            <div class='censorreact-button-div'>
                                <button type='submit' class='censorreact-btn' name='censorreact-register-user'>Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php
}
?>
</div>
