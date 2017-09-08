jQuery(document).ready(function(){
	
		jQuery('.statusIconSpin').ajaxStart(function(){
			jQuery(this).fadeIn(700);
		});
	
	jQuery(document).on('submit', '.notesUploadForm', function(event){

		event.preventDefault();

		var formNoteData = new FormData(this);

		jQuery.ajax({
			type: 'POST', 
			URL: 'pivotal_tracker_api.php', 
			data: formNoteData,
			processData: false, 
			contentType: false,
			success: function(data){
				jQuery('#getSuccessModal').modal('show');
			},
			error: function(data){
				jQuery('#modalFailurePop').modal('show');
			}
		});
		return false;
	});
	
		jQuery('.statusIconSpin').ajaxStop(function(){
			jQuery(this).fadeOut(700);
		});
	
});








