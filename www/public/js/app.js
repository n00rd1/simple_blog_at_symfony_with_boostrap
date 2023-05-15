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
    $('#createUserButton').click(function () {
        $.ajax('/user/create', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'username': $('#createUserModal #userUsernameInput').val(),
                'password': $('#createUserModal #userPasswordInput').val(),
                'name': $('#createUserModal #userNameInput').val(),
                'surname': $('#createUserModal #userSurnameInput').val()
            },
            success: function (response, status) {
                alert("Пользователь успешно зарегистрирован!");
                location.reload();
            }
        });
    });

    // Для работы с авторизацией
    $('#loginUserButton').click(function () {
        $.ajax('/user/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'username': $('#loginUserModal #userUsernameInput').val(),
                'password': $('#loginUserModal #userPasswordInput').val()
            },
            success: function (response, status) {
                alert("Авторизация успешна!");
                location.reload();
            }
        });
    });
});
