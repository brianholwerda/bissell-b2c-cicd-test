$().ready(function () {
    window.orderLineValidator = $("#orderform").validate({
        rules: {
            shippingmethod: {
                required:true
            }
        }
    })
});