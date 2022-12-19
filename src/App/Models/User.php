<?php

namespace App\Models;

use PDO;
use PDOException;

class User {

    static $db;

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->$key = filter_var($value, FILTER_SANITIZE_STRING);
        }
    }

    public static function DB() {
        if (is_null(static::$db)) {
            $host = _DBHOST;
            $database = _DBNAME;
            $username = _DBUSER;
            $password = _DBPASS;

            static::$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);

            static::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return static::$db;
    }

    public function checkLogin() {
        try {
            $db = static::DB();
            $stmt = $db->prepare("SELECT * FROM `users` WHERE `username` = :username;");
            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);

            $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            $stmt->execute();

            $user = $stmt->fetch();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        if ($user) {
            if (password_verify($this->password, $user->password))
                return $user;
        }

        return false;
    }

    public function registerUser() {
        $this->validate();

        if (empty($this->errors)) {
            try {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

                $db = static::DB();
                $stmt = $db->prepare("INSERT INTO `users` (`username`, `password`, `email`) VALUES (:username, :password, :email);");

                $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
                $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
                $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

                return $stmt->execute();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        $_SESSION['message'] = implode('<br />', $this->errors);

        return false;
    }

    private function validate() {
        if (empty($this->username)) {
            $this->errors[] = 'Το Ονοματεπώνυμο είναι υποχρεωτικό';
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Μη έγκυρο Email';
        }

        if ($this->emailExists($this->email)) {
            $this->errors[] = 'Το Email αυτό υπάρχει ήδη';
        }

        if (empty($this->password) || preg_match('/\s+/i', $this->password)) {
            $this->errors[] = 'Ο Κωδικός είναι υποχρεωτικός';
        }

        if (strcmp($this->password, $this->password_confirmation) !== 0) {
            $this->errors[] = 'Το πεδίο Επανάληψη Κωδικού πρέπει να είναι ίδιο με το πεδίο Κωδικού';
        }
    }

    public static function emailExists($email) {
        try {
            $db = static::DB();
            $stmt = $db->prepare("SELECT * FROM `users` WHERE `email` = :email;");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);

            $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            $stmt->execute();

            if ($stmt->fetch())
                return true;
            else
                return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}