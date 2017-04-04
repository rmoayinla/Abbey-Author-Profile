// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		var profile_data = null, countries, states;
		if( abbeyAuthorProfile.data_json !== undefined ){
			profile_data = abbeyAuthorProfile.data_json;
		}
		countries = profile_data.country; 
		
		$(".quicktags").tagEditor({ 
    		delimiter: ','
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

		$( document ).on( "focus", "input[type='date']", function( e ) {
			var _this = $( this );
			_this.datepicker({
			     changeMonth: true,
			     changeYear: true,
			     showOn: 'focus'
			}).focus();
    	});
    	

	$(function () { 
      var repeatInput, repeatWrapper, addButton, deleteButton, repeaterForm, elemNum, indexNum; 
      repeatInput = $( ".repeater-group" );  
      repeatWrapper = repeatInput.parents( "table" ); 
       
      addButton = '<button type="button" name="btnAdd" class="button btn-primary btn-add">Add</button>'; 
      deleteButton = '<button type="button" name="btnDel" class="button btn-danger">Remove</button>'; 
       
      repeatWrapper.addClass( "repeater-wrapper" ); 
      elemNum = repeatInput.length; 
      indexNum = parseInt( elemNum - 1 ); 
 
      addButtons(); 
 
      function addButtons(){ 
        $( "tr:first-of-type .repeater-group:first-of-type" ).each( function( index ){ 
	          var _this, parent; 
	          _this = $( this ); 
	          parent = _this.parents( "tbody" ); 
	          parent.find( ".repeater-buttons" ).remove(); 
	          parent.append( "<p class='repeater-buttons btn-"+index+"'>"+addButton+"</p>" ); 
	          if( index > 0 ){ 
	            parent.find( ".repeater-buttons" ).append( deleteButton ); 
	        } 
        }); 
      } 
       
      $( document ).on( "click", ".btn-add", function(e ){ 
        e.preventDefault(); 
        var newElem, _this, repeatElem;  
        _this = $( this ); 
        repeatElem = _this.parents( "tbody" ); 
        newElem = repeatElem.clone( true, true ).fadeIn('slow'); 
 
        newElem.find( ".repeater-group" ).each( function( i ){ 
          var _this, oldName;  
          _this = $( this ); 
          oldName = _this.attr( "name" ).replace(/\[(\d)+\]/g, '['+indexNum+']'); 
          _this.val( null ).attr( "name", oldName ); 
        } ); 
         
        repeatElem.after(newElem); 
        newElem.find( "input" ).filter( ":first" ).focus(); 
 
        addButtons(); 
 
      }); 
 
 
 
    });//function // 

		

	}); //document.ready //

})( jQuery ); 