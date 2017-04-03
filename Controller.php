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
            if ($action == 'photosList') {
                $this->photosList();
            } else if ($action == 'addPhotos') {
                $this->addPhotos();
                // добавление
            } else if ($action == 'logout') {
                // выход из системы
                $this->logout();
            }
            // иначе
        } else {
             // перенаправить на страницу для непользователей
            header('location: ?action=forNotAuthUser');
        }
      }
    }
    
    private function logout() {
        unset($_SESSION['userId']);
        header('location: ?action=forNotAuthUser');
    }
    
    private function photosList() {
        // получить список файлов
        require 'renders/PhotoList.php';
    }
    
    private function getCurrentUserId() {
        return (isset($_SESSION['userId']) ? $_SESSION['userId'] : null);
    }
    
    private function addPhotos() {
        
        // получить ИД текущего пользователя
        $userId = $this->getCurrentUserId();
        if ($userId != null) {
            // получить массив файлов
            $files = $_FILES['photos'];
            // для каждого файла
            $error = '';
            $n = 0;
            foreach ($files['name'] as $fileName) {
                // проверить его расширение
                if (!$this->checkFileType($fileName)) {
                    $error = 'неправильный формат файла: все файлы должны быть изображениями';
                    break;
                }
                $n++;
            }

            $n = 0;
            // если нет ошибок
            if ($error == '') {
                // каждый файл
                foreach ($files['name'] as $fileName) {
                    // начать транзакцию
                    $this->dataProvider->startTransaction();
                    // записать в бд
                    $fileId = $this->saveFileToDatabase($files, $n, $userId, $error);
                    if ($fileId != null) {
                        // сохранить на сервер
                        $ok = $this->saveFileToServer($files, $n, $userId, $fileId, $error);
                        // если нет ошибок
                        if ($ok = true && $error == '') {
                            // подтвердить транзакцию 
                            $this->dataProvider->commitTransaction();
                        } else {
                        // если есть ошибки
                            // откатить транзакцию
                            // выйти из цикла
                            $this->dataProvider->rollbackTransaction();
                            break;
                        }
                    } else {
                        //$this->dataProvider->rollbackTransaction();
                        break;
                    }
                }
            }
            // если есть ошибки
            if ($error != '') {
               // вызвать метод вывода списка файлов 
               $this->photosList();
            } else {
                // перенаправить на список
                header('location: ?action=photosList');
            }
        }
    }
    
    /**
     * 
     * @param type $files
     * @param type $n
     * @param type $userId
     * @param type $fileId
     * @param type $error
     * @return boolean
     */
    private function saveFileToServer($files, $n, $userId, $fileId, &$error) {
        // создать директорию
            // если не существует директории для этого пользователя
        $dirName = './photos/' . $userId;
        if (!file_exists($dirName)) {
                // создать директорию
            $ok = $mkdir($dirName);
            if (!$ok) {
                $error = 'Не удалось создать директорию';
                return false;
            }
        }
        // подобрать название для файла 
        
            // задать начальное название
            // если такое название есть
            // прибавлять номер, пока не подберется свободное название
        
        // имя нового файла
        $fileName = './photos/' . $fileId;
        // скопировать файл на новое место
        $ok = copy($files['tmp_name'][$n], $fileName);
        if (!$ok) {
            $error = 'Ошибка! Не удалось загрузить файл';
        }
        return $ok;
    }
    
    
    
    /**
     * 
     * @param type $files
     * @param type $n
     * @param type $userId
     * @param type $error
     * @return integer ид файла либо null
     */
    private function saveFileToDatabase($files, $n, $userId, &$error) {
        $fileName = $files['name'][$n];
        return $this->dataProvider->saveFile($userId, $fileName, '', '', $error);
    }
    
    /**
     * проверить расширение файла
     * @param type $fileName
     * @return boolean
     */
    private function checkFileType($fileName) {
        return true;
    }
    
    /**
     * проверка того, авторизован ли пользователь
     * 
     * @return boolean
     */
    private function checkAuthorization() {
        if (!isset($_SESSION['userId'])) {
            return false;
        }
        return true;
    }
    
    private function forNotAuthUser() {
        require 'renders/forNotAuthUser.php';
    }
    
    private function auth() {
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
                    header('location: ?action=photosList');
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
    
    
    private function registration() {
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
