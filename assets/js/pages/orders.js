$(document).ready(function () {
    // Filtros persistentes durante la sesión
    ['status', 'date_from', 'date_to', 'warehouse_id', 'seller_user_id'].forEach(function (key) {
        var $el = $('#' + key);
        if ($el.length && sessionStorage.getItem('sgms_f_' + key)) {
            $el.val(sessionStorage.getItem('sgms_f_' + key));
        }
        $el.on('change', function () {
            sessionStorage.setItem('sgms_f_' + key, $(this).val());
        });
    });
});
