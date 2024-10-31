jQuery(document).ready(function() {
    setInterval( function() {
        jQuery( "#siw-server" ).load( location.href + " #siw-server" );
        jQuery( "#siw-php" ).load( location.href + " #siw-php" );
        jQuery( "#siw-wordpress" ).load( location.href + " #siw-wordpress" );
    }, 3000 );
});