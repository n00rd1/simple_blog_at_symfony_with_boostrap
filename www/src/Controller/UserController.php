<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

// Для работы с HTTP кодом
use phpDocumentor\Reflection\DocBlock\Tags\Uses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Для хеширования пароля
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/usr_list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    #[Route('/user/create', name: 'user_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO валидация входных данных


        $user = new User();
        $user->setUsername($request->get('username'));
        $user->setPassword_hash(md5($request->get('password')));;
        $user->setName($request->get('name'));
        $user->setSurname($request->get('surname'));
        $user->setAuth_token(bin2hex(random_bytes(32)));

        $entityManager->persist($user);
        $entityManager->flush();


        return $this->json(['user_id' => $user->getId()]);
    }

    #[Route('/user/login', name: 'user_login')]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO валидация данных

        $userId = new User();
        // TODO добавить получение информации


        // TODO добавить сравнение md5 введённого пароля и пароля из базы данных
/*      if(md5($password) === $password_user)
        {

            //echo "<br> Correct password ";
        }
        else {

            //echo "<br> Incorrect password ";
        }*/
    }

    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_user');
    }

}

/*
class UsersController extends AbstractController
//{

    public function createUser(Request $request, UserRepository $userRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        // Создаём новый класс для пользоватлея
        $user = new User();

        // Задаём данные пользователя
        $user->setUsername($data['username']);
        $user->setPassword_hash(password_hash($data['password'], PASSWORD_ARGON2I));;
        $user->setName($data['name']);
        $user->setSurname($data['surname']);
        $user->setAuth_token(bin2hex(random_bytes(64)));
        $userRepository->save($user, true);

        return new Response(sprintf('User %s successfully created', $user->getEmail()));
    }
*/
/*    public function updateUser(Request $request, UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Я пока своё не придумал, а принцип работы сгенерированного кода - не понял =(
*/

/*      // ---  Made by ChatGPT
        // Получаем данные пользователя из тела запроса
        $userDTO = UserDTO::createFromJson($request->getContent());

        // Обновляем данные пользователя
        $user->setUsername($userDTO->username);
        $user->setEmail($userDTO->email);
*/
/*        // Сохраняем изменения в базе данных
        $userRepository->save($user, true);

        // Возвращаем ответ в виде JSON
        return new Response(['message' => 'User updated successfully'], Response::HTTP_OK);

    }
*/
/*    public function deleteUser(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => sprintf('User with id %d not found', $id)], Response::HTTP_NOT_FOUND);
        }

        $userRepository->remove($user, true);

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }
}*/
