//Register Form Validation
jQuery(document).ready(function($) {

    $('#censorreact-register-form').submit(function(e) {
        // Put all validation in here

        //Email Validation
        const email = $('#censorreact-register-email').val();

        function validateEmail($email) {
            var emailReg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/; 
            return emailReg.test( $email ); 
        }

        if( !validateEmail(email) || email >= 0) {
            console.log('the email is dodgy');
            document.getElementById('censorreact-show-errors').innerHTML = '<p>Please enter a valid email!</p>';
            return false;
        } else {
            console.log('The email is good');
        }

        //Password Validation
        const password1 = $('#censorreact-register-password').val();
        const password2 = $('#censorreact-register-password-confirm').val();

        if(password1 !== password2) {
            console.log('the passwords dont match');
            document.getElementById('censorreact-show-errors').innerHTML = '<p>The Passwords do not match</p>';
            return false;
        } else {
            console.log('The passwords match');
        }

        function validatePassword($password) {
            var passwordReg = /(?=^.{8,}$)(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s)[0-9a-zA-Z!@#$%^&*()]*$/;
            return passwordReg.test( $password );
        }

        if(!validatePassword(password1)) {
            console.log('the password is dodgy');
            document.getElementById('censorreact-show-errors').innerHTML = '<p>Your password must have at least 8 characters, 1 Lowercase, 1 Uppercase and 1 Number</p>';
            return false;
        } else {
            console.log('The password is good');
        }

        const terms = $("input[type='checkbox'][name='censorreact-terms']:checked").length;

        if(terms === 0) {
            document.getElementById('censorreact-show-errors').innerHTML = '<p>To use censorREACT you need to agree the Intygrate Terms of Service</p>';
            return false;
        }
    });
}); 

//Login Form Validation
jQuery(document).ready(function($) {

    $('#censorreact-login-form').submit(function(e) {
        // Put all validation in here

        //Email Validation
        const email = $('#censorreact-login-email').val();

        function validateEmail($email) {
            var emailReg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/; 
            return emailReg.test( $email ); 
        }

        if( !validateEmail(email) || email >= 0) {
            console.log('the email is dodgy');
            document.getElementById('censorreact-show-errors').innerHTML = '<p>Please enter a valid email!</p>';
            return false;
        } else {
            console.log('The email is good');
        }

        //Password Validation
        const password = $('#censorreact-login-password').val();

        function validatePassword($password) {
            var passwordReg = /(?=^.{8,}$)(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s)[0-9a-zA-Z!@#$%^&*()]*$/;
            return passwordReg.test( $password );
        }

        if(!validatePassword(password)) {
            console.log('the password is dodgy');
            document.getElementById('censorreact-show-errors').innerHTML = '<p>Your password must have at least 8 characters, 1 Lowercase, 1 Uppercase and 1 Number</p>';
            return false;
        } else {
            console.log('The password is good');
        }
    });
});

//Get Key Form Validation
jQuery(document).ready(function($) {

    $('#censorreact-get-key-form').submit(function(e) {
        // Put all validation in here
        
        //Password Validation
        const password1 = $('#censorreact-get-key-password').val();

        function validatePassword($password) {
            var passwordReg = /(?=^.{8,}$)(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s)[0-9a-zA-Z!@#$%^&*()]*$/;
            return passwordReg.test( $password );
        }

        if(!validatePassword(password1)) {
            console.log('the password is dodgy');
            document.getElementById('censorreact-show-errors').innerHTML = '<p>Your password must have at least 8 characters, 1 Lowercase, 1 Uppercase and 1 Number</p>';
            return false;
        } else {
            console.log('The password is good');
        }
    });
}); 

//Confirm Form Validation
jQuery(document).ready(function($) {

    $('#censorreact-confirm-form').submit(function(e) {
        // Put all validation in here

        console.log('confirm form validation called');

        const confirmationCode = $('#censorreact-confirmation-code').val();

        function validateCode(confirmationCode) {

            const isNumber = /^\d+$/.test(confirmationCode);

            if(!confirmationCode || confirmationCode.trim().length !== 6 || !isNumber) {
                return false;
            } else {
                return true;
            }
        }
        
        if(!validateCode(confirmationCode)) {
            document.getElementById('censorreact-show-errors').innerHTML = '<p>You need to enter a valid confirmation code.</p>';
            return false;
        } else {
            console.log('The code is good');
        }
    });
}); 