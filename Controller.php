<?php

require_once 'DataProvider.php';


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Controller {
    
    private $dataProvider;
    
    public function __construct() {
        $this->dataProvider = new DataProvider();
    }
    
    public function exec() {
      // начать работу с сессией
      session_start();
      // получить действие
      $action = '';
      if (isset($_REQUEST['action']) && $_REQUEST['action'] != '') {
          $action = $_REQUEST['action'];
      } else {
          $action = 'photosList';
      }
      // если действия без авторизации
      if ($action == 'auth') {
          $this->auth();
      } else if ($action == 'registration') {
          $this->registration();
      } else if ($action == 'forNotAuthUser') {
          $this->forNotAuthUser();
      } else {        
        // если есть авторизация
         if ($this->checkAuthorization()) {
             // пока показать список фотографий
             echo 'тут будет список фотографий';
            // иначе
        } else {
             // перенаправить на страницу для непользователей
            header('location: ?action=forNotAuthUser');
        }
      }
    }
    
    /**
     * проверка того, авторизован ли пользователь
     * 
     * @return boolean
     */
    public function checkAuthorization() {
        if (!isset($_SESSION['userId'])) {
            return false;
        }
        return true;
    }
    
    public function forNotAuthUser() {
        require 'renders/forNotAuthUser.php';
    }
    
    public function auth() {
        // если отправлена форма
        $error = '';
        if (isset($_REQUEST['submit'])) {
            // проверить авторизацию
            // если переданы обязательные параметры
            if (isset($_REQUEST['login'], $_REQUEST['password']) 
                    && $_REQUEST['login'] != ''
                    && $_REQUEST['password'] != '') {
                $login = $_REQUEST['login'];
                $password = $_REQUEST['password'];
                $userId = $this->dataProvider->getUserId($login, $password, $error);
                // если да
                if ($userId != null) {
                    // перенаправить на список
                    $_SESSION['userId'] = $userId;
                    header('location: ?action=photoList');
                } else {
                    $error = 'не найдено пользователя с таким логином и паролем';
                } 
            } else {
                $error = 'Не переданы обязательные параметры';
            }
        }
        // показать форму
        require 'renders/auth.php';
    }
    
    
    public function registration() {
        $error = '';
        if (isset($_REQUEST['submit'])) {
            // проверить авторизацию
            // если переданы обязательные параметры
            if (isset($_REQUEST['login'], $_REQUEST['password'])
                    && $_REQUEST['login'] != ''
                    && $_REQUEST['password'] != '') {
                $login = $_REQUEST['login'];
                $password = $_REQUEST['password'];
                $authResult = $this->dataProvider->registration($login, $password, $error);
                // если да
                if ($authResult == true) {
                    // перенаправить на список
                    header('location: ?action=photoList');
                } 
            } else {
                $error = 'Не переданы обязательные параметры';
            }
        }
        // показать форму
        require 'renders/registration.php';
    }
    
}
