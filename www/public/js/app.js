$(document).ready(function () {
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
});
