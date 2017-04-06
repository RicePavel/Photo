/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
    // при щелчке на контейнер
    $('.photo_container').click(function(){
        // если есть класс selected
        var elem = $(this);
        var input = elem.find('input[type=checkbox]');
        if (elem.hasClass('selected')) {
            // отменить отметку чекбокса
            input.prop('checked', false);
            // убрать класс selected 
            elem.removeClass('selected');
        } else {
            // если нет класса selected
                // добавить класс selected
            elem.addClass('selected');
                // отметить чекбокс
            input.prop('checked', true);
        }
    });
    
    // показать индикатор загрузки
    $('.uploadForm').submit(function() {
        $('.uploadIndicator').show();
    });
    
});
    

