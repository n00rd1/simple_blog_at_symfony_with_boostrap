<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;

// Для работы с HTTP кодом
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Для хеширования пароля
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UsersController extends AbstractController
{


    /**
     * @Route("/users", methods={"POST"})
     */
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

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function updateUser(Request $request, UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Я пока своё не придумал, а принцип работы сгенерированного кода - не понял =(



/*      // ---  Made by ChatGPT
        // Получаем данные пользователя из тела запроса
        $userDTO = UserDTO::createFromJson($request->getContent());

        // Обновляем данные пользователя
        $user->setUsername($userDTO->username);
        $user->setEmail($userDTO->email);
*/
        // Сохраняем изменения в базе данных
        $userRepository->save($user, true);

        // Возвращаем ответ в виде JSON
        return new Response(['message' => 'User updated successfully'], Response::HTTP_OK);

    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function deleteUser(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => sprintf('User with id %d not found', $id)], Response::HTTP_NOT_FOUND);
        }

        $userRepository->remove($user, true);

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }
}
