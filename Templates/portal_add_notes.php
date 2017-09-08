<?php

class PortalAddNotes{
	
	protected $notesString;
	
	public function __construct(){
		$this->addNotesToStory();
		add_shortcode('add_notes', array($this,'addNotesToStory'));
	}
	
	public function addNotesToStory(){
		$this->notesString = '
		<div>
			<button type="button" class="btn btn-default" data-toggle="collapse" data-target="#collapseMenuNotes">Add Note</button>
		</div>
		<div class="collapse" id="collapseMenuNotes">
		<form method="post" action="/submitted-story/" enctype="multipart/form-data">
			<div class="form-group">
				<label for="describe">Please enter a new comment for this story.</label>
				<textarea id="describe" class="form-control" name="pivotalDescription" placeholder="Required*" rows="4" value="' . $_POST["pivotalNote"]  . '"></textarea>
			</div>
			<!-- Add File Uploader Here -->
			<div class="form-group">
				<label>Please upload files or screenshots to be included with this comment.</label>
				<div style="height: 5px;"></div>
				<input type="file" name="uploadFilePivotal_A" id="file">
				<div style="height: 10px;"></div>
				<label>Please upload files or screenshots to be included with this comment.</label>
				<div style="height: 5px;"></div>
				<input type="file" name="uploadFilePivotal_B" id="file">
			</div>
			<div style="height: 25px;"></div>
			<button type="submit" class="btn btn-default" name="submitStoryNewNote">Submit</button>
		</form>
		</div>
		';
		
		return $this->notesString;
	}
}

new PortalAddNotes();