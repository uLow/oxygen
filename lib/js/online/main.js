function fixIE7zIndex(selector)
{
    var zIndexNumber = 1000;

    $(selector).each(function(){

        $(this).css('zIndex', zIndexNumber-=10);

    });

}


$(document).ready(function(){

	fixIE7zIndex('.site-tabs-a .tab');

});