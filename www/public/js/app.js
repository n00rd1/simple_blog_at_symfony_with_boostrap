$(document).ready(function () {
    // Пример написания кода на JS
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
    $('#btn2').click(function () {
        alert("hello 2");
    });

    // Для работы с регистрацией
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

    // Для работы с авторизацией
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

    // Для создания блога
    $('#addArticleSendButton').click(function () {
        $.ajax('/article/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'author_id': $('#createArticleModal #articleAuthoridInput').val(),
                'text': $('#createArticleModal #articleTextInput').val(),
            },
            success: function (response, status) {
                alert("Блог успешно опубликован>!");
                location.reload();
            }
        });
    });
});
