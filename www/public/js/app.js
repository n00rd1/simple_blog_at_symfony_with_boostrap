$(document).ready(function () {
    const pageMessages = document.body.dataset;

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
            success: function (response) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    alert(pageMessages.userCreatedMessage);
                    location.reload();
                }
            }
        });
    });

    $('#loginUserSendButton').click(function () {
        $.ajax('/user/login', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'username': $('#loginUserModal #loginUserUsernameInput').val(),
                'password': $('#loginUserModal #loginUserPasswordInput').val(),
            },
            success: function (response) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    location.reload();
                }
            }
        });
    });

    $('#logoutUserButton').click(function () {
        $.ajax('/user/logout', {
            'method': 'POST',
            'dataType': 'json',
            'data': {},
            success: function (response) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    location.reload();
                }
            }
        });
    });

    $('#addArticleSendButton').click(function () {
        $.ajax('/article/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'text': $('#addArticleModal #articleTextInput').val(),
            },
            success: function (response) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    location.reload();
                }
            }
        });
    });

    $('#addCommentSendButton').click(function () {
        $.ajax('/comment/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                'article_id': $('#articleId').data('id'),
                'text': $('#addCommentModal #commentTextInput').val(),
            },
            success: function (response) {
                if (response.success === false) {
                    alert(response.error);
                } else {
                    location.reload();
                }
            }
        });
    });

});
