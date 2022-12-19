<?php
namespace App;

class App {
    private $action;

    public function __construct() {
        //if action is specified store it
        $this->action = $_GET['a'] ?? null;
    }

    public function init() {
        //if action is requested
        if (isset($this->action)) {
            //check if similar function exists as page and load it
            if (method_exists('App\Controllers\Account', $this->action . 'Page'))
                static::render($this->action);
            else
                static::render('404');
        } else
            static::render('index');
    }

    private static function render($action, array $args = []) {
        if ($action != '404')
            call_user_func_array(['App\Controllers\Account', $action . 'Page'], $args);

        if (strpos($action, 'json') !== false)
            return true;

        $tmpl = file_get_contents(dirname(__FILE__) . '/Views/template.html');
        $view = file_get_contents(dirname(__FILE__) . '/Views/' . $action . '.html');

        if (isset($_SESSION['message'])) {
            $message = '<div class="notification">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        } else
            $message = '';

        echo str_replace([
            '{{ title }}',
            '{{ message }}',
            '{{ body }}',
            '{{ website }}',
            '{{ username }}',
            '{{ email }}',
            '{{ user_name }}',
            '{{ user_email }}',
        ], [
                ucfirst($action),
                $message,
                $view,
                'http://' . _ROOT,
                $_SESSION['username'] ?? '',
                $_SESSION['email'] ?? '',
                $_SESSION['user_name'] ?? '',
                $_SESSION['user_email'] ?? '',
            ], $tmpl);

        unset($_SESSION['username']);
        unset($_SESSION['email']);
    }

    public static function redirect(string $url = '') {
        header('Location: http://' . _ROOT . '/index.php' . (!empty($url) ? '?a=' . strtolower($url) : ''));
        exit;
    }
}
?>