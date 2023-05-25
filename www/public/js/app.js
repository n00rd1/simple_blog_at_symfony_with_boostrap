$(document).ready(function () {
    /**
     * Пример написания кода по добавлению информации
     */
    $('#addProductSendButton').click(function () {
        $.ajax('/product/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'name': $('#addProductModal #productNameInput').val(),
                'price': $('#addProductModal #productPriceInput').val(),
                'size': $('#addProductModal #productSizeInput').val()
            },
            success: function (response, status) {
                alert("Товар успешно добален!");
                location.reload();
            }
        });
    });

    /**
     * Для работы с регистрацией
     * createUserUsernameInput  - Создание имени пользователя
     * createUserPasswordInput  - Создание пароля
     * createUserNameInput      - Создание имени
     * createUserSurnameInput   - Создание фамилии
     */
    $('#createUserSendButton').click(function () {
        $.ajax('/user/create', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'username': $('#createUserModal #createUserUsernameInput').val(),
                'password': $('#createUserModal #createUserPasswordInput').val(),
                'name': $('#createUserModal #createUserNameInput').val(),
                'surname': $('#createUserModal #createUserSurnameInput').val()
            },
            success: function (response, status) {
                alert("Пользователь успешно зарегистрирован!");
                location.reload();
            }
        });
    });

    /**
     * Для работы с авторизацией
     * loginUserUsernameInput - поле для указания имени пользователя
     * loginUserPasswordInput - поле для указания пароля
     */
    $('#loginUserSendButton').click(function () {
        $.ajax('/user/login', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'username': $('#loginUserModal #loginUserUsernameInput').val(),
                'password': $('#loginUserModal #loginUserPasswordInput').val()
            },
            success: function (response, status) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    alert("Авторизация успешна!");
                    location.reload();
                }
            }
        });
    });

    /**
     * Для создания блога
     *  articleAuthorIdInput - поле для отображения пользовательского ника
     *  articleTextInput - поле для указания текста блога
     */
    $('#addArticleSendButton').click(function () {
        $.ajax('/article/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'author_id': $('#createArticleModal #articleAuthorIdInput').val(),
                'text': $('#createArticleModal #articleTextInput').val(),
            },
            success: function (response, status) {
                alert("Блог успешно опубликован>!");
                location.reload();
            }
        });
    });

    /**
     * Для выхода из учётной записи
     */
    $('#logoutUserButton').click(function () {
        $.ajax('/user/logout', {
            'method': 'POST',
            'dataType': 'json',
            'data': {},
            success: function (response, status) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    alert("Вы успешно вышли из системы!");
                    location.reload();
                }
            }
        });
    });
});
