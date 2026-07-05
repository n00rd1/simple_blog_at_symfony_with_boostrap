$(document).ready(function () {
    const pageMessages = document.body.dataset;

    function showNotification(message, variant = 'danger') {
        const toastElement = $('<div>')
            .addClass(`toast notification-toast text-bg-${variant} border-0`)
            .attr({
                'role': 'alert',
                'aria-live': 'assertive',
                'aria-atomic': 'true'
            });
        const toastBody = $('<div>').addClass('d-flex');
        const toastMessage = $('<div>').addClass('toast-body').text(message);
        const closeButton = $('<button>')
            .attr({
                'type': 'button',
                'data-bs-dismiss': 'toast',
                'aria-label': 'Close'
            })
            .addClass('btn-close btn-close-white me-2 m-auto');

        toastBody.append(toastMessage, closeButton);
        toastElement.append(toastBody);
        $('#notificationArea').append(toastElement);

        const toast = new bootstrap.Toast(toastElement[0], {delay: 4200});
        toastElement.on('hidden.bs.toast', function () {
            toastElement.remove();
        });
        toast.show();
    }

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
                    showNotification(response.error);
                } else {
                    showNotification(pageMessages.userCreatedMessage, 'success');
                    setTimeout(function () {
                        location.reload();
                    }, 700);
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
                    showNotification(response.error);
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
                    showNotification(response.error);
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
                    showNotification(response.error);
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
                    showNotification(response.error);
                } else {
                    location.reload();
                }
            }
        });
    });

});
