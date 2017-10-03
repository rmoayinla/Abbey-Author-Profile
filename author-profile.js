/**
 * Abbey Profile Plugin main javascript file 
 * most of the javascript is handled by JQuery 
 * some of the JQuery plugins used are: datepicker, quicktags, repeater etc
 */

//wrapper for JQuery selector //
(function($) {
	//all events are fired when window/document has been loaded i.e. onready event //
	$( document ).ready( function() {
		//plugin global variables //
		var profile_data = null, countries, states;

		if( abbeyAuthorProfile.data_json == undefined ) return false;
		
		//global container for plugin data sent with wp_localize_script //
		profile_data = abbeyAuthorProfile.data_json;
		
		countries = profile_data.country; 
		
		/** Initialize Quicktag plugin for adding tags i.e multiple datas in textarea */
		$(".quicktags").tagEditor({ 
    		delimiter: ','
    	});

    	/**
    	 * Add support for adding additional info to select fields 
    	 * this little snippets is fired when a select field value is changed 
    	 * we check if the new value is empty, if it is we add a text field after the select field for the user
    	 * the user can now add an additional option apart from the available option 
    	 */
		$(document).on( "change", "#profile-form select", function( e ){
			//prevent default action //
			e.preventDefault();
			//clone the current JQuery selector i.e select field to a var //
			var _this = $(this);
			//get the name attribute of this select field //
			var name = _this.attr( "name" );
			//check if the current value of the select field is null, if so add an extra text field after //
			if( _this.val() === "" ){
				_this.after( "<input type='text' class='other-text' name='"+name+"' />" );//insert text field after//
				_this.attr( "name", "" );//remove the name attribute from the select field//
			}
			else{
				_this.attr( "name", _this.data( "name" ) ); //replace the name attribute to the select field //
				_this.next( ".other-text" ).remove();//remove the text field after since we dont need it //
			}
		});

		/**
		 * Add support for creating fields that responds to the option of another field 
		 * example: the state select field will be populated according to the country field 
		 * if the user select a country, the states for that country is fetched and populated\
		 * the data for the select field is stored in the global profile_data var 
		 */
		$( document ).on( "change", "#abbey_author_profile_country", function( e ){
			//prevent default action  //
			e.preventDefault();
			
			//declare our variables //
			var _this, _respond, json_key, respondSelect, respondValues, index; 
			
			//clone the JQuery selector for this field to a var //
			_this = $( this );

			//field to fill in data to when the current field changes //
			_respond = $( "#abbey_author_profile_state" );

			//the key of the respond data e.g. states //
			json_key  = _respond.data( "json" );
			respondSelect  = _respond.data( "respond" );

			//if the respond data key exist in our plugin profile data //
			if( json_key in abbeyAuthorProfile.data_json ){
				//get the values of that key //
				respondValues = profile_data[json_key][_this.val()];
				
				if( respondValues !== undefined ){
					index = "";
					$( ".profile-"+json_key ).val("").html(""); 
					for( index in respondValues  ){
						$( ".profile-"+json_key ).append( "<option value='"+respondValues[index]+"'>"+respondValues[index]+"</option>" );
					}
				}
				
			}
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
	      repeatInput = $( ".repeater-group" ).first();  
	      repeatWrapper = repeatInput.parents( "tr" ).prev( "tr" ).nextUntil( "tbody" ); 
	       
	      addButton = '<button type="button" name="btnAdd" class="button btn-primary btn-add">Add</button>'; 
	      deleteButton = '<button type="button" name="btnDel" class="button btn-danger">Remove</button>'; 
	       
	      repeatWrapper.wrapAll( "<div class='repeater-wrapper'></div>" );
	      //repeatWrapper.addClass( "repeater-wrapper" ); 
	      elemNum = repeatInput.length; 
	      indexNum = parseInt( elemNum - 1 ); 
	 
	      addButtons(); 
	 
	      function addButtons(){ 
	        $( ".repeater-group:first-of-type" ).each( function( index ){ 
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
	        repeatElem = _this.parent().prev( ".repeater-wrapper" ); 
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