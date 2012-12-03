$.fn.extend({
    infoElement: function(select, options){
        var element = $(this);
        var handler = function(){                        
            var item = options['data'][select.val()];
            
            var message = new Array();
            if(item != undefined){
                if(item['sig_digits'] != null){
                    message.push(options['expected'] + ': <span class="sigdigits">' + item['sig_digits'] + '</span>.');
                }
                if(item['unit'] != null){
                    message.push(options['unit'] + ': <span class="unit">' + item['unit'] + '</span>.');
                }
                if(item['type'] == 'real'){
                    message.push(options['realHint']);
                }
            }
            
            element.html(message.join(' '));
        };
        select.change(handler);
        select.keyup(handler);
    }
});
