<?php

class PortalStoryExportHtml{
	
	protected $searchExportString;
	protected $exportErrorHtmlCode;
	
	public function __construct(){
		$this->createPortalExportSearchHtml();
		add_shortcode('searchexport_portal', array($this, 'createPortalExportSearchHtml'));
		$this->exportErrorResults();
		add_shortcode('export_errorpage', array($this, 'exportErrorResults'));
	}
	
	public function createPortalExportSearchHtml(){
		$this->searchExportString = '
		<form method="post" class="exportCsvForm" action="/export-results/">
			<div class="form-group">
				 <label>In which project queue are the stories you are searching for?</label>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalProjectType" value="' . $_POST["pivotalProjectType"] . 'Project Id as String' . '" checked> 
							Enterprise Support
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalProjectType" value="' . $_POST["pivotalProjectType"] . 'Project Id as String' . '">
							Enterprise New Development
					</label>	
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalProjectType" value="' . $_POST["pivotalProjectType"] . 'Project Id as String' . '">
							Website New Development
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalProjectType" value="' . $_POST["pivotalProjectType"] . 'Project Id as String' . '">
							Website Support
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalProjectType" value="' . $_POST["pivotalProjectType"] . 'Project Id as String' . '">
							IT/Tech-Support
					</label>
				</div> 
			</div>
			<br />
			<!--<div class="form-group">
				<label>What is the status of the stories you are searching for?</label>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalStoryState" value="' . $_POST["pivotalStoryState"] . '" checked>
							All Stories
					</label>	
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalStoryState" value="' . $_POST["pivotalStoryState"] . 'started' . '"> 
							Started Stories
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="pivotalStoryState" value="' . $_POST["pivotalStoryState"] . 'unstarted' . '">
							Unstarted Stories
					</label>
				</div> 
				<div class="radio">
					<label>
						<input type="radio" name="pivotalStoryState" value="' . $_POST["pivotalStoryState"] . 'delivered' . '">
							Delivered Stories
					</label>
				</div> 
				<div class="radio">
					<label>
						<input type="radio" name="pivotalStoryState" value="' . $_POST["pivotalStoryState"] . 'accepted' . '">
							Accepted Stories
					</label>
				</div> 
			</div>
			<br />-->
			<div class="form-group">
				<label for="searchNameParam" style="margin-bottom: 15px;"><b>Optional:</b> Search by story name.</label>
				<div>
					<input type="text" class="form-control" id="searchNameParam" rows:"1" placeholder="Please type in a search query..." style="resize: none; height: 35px!important;" name="searchNameParam" value="' . $_POST["searchNameParam"] . '"></input>
				</div>
			</div>
			<br />
			<!-- Label Search -->
			<div class="form-group">
				<label for="searchLabelParam" style="margin-bottom: 15px;"><b>Optional:</b> Search by label.</label>
				<div>
					<input type="text" class="form-control" id="searchLabelParam" rows:"1" placeholder="Please type in a search query..." style="resize: none; height: 35px!important;" name="searchLabelParam" value="' . $_POST["searchLabelParam"] . '"></input>
				</div>
			</div>
			<br />
			<div class="form-group">
				<label>Please choose dates to search within for creating reports.</label>
					<div>
						<div style="display: inline-block">
							<label for="start">Start:</label>
							<br>
							<input type="text" id="start" name="pivotalStartDate" value="' . $_POST["pivotalStartDate"] . '">
						</div>
						<div style="width" 25px; display: inline-block;"></div>
						<div style="display: inline-block;">
							<label for="end">End:</label>
							<br>
							<input type="text" id="end" name="pivotalEndDate" value="' . $_POST["pivotalEndDate"] . '">
						</div>
					</div>
			</div>
			<button type="submit" class="btn btn-default" name="submitExportSearch">Download File</button>
		</form>
		';
		
		return $this->searchExportString;
	}
	
	public function exportErrorResults(){
		$this->exportErrorHtmlCode =  '
			<h1>Sorry, your query did not return results. Please search again.</h1><br><br>
			<a  href="/create-reports/"><button class="btn btn-default">Back</button></a>
			<div style="height: 125px;"></div>';
		
	    return $this->exportErrorHtmlCode;
	}
}

new PortalStoryExportHtml();