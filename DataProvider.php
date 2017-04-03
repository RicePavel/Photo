<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DataProvider {

    const HOST = 'localhost';
    const USER = 'photos';
    const PASSWORD = 'qwerty';
    const DATABASE = 'photos';

    private $link;

    public function __construct() {
        $this->link = new mysqli($this::HOST, $this::USER, $this::PASSWORD, $this::DATABASE);
        if ($this->link->connect_errno) {
            echo 'не удалось подключиться в БД';
        }
    }

    public function startTransaction() {
        $this->link->begin_transaction();
    }
    
    public function commitTransaction() {
        $this->link->commit();
    }
    
    public function rollbackTransaction() {
        $this->link->rollback();
    }
    
    public function existUser($login, &$error) {
        $stmt = $this->link->prepare('select user_id from users where login = ?');
        if (!$stmt) {
            $error = $this->link->error;
            return false;
        }
        // параметризовать запрос
        if (!$stmt->bind_param('s', $login)) {
            $error = $this->link->error;
            return false;
        }
        // произвести запрос
        if (!$stmt->execute()) {
            $error = $this->link->error;
            return false;
        }
        // если количество строк > 0
        if ($stmt->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * проверить авторизацию
     * @param type $login
     * @param type $password
     * @return integer
     */
    public function getUserId($login, $password, &$error) {
        $stmt = $this->link->prepare('select user_id from users where login =  ? and password = ?');
        if (!$stmt) {
            $error = $this->link->error;
            return null;
        }
        $md5 = md5($password);
        $ok = $stmt->bind_param('ss', $login, $md5);
        if (!$ok) {
            $error = $this->link->error;
            return null;
        }
        $ok = $stmt->execute();
        if (!$ok) {
            $error = $this->link->error;
            return null;
        }
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // привязать результаты
            if (!$stmt->bind_result($userId)) {
                // записать переменные
                $error = $this->link->error;
                // return
                return null;
            }
            // получить первый результат в переменную 
            while ($stmt->fetch()) {
                return $userId;
            }
            // вернуть переменную
        } else {
            return null;
        }
    }

    /**
     * 
     * @param type $userId
     * @param type $fileName
     * @param type $header
     * @param type $decsription
     * @return id сохраненного файла
     */
    public function saveFile($userId, $fileName, $header, $decsription, &$error) {
        $stmt = $this->link->prepare(' insert into photo (user_id, photo_name, header, description ) values (?, ?, ?, ?) ');
        if (!$stmt) {
            $error = $this->link->error;
            return null;
        }
        // привязать параметры
        if (!$stmt->bind_param('isss', $userId, $fileName, $header, $description)) {
            // проверить ошибки
           $error = $this->link->error;
           return null; 
        }
        if (!$stmt->execute()) {
            // выполнить запрос
            // проверить ошибки
           $error = $this->link->error;
           $err = mysqli_error($this->link);
           return null; 
        }
        // получить ид добавленной записи
        return $this->link->insert_id;
    }
    
    /**
     * зарегитрировать пользователя
     * @param type $login
     * @param type $password
     * @return boolean
     */
    public function registration($login, $password, &$error) {
        // если нет ещё пользователя с таким логином
        if (!$this->existUser($login, $error)) {
            if (strlen($error) == 0) {
                $stmt = $this->link->prepare(' insert into users (login, password) values (?, ?) ');
                if (!$stmt) {
                    $error = $this->link->error;
                    return false;
                }
                $md5 = md5($password);
                $ok = $stmt->bind_param('ss', $login, $md5);
                if (!ok) {
                    $error = $this->link->error;
                    return false;
                }
                $ok = $stmt->execute();
                if (!$ok) {
                    $error = $this->link->error;
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            $error = 'Пользователь с таким логином уже существует';
            return false;
        }
    }

}
