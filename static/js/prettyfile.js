/*
 * jQuery and Bootsrap3 Plugin prettyFile
 *
 * version 2.0, Jan 20th, 2014
 * by episage, sujin2f
 * Git repository : https://github.com/episage/bootstrap-3-pretty-file-upload
 */
( function( $ ) {
	$.fn.extend({
		prettyFile: function( options ) {
			var defaults = {
				text : "Select images"
                                , minFiles: 3
                                , required: true
			};

			var options =  $.extend(defaults, options);
			var plugin = this;

			function make_form( $el, text ) {
				$el.wrap('<div></div>');

				$el.hide();
                                var requiredExtra = "";
                                if (options.required) {
                                    requiredExtra = " required ";
                                }
				$el.after( '\
				<div class="input-append input-group">\
					<span class="input-group-btn">\
						<button class="btn btn-outline-success" type="button"><i class="far fa-images"></i>&nbsp;' + text + '</button>\
					</span>\
					<input class="input-large form-control" ' + requiredExtra + ' type="text">\
				</div>\
				' );

				return $el.parent();
			};

			function bind_change( $wrap, multiple ) {
				$wrap.find( 'input[type="file"]' ).change(function () {
					// When original file input changes, get its value, show it in the fake input
					var files = $( this )[0].files, 
                                        
					info = '';
                                        var updateProp = false;

					if ( files.length == 0 )
						return false;

					if ( !multiple || files.length == 1 ) {
                                            if (typeof options.minFiles !== "undefined" && options.minFiles > 1) {
                                                info = "Please select a minimum of " + options.minFiles + " files";
                                                updateProp = true;
                                            } else {
                                                var path = $( this ).val().split('\\');
						info = path[path.length - 1];
                                            }
						
					} else if ( files.length > 1 ) {
						// Display number of selected files instead of filenames
                                                if (options.minFiles > 0 && files.length < options.minFiles) {
                                                    info = "Please select a minimum of " + options.minFiles + " files";
                                                    updateProp = true;
                                                } else {
                                                    info = files.length + ' files selected';
                                                }
						
					}
                                        if (updateProp) {
                                            $wrap.find('.input-append input').attr("placeholder", info );
                                        } else {
                                            $wrap.find('.input-append input').val( info );
                                        }
					
				});
			};

			function bind_button( $wrap, multiple ) {
				$wrap.find( '.input-append' ).click( function( e ) {
					e.preventDefault();
					$wrap.find( 'input[type="file"]' ).click();
				});
			};

			return plugin.each( function() {
				$this = $( this );

				if ( $this ) {
					var multiple = $this.attr( 'multiple' );

					$wrap = make_form( $this, options.text );
					bind_change( $wrap, multiple );
					bind_button( $wrap );
				}
			});
		}
	});
}( jQuery ));
