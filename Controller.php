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
                } else if ($action == 'changePhotoForm') {
                    $this->changePhotoForm();
                } else if ($action == 'changePhoto') {
                    $this->changePhoto();
                } else if ($action = 'deletePhotos') {
                    $this->deletePhotos();
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

    private function photosList($error = '') {
        // получить ИД текущего пользователя
        $userId = $this->getCurrentUserId();
        // получить список фотографий из базы
        // если передана строка поиска
        $searchText = '';
        if (isset($_REQUEST['searchText']) && $_REQUEST['searchText'] != '') {
            // передать её в функцию
            $searchText = trim($_REQUEST['searchText']);
        }
        $photoArray = $this->dataProvider->getPhotoList($userId, $searchText, $error);
        if ($photoArray != null) {
            $this->setPathToPhotos($photoArray, $userId);
        }
        require 'renders/PhotoList.php';
    }

    private function setPathToPhotos($photoArray, $userId) {
        $dirName = './photos/' . $userId;
        foreach ($photoArray as $photo) {
            $photo->path = $dirName . '/' . $photo->photoName;
        }
    }

    private function deletePhotos() {
        // получить массив ИД фотографий
        // удалить их из бд
        $error = '';
        $photoIdArray = array();
        if (isset($_REQUEST['photoId'])) {
            $photoIdArray = $_REQUEST['photoId'];
        }
        // получить массив объектов Photo
        $photoArray = $this->dataProvider->getPhotos($photoIdArray, $error);
        if ($error == '') {
            $userId = $this->getCurrentUserId();
            $this->setPathToPhotos($photoArray, $userId);
            $ok = $this->dataProvider->deletePhotos($photoIdArray, $error);
            // если успешно удалились из бд
            if ($ok) {
                // удалить из файловой системы
                $this->deletePhotosFromServer($photoArray, $error);
            }
        }
        // перенаправить на список фотографий
        header('location: ?action=photosList');
    }

    private function deletePhotosFromServer($photoArray, &$error) {
        // если нет ошибок
        if ($error == '') {
            // цикл по массиву объектов
            foreach ($photoArray as $photo) {
                // удалить каждую фотографию
                $ok = unlink($photo->path);
                if (!$ok) {
                    $error = 'произошла ошибка во время удаления файла';
                }
            }
        }
    }

    private function changePhotoForm() {
        // получить данные фотографии
        // показать форму
        $userId = $this->getCurrentUserId();
        if (isset($_REQUEST['photoId']) && $_REQUEST['photoId'] != '') {
            $photoId = $_REQUEST['photoId'];
            $error = '';
            $photo = $this->dataProvider->getPhoto($photoId, $error);
            $this->setPathToPhotos(array($photo), $userId);
            require 'renders/PhotoChange.php';
        }
    }

    private function changePhoto() {
        // получить данные из запроса
        if (isset($_REQUEST['photoId']) && $_REQUEST['photoId'] != '') {
            $photoId = $_REQUEST['photoId'];
            $header = $_REQUEST['header'];
            $description = $_REQUEST['description'];
            // изменить данные фотографии
            $error = '';
            $ok = $this->dataProvider->changePhoto($photoId, $header, $description, $error);
            // если успешно
            if ($ok) {
                // перенаправить на список
                header('location: ?action=photosList');
            } else {
                // если неуспешно
                // получить данные фотографии
                // отобразить форму изменения
                $this->changePhotoForm();
            }
        }
    }

    private function getCurrentUserId() {
        return (isset($_SESSION['userId']) ? $_SESSION['userId'] : null);
    }

    private function addPhotos() {
        // получить ИД текущего пользователя
        $userId = $this->getCurrentUserId();
        if ($userId != null) {
            // получить массив файлов
            if (isset($_FILES['photos'])) {
                $files = $_FILES['photos'];
                // для каждого файла
                $error = '';
                if (!$this->checkFileTypes($files)) {
                    $error = 'Неправильный тип файлов. Все файлы должны быть изображениями.';
                }
                // если нет ошибок
                if ($error == '') {
                    // каждый файл
                    $n = 0;
                    foreach ($files['name'] as $fileName) {
                        // сохранить файл, получить название файла
                        $fileName = $this->saveFileToServer($files, $n, $userId, $error);
                        // если сохранение прошло успешно
                        if ($fileName != null) {
                            // сохранить файл в БД                    
                            // записать в бд
                            $fileId = $this->saveFileToDatabase($files, $n, $userId, $fileName, $error);
                        }
                        $n++;
                    }
                }
            }
            // если есть ошибки
            if ($error != '') {
                // вызвать метод вывода списка файлов 
                $this->photosList($error);
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
     * @param type $error
     * @return string название файла
     */
    private function saveFileToServer($files, $n, $userId, &$error) {
        // название директории для пользователя
        $dirName = './photos/' . $userId;
        // если директории не существует
        if (!file_exists($dirName)) {
            // создать директорию
            // проверить ошибки
            $ok = mkdir($dirName);
            if (!$ok) {
                $error = 'не удалось создать директорию';
                return null;
            }
        }
        // получить имя для сохраняемого файла
        $fileName = $this->getFileName($dirName, $files, $n);
        // сохранить файл функцией copy
        $ok = copy($files['tmp_name'][$n], $fileName);
        // вернуть название сохраненного файла
        if (!$ok) {
            $error = 'Ошибка - не удалось загрузить файл';
            return null;
        }
        // получить краткое имя файла
        return basename($fileName);
    }

    private function getFileName($dirName, $files, $n) {
        // получить название файла
        $name = $files['name'][$n];
        // получить основное имя файла
        // получить расширение
        $basicName = '';
        $extension = '';
        $arr = explode('.', $name);
        if (count($arr) >= 2) {
            $basicName = $arr[0];
            $extension = $arr[1];
        } else {
            $basicName = $name;
        }
        // к названию директории прибавить название файла
        $newFileName = $dirName . '/' . $name;
        // если такого файла не существует
        if (!file_exists($newFileName)) {
            // вернуть это имя
            return $newFileName;
        } else {
            // иначе
            $n = 1;
            // пока такой файл существует
            while (file_exists($newFileName)) {
                // сконструировать новое название файла
                // основное имя + цифра в скобках + расширение
                $newFileName = $dirName . '/' . $basicName . ' (' . $n . ') ' . '.' . $extension;
                $n++;
            }
            // вернуть название файла
            return $newFileName;
        }
    }

    /**
     * 
     * @param type $files
     * @param type $n
     * @param type $userId
     * @param type $error
     * @return integer ид файла либо null
     */
    private function saveFileToDatabase($files, $n, $userId, $fileName, &$error) {
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
     * проверить правильность типов загружаемых файлов
     * @param type $files
     * @return bool
     */
    private function checkFileTypes($files) {
        foreach ($files['type'] as $type) {
            if (strpos($type, 'image') === false) {
                return false;
            }
        }
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
            if (isset($_REQUEST['login'], $_REQUEST['password']) && $_REQUEST['login'] != '' && $_REQUEST['password'] != '') {
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
            if (isset($_REQUEST['login'], $_REQUEST['password']) && $_REQUEST['login'] != '' && $_REQUEST['password'] != '') {
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
