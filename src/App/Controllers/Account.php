<?php

namespace App\Controllers;

use App\App;
use \App\Models\User;

class Account {
    public static function indexPage() {
        if (!isset($_SESSION['user_name']))
            App::redirect('login');
    }

    public static function profilePage() {
        if (!isset($_SESSION['user_name'])) {
            $_SESSION['message'] = 'Πρέπει να είστε συνδεδεμένος για να δείτε το περιεχόμενο αυτής της σελίδας.';
            App::redirect('login');
        }
    }

    public static function loginPage() {
        if (isset($_POST['login'])) {
            $user = new User($_POST);
            $valid_user = $user->checkLogin();
            if ($valid_user) {
                session_regenerate_id(true);

                $_SESSION['user_name'] = $valid_user->username;
                $_SESSION['user_email'] = $valid_user->email;

                App::redirect('profile');
            } else {
                $_SESSION['message'] = 'Το Όνομα χρήστη ή ο Κωδικός δεν είναι σωστό.';
                App::redirect('login');
            }
        }
    }

    public static function registerPage() {
        if (isset($_POST['register'])) {
            $user = new User($_POST);

            if ($user->registerUser()) {
                $_SESSION['message'] = 'Η εγγραφή σας ολοκληρώθηκε με επιτυχία!';
                unset($_SESSION['username']);
                unset($_SESSION['email']);
                App::redirect();
            } else {
                $_SESSION['username'] = $user->username;
                $_SESSION['email'] = $user->email;
                App::redirect('register');
            }
        }
    }

    public static function logoutPage() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        App::redirect('logoutmessage');
    }

    public static function logoutMessagePage() {
        $_SESSION['message'] = 'Αποσυνδεθήκατε επιτυχώς!';
        App::redirect();
    }

    public static function checkEmailJsonPage() {
        $input = file_get_contents('php://input');
        $input = filter_var($input, FILTER_VALIDATE_EMAIL);
        $check = User::emailExists($input);

        header('Content-Type: application/json');
        echo json_encode(array($check));
    }
}
