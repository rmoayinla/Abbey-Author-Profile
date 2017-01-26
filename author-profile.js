// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		var profile_data = null, countries, states;
		if( abbeyAuthorProfile.data_json !== undefined ){
			profile_data = abbeyAuthorProfile.data_json;
		}
		countries = profile_data.country; 
		
		$("input.quicktags").tagEditor({ 
			initialTags: [], 
    		delimiter: ', '
    	});

		$(document).on( "change", "#profile-form select", function( e ){
			e.preventDefault();
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
		$( document ).on( "change", "#abbey_author_profile_country", function( e ){
			e.preventDefault();
			var _this, _respond, json_key, respondSelect, respondValues, index; 
			_this = $( this );
			_respond = $( "#abbey_author_profile_state" );
			json_key  = _respond.data( "json" );
			respondSelect  = _respond.data( "respond" );

			if( json_key in abbeyAuthorProfile.data_json ){
				respondValues = profile_data[json_key][_this.val()];
				if( respondValues !== undefined ){
					index = "";
					$( ".profile-"+json_key ).val("").html(""); 
					for( index in respondValues  ){
						$( ".profile-"+json_key ).append( "<option value='"+respondValues[index]+"'>"+respondValues[index]+"</option>" );
					}
				}
				
				
				
			}

			//$( "input[type='submit']" ).click();
		});

		$( "input[type='date']" ).datepicker({
			      changeMonth: true,
			      changeYear: true
    	});


		

	}); //document.ready //

})( jQuery ); 