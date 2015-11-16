var info = {
    curentpage: "1",
    numberofelements: jQuery('#pagination-flickr li').size(),
    ajaxurl: info_object.ajax_url,
    loadimage: info_object.loadimage,
    loadingtext: info_object.loadingtext,
    inprogress: 'no'
};

jQuery("#pagination-flickr").hide();

jQuery.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return results[1] || 0;
}

// ajax data
var datajax = {
    'query': jQuery.urlParam('search'),
    'action': 'return_solr_results',
    'page_no': 1,
    'opts': '',
    'sort_opt': 'sort_by_relevancy_des'
};

//select_opt
jQuery('.select_opt').click(function () {
    datajax.opts = jQuery(this).attr("id");
    info.numberofelements = 1;
    info.curentpage = 1;
    console.log('curent data opts ' + datajax.opts);


    setTimeout(function () {
        info.numberofelements = jQuery('#pagination-flickr li').size();
    }, 2000);

    return;
});

function showloading() {
    jQuery('#loadingtext').remove();
    console.log('showloading');
    jQuery("body").prepend('<div id="loadingtext" style="position: fixed; top: 100px; text-align: center; font-size: 15px; z-index: 999; margin: 0px auto; background: #CCC; padding: 30px; left: 40%;">' + info.loadingtext + '<br /><img src="' + info.loadimage + '" alt="loading" /></div>');

    setTimeout(function () {
        jQuery('#loadingtext').remove();
    }, 1000);


    return;
}


jQuery(document).scroll(function () {

    jQuery("#pagination-flickr").hide();

    info.numberofelements = jQuery('#pagination-flickr li').size();

    jQuery(document).ready(function () {

        if (info.numberofelements > 1) {

            var offset = jQuery("#pagination-flickr").offset();
            var scrollposition = jQuery('body').scrollTop();
            var position = Math.round(offset.top);


            scrollpositionnew = scrollposition + 600;


            if (scrollpositionnew >= position && info.curentpage <= info.numberofelements && info.inprogress == 'no') {

                info.inprogress = 'yes';
                showloading(); // show loading bar
                info.curentpage++;
                setTimeout(function () {
                    info.inprogress = 'no';
                }, 1800); // execute function after 2 sec

                datajax.sort_opt = jQuery(".select_field").val();
                console.log('curent select_field is ' + datajax.sort_opt);

                datajax.page_no = info.curentpage;
                console.log('curent data page no ' + datajax.page_no);
                console.log(info.curentpage + 'pagenumber');


                // since wp 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post(info.ajaxurl, datajax, function (response) {

                    var obj = jQuery.parseJSON(response)
                    //console.log(obj);
                    var ojectlenght = obj.length;
                    var i = 1;


                    ojectlenght = ojectlenght - 2; // no need pagination again
                    jQuery.each(obj, function (index, value) {
                        // without  last 2 items
                        //results-by-facets
                        if (i <= ojectlenght && value.length > 0) {
                            jQuery(".results-by-facets").append(value);
                        }
                        i++;
                    });


                });


            } else {
                /*
                 console.log('fade out postion' + position);
                 console.log('fade out scrollpositionnew' + scrollpositionnew);
                 console.log('numberelements ' + info.numberofelements);
                 console.log('info.curentpage ' + info.curentpage);
                 console.log('info.inprogress ' + info.inprogress);
                 console.log(' ----- ');
                 */

                // do noting
            }


        }

    });
});