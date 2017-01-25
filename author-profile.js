// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		var profile_data = null, countries, states;
		if( abbeyAuthorProfile.data_json !== undefined ){
			profile_data = abbeyAuthorProfile.data_json;
		}
		countries = profile_data.country; 

		$(document).on( "change", "#profile-form select", function( e ){
			var _this = $(this);
			var name = _this.attr( "name" );
			if( _this.val() === "" ){
				_this.after( "<input type='text' class='other-text' name='"+name+"' />" );
				_this.attr( "name", "" );
			}
			else{
				_this.attr( "name", _this.data( "name" ) ); 
				_this.next( ".other-text" ).remove();
			}
		} );
		

	}); //document.ready //

})( jQuery ); 