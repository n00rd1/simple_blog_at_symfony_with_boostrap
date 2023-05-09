<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

// Для работы с HTTP кодом
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Для хеширования пароля
use Symfony\Component\Security\Core\User\UserInterface;
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
    public function add(): Response
    {
        return $this->render('user/usr_list.html.twig', [
            'controller_name' => 'UserController',
            'user_string' =>'USER ADD',
        ]);
    }

/*    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function remove(): Response
    {
        return $this->render();
    }*/

    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_product');
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
