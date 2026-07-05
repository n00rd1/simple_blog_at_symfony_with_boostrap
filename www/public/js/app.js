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

    function handleRequestError() {
        showNotification(pageMessages.requestFailedMessage);
    }

    $('#createUserSendButton').click(function () {
        $.ajax('/user/create', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                '_csrf_token': pageMessages.csrfRegister,
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
            },
            error: handleRequestError
        });
    });

    $('#loginUserSendButton').click(function () {
        $.ajax('/user/login', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                '_csrf_token': pageMessages.csrfLogin,
                'username': $('#loginUserModal #loginUserUsernameInput').val(),
                'password': $('#loginUserModal #loginUserPasswordInput').val(),
            },
            success: function (response) {
                if (response.success === false) {
                    showNotification(response.error);
                } else {
                    location.reload();
                }
            },
            error: handleRequestError
        });
    });

    $('#logoutUserButton').click(function () {
        $.ajax('/user/logout', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                '_csrf_token': pageMessages.csrfLogout
            },
            success: function (response) {
                if (response.success === false) {
                    showNotification(response.error);
                } else {
                    location.reload();
                }
            },
            error: handleRequestError
        });
    });

    $('#addArticleSendButton').click(function () {
        $.ajax('/article/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                '_csrf_token': pageMessages.csrfArticleAdd,
                'text': $('#addArticleModal #articleTextInput').val(),
            },
            success: function (response) {
                if (response.success === false) {
                    showNotification(response.error);
                } else {
                    location.reload();
                }
            },
            error: handleRequestError
        });
    });

    $('#addCommentSendButton').click(function () {
        $.ajax('/comment/add', {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                '_csrf_token': pageMessages.csrfCommentAdd,
                'article_id': $('#articleId').data('id'),
                'text': $('#addCommentModal #commentTextInput').val(),
            },
            success: function (response) {
                if (response.success === false) {
                    showNotification(response.error);
                } else {
                    location.reload();
                }
            },
            error: handleRequestError
        });
    });

    $('.like-button').click(function () {
        const button = $(this);
        const articleId = button.data('article-id');

        if (button.prop('disabled') || !articleId) {
            return;
        }

        button.prop('disabled', true);

        $.ajax(`/article/${articleId}/like`, {
            'method': 'POST',
            'dataType': 'json',
            'data': {
                '_csrf_token': pageMessages.csrfArticleLike
            },
            success: function (response) {
                if (response.success === false) {
                    showNotification(response.error);
                    return;
                }

                const liked = Boolean(response.data.liked);

                button.toggleClass('liked', liked);
                button.attr('aria-pressed', liked ? 'true' : 'false');
                button.find('.like-count').text(response.data.like_count);
            },
            error: handleRequestError,
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

});
