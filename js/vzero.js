jQuery( function( $ ) {
    $( 'form.checkout' ).submit( function(event) {
        alert('test');
        event.preventDefault();
        return false;
    });
});
