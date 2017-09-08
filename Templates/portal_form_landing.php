<?php 

class PortalFormLanding {
	
	protected $string;
	protected $testStringString;
	
	public function __construct(){
		$this->createPortalFormHtml();
		add_shortcode('form_landing', array($this, 'createPortalFormHtml'));
	}
	
	public function createPortalFormHtml(){

	$this->string = '
	<div style="height: 25px;"></div>
	<!-- Main Form -->	
	<form method="post" action="" enctype="multipart/form-data" autocomplete="off">
		<div class="form-group">
			 <label>Under which category would this issue fall within?</label>
			<div class="radio">
			<label>
				<input type="radio" name="pivotalProjectName" value="' . $_POST["pivotalProjectName"] . 'Project Name as String' . '" checked>
					Software
			</label>	
			</div>
			<div class="radio">
			<label>
				<input type="radio" name="pivotalProjectName" value="' . $_POST["pivotalProjectName"] . 'Project Name as String' . '"> 
					Websites&nbsp;&nbsp;&nbsp;(New website features, content, and design.)
			</label>
			</div>
			<div class="radio">
			<label>
				<input type="radio" name="pivotalProjectName" value="' . $_POST["pivotalProjectName"] . 'Project Name as String' . '">
					IT/Tech-Support&nbsp;&nbsp;&nbsp;(Computer hardware tech-support, network issues, phones, and all other hardware related issues.)
			</label>
			</div> 
		</div>
		<div class="form-group">
			<label>Is this request for something new you would like added or to fix an issue?</label>
			<div class="radio">
				<label>
				<input type="radio" name="pivotalProjectNameType" value="' . $_POST["pivotalProjectNameType"] . 'feature' . '" checked>
					New
				</label>
			</div>
			<div class="radio">
				<label>
				<input type="radio" name="pivotalProjectNameType" value="' . $_POST["pivotalProjectNameType"] . 'chore' . '"> 
					Fix
				</label>
			</div>
		</div>
		<div class="form-group">
			<label for="subjectArea">Subject of Request</label>
			<textarea class="form-control" id="subjectArea" placeholder="Required*" style="resize: none; height: 35px!important;" name="pivotalName" value="' . $_POST["pivotalName"] . '" required></textarea>
		</div>
			<div class="form-group">
			<label for="describe">Please enter a verbose description of your request.</label>
			<textarea id="describe" class="form-control" name="pivotalDescription" placeholder="Required*" rows="12" value="' . $_POST["pivotalDescription"]  . '" required></textarea>
		</div>
		<div class="form-group">
			<label>Urgency of Request</label>
			<div class="radio">
				<label>
				<input type="radio" name="requestUrgency" value="' . $_POST["requestUrgency"] . 'Normal' . '" checked> 
					Normal
				</label>
			</div>
			<div class="radio">
				<label>
				<input type="radio" name="requestUrgency" value="' . $_POST["requestUrgency"] . 'High Importance' . '"> 
					High Importance
				</label>
			</div>
			<div class="radio">
				<label>
				<input type="radio" name="requestUrgency" value="' . $_POST["requestUrgency"] . 'Urgent' . '" >
					Urgent
				</label>
			</div>
		</div>
		<!-- Add File Uploader Here -->
		<div class="form-group">
			<label>Please upload files or screenshots to be included with the story.</label>
			<div style="height: 5px;"></div>
			<input type="file" name="uploadFilePivotal_A" id="uploadFilePivotal_A-">
			<canvas id="canvasA-"  width="300" height="180" style="background-color:#ffffff; border: 1px solid #666;"></canvas>
			<div style="height: 10px;"></div>
			<label>Please upload another file or screenshot to be included with the story.</label>
			<div style="height: 5px;"></div>
			<input type="file" name="uploadFilePivotal_B" id="uploadFilePivotal_B-">
			<canvas id="canvasB-"  width="300" height="180" style="background-color:#ffffff; border: 1px solid #666;"></canvas>
		</div>
		<div style="height: 25px;"></div>
		<button type="submit" class="btn btn-default" name="submitStoryPhotoComment">Submit</button>
	</form>
	';

return $this->string;
		
	}
}

new PortalFormLanding();

