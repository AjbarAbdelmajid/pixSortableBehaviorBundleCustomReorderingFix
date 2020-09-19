//const routes = require('/js/fos_js_routes.json');
//import Routing from '/bundles/fosjsrouting/js/router.min.js';

$(function() {
    $('.sonata-ba-list').draggableTable();
});

$.fn.draggableTable = function() {
    $(this).each(function (index, item) {
        item = $(item);
        if (!item.data('DraggableTable')) {
            item.data('DraggableTable', new DraggableTable(item));
        }
    });
};

var DraggableTable = function(element) {
    var movers = element.find('.js-sortable-move');
    if (movers.length <= 1) return;

    var $document = $(document);
    var $body = $(document.body);

    var first = parseInt(movers.first().attr('data-current-position'));
    var last = parseInt(movers.last().attr('data-current-position'));
    var direction = first <= last ? 1 : -1;

    element.find('tbody').sortable({
        'handle': '.js-sortable-move',
        'start': function() {
            $body.addClass('is-dragging');
        },
        'stop': function() {
            setTimeout(function() {
                $body.removeClass('is-dragging');
            }, 100);
        },
        'axis': 'y',
        'cancel': 'input,textarea,select,option,button:not(.js-sortable-move)',
        'tolerance': 'pointer',
        'revert': 100,
        'cursor': 'move',
        'zIndex': 1,
        'helper': function(e, ui) {
            ui.css('width', '100%');
            ui.children().each(function() {
                var item = $(this);
                item.width(item.width());
            });
            return ui;
        },
        'update': function(event, ui) {
            element.find('.js-sortable-move').each(function(index, item) {
                $(item).attr('data-current-position', first + (index * direction));
            });

            var moved = $(ui.item).find('.js-sortable-move');
            var newPosition = moved.attr('data-current-position');

            // get the index of /move/
                var moveIndex =  moved.attr('data-url').indexOf('/move/');

            // replace admi/app/ by nothing
                var valuableStr = moved.attr('data-url').replace('/admin/app/', '');

            // from 0 to indexof(/) => that's the table name 
                var entityname = valuableStr.slice(0, valuableStr.indexOf('/'));

            // remove the intityName from the str
                var valuableStr = valuableStr.replace(entityname+'/', '');

            // from 0 to indexof(/) => that's the id of the element 
                var elementId = valuableStr.slice(0, valuableStr.indexOf('/'));
            
            url = Routing.generate('dragdrop')+ "/" + elementId + "/" + entityname +"/"+ newPosition

            $document.trigger('pixSortableBehaviorBundle.update', [event, ui]);

            $.ajax({
                'type': 'GET',
                'url': url.replace(window.location.host, ''),
                'error': function(data) {
                    // console.log(data);
                    $document.trigger('pixSortableBehaviorBundle.error', [data]);
                },
                'success': function(data) {
                    location.reload();
                    // console.log(data);
                    $document.trigger('pixSortableBehaviorBundle.success', [data]);
                },
                'complete': function(data) {
                    
                    $document.trigger('pixSortableBehaviorBundle.complete');
                }
            });
        }
    }).disableSelection();
};

function move_by_buttons(newPosition, entity_name, current_position, direction){

    url = Routing.generate('top_bottom')+ "/" + newPosition + "/" + entity_name +"/"+ current_position + "/"+direction

            $.ajax({
                'type': 'GET',
                'url': url.replace(window.location.host, ''),
                'error': function(data) {

                    // console.log(data);
                    // alert( url );
                    
                },
                'success': function(data) {
                    location.reload();

                    // console.log(data);
                },
            });
}
function move_up_down(current_position, entity_name, direction){

    url = Routing.generate('movebyone')+ "/" + current_position + "/" + entity_name + "/"+direction

            $.ajax({
                'type': 'GET',
                'url': url.replace(window.location.host, ''),
                'error': function(data) {

                    // console.log(data);
                    // alert( url );
                    
                },
                'success': function(data) {
                    location.reload();

                    // console.log(data);
                },
            });
}
function checkPosition(currentPosition, entity_name){
    url = Routing.generate('checkposition')+ "/" + currentPosition + "/" + entity_name

            $.ajax({
                'type': 'GET',
                'url': url.replace(window.location.host, ''),
                'error': function(data) {

                    // console.log(data);
                    // alert( url );
                    
                },
                'success': function(data) {
                    if(data[0] !== 'stop')
                        location.reload();
                    // console.log(data[0]);
                },
            });
}