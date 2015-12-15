


jQuery(document).ready(function(){
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if ( pair[0] === "redirect_to") {
            var parser = document.createElement('a');
            parser.href = decodeURIComponent( pair[1] );

            var vars2 = parser.search.split("&");
            for (var i=0;i<vars2.length;i++) {
                var pair2 = vars2[i].split("=");
                if ( pair2[0] === "?service") {
                    var parser2 = document.createElement('a');
                    parser2.href = decodeURIComponent( pair2[1] );
                    jQuery("#backtoblog a").attr("href", parser2.protocol + "//" + parser2.hostname );
                }
            }
        }
    }
})

