<?php

namespace Geekbrains\Homework\Domain\Controllers;

use Geekbrains\Homework\Application\Application;
use Geekbrains\Homework\Application\Render;
use Geekbrains\Homework\Domain\Models\User;
use Geekbrains\Homework\Application\Auth;


class UserController extends AbstractController {

    protected array $actionsPermissions = [
        'actionHash' => ['admin', 'some'],
        'actionSave' => ['admin']
    ];

    public function actionIndex() {
        $users = User::getAllUsersFromStorage();
        $render = new Render();
        if(!$users){
            return $render->renderPage(
                'user-empty.twig', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]);
        }
        else{
            return $render->renderPage(
                'user-index.twig', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users
                ]);
        }
    }


    public function actionSave(): string {
        if(User::validateRequestData()) {
            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();
            $render = new Render();
            return $render->renderPage(
                'user-created.twig', 
                [
                    'title' => 'Пользователь создан',
                    'message' => "Создан пользователь " . $user->getUserName() . " " . $user->getUserLastName()
                ]);
        }
        else {
            throw new \Exception("Переданные данные некорректны");
        }
    }


    public function actionEdit(): string {
        $render = new Render();
        return $render->renderPageWithForm(
                'user-form.twig', 
                [
                    'title' => 'Форма создания пользователя'
                ]);
    }


    public function actionAuth(): string {
        $render = new Render();
        return $render->renderPageWithForm(
                'user-auth.twig', 
                [
                    'title' => 'Форма логина'
                ]);
    }


    public function actionHash(): string {
        return Auth::getPasswordHash($_GET['pass_string']);
    }

    public function actionLogin(): string {
        $result = false;
        
        if(isset($_POST['login']) && isset($_POST['password'])){
            $result = Application::$auth->proceedAuth($_POST['login'], $_POST['password']);
        }
        
        if(!$result){
            $render = new Render();

            return $render->renderPageWithForm(
                'user-auth.tpl', 
                [
                    'title' => 'Форма логина',
                    'auth-success' => false,
                    'auth-error' => 'Неверные логин или пароль'
                ]);
        }
        else{
            header('Location: /');
            return "";
        }
    }


    public function actionUpdate(): string {
        if(User::exists($_GET['id']) && isset($_GET['name'])) {
            $user = new User();
            $user->setUserId($_GET['id']);
            $user->updateUser($_GET['id'], $_GET['name']);
        }
        else {
            throw new \Exception("Пользователь не существует");
        }

        $render = new Render();
        return $render->renderPage(
            'user-created.twig', 
            [
                'title' => 'Пользователь обновлен',
                'message' => "Обновлен пользователь " . $user->getUserId()
            ]);
    }

    public function actionDelete(): string {
        if(User::exists($_GET['id'])) {
            User::deleteFromStorage($_GET['id']);

            $render = new Render();
            
            return $render->renderPage(
                'user-removed.twig', []
            );
        }
        else {
            throw new \Exception("Пользователь не существует");
        }
    }


}