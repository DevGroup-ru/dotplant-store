"use strict";
(function ($) {
    $('.extended_price_form').on('beforeSubmit', function (e) {
        var form = $(this);

        var formData = form.serializeArray();
        $.each(form.data('extra'), function (index, value) {
            var json = JSON.stringify(value);
            formData.push({name: index, value: json})
        });

        $.ajax({url: form.attr('action'), data: formData, method: "POST"}).done(function (data) {
            if (data.success) {
                form.data('extra', data.extra);
                alert('All ok');
            } else {
                alert('Something is wrong');
            }
            // console.log(data);
            console.log(form.data());
        });
        return false;
    });
})(jQuery);