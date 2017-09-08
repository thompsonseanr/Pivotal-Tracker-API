<?php

//	API Integration with cUrl

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php"); 
}

class PivotalTrackerAPI{
	
	protected $apiGETResults;
	protected $apiPOSTResults;
	protected $url = 'https://www.pivotaltracker.com/services/v5';
	protected $projects = 'projects';
	protected $projectIdSearch;
	protected $projectId;
	protected $conditionA;
	protected $conditionB;
	protected $projectIdEntNew = 'Enter Project #';
	protected $projectIdEntSup = 'Enter Project #';
	protected $projectIdWebNew = 'Enter Project #';
	protected $projectIdWebSup = 'Enter Project #';
	protected $projectIdOpSup = 'Enter Project #';
	protected $stories = 'stories';
	protected $storyId;
	protected $storyState;
	protected $comments = 'comments';
	protected $searchQueryLabelName = '?date_format=millis';
	protected $with_state = '&with_state=';
	protected $with_label = '&with_label=';
	protected $created_before = '&created_before=';
	protected $created_before_date;
	protected $created_after = '&created_after=';
	protected $created_after_date;
	protected $search_name_param;
	protected $search_label_param;
	protected $userApi; 
	protected $decodeJsonAPI;
	protected $getDataApi;
	protected $showGETHeader;
	protected $showPOSTHeader;
	protected $postDataApi;
    protected $adminToken = 'Enter Admin Token';
	protected $ownerToken = 'Enter Owner Token';
	protected $jsonHeader = array('Accept: application/json','Content-Type: application/json');
	protected $createStoryArray = array(
		'current_state' => 'unstarted',
		'estimate' => null,
		'name' => null,  
		'description' => null, 
		'labels' => array(
			array(
			'name' => null 
			)
		), 
		'story_type' => null 	
	);
	protected $addPhotoNotesArrayA = array(
		'file' => null
	);
	protected $addPhotoNotesArrayB = array(
		'file' => null
	);
	protected $addEmailNotesArray = array(
		'file' => null
	);
	protected $addCommentNotesArray = array(
		'text' => '',
	);
	protected $encodecreateStoryArray;
	protected $wpPOST;
	protected $wpPOSTResponse;
	protected $arrayArgs;
	protected $errorLog;
	protected $curlPivotalCreateStoryResponse;
	protected $curlSearch;
	protected $curlSearchResponse;
	protected $chGetStory;
	protected $chPostStory;
	protected $chPostNotes;
	protected $jsonDecodeStoryResults;
	protected $requestUrgencyForm;
	protected $jsonEncodedPhotoResponse;
	protected $chPostPhoto;
	protected $chPostEmail;
	protected $showPhotoHeader;
	protected $showPhotoResultsA;
	protected $showPhotoResultsB;
	protected $showEmailResults;
	protected $jsonDecodedPhotoResponseA;
	protected $jsonDecodedPhotoResponseB;
	protected $jsonDecodedEmailResponse;
	protected $userUploadFileA;
	protected $userUploadFileB;
	protected $userUploadEmail;
	protected $storyCreatePostHeader;
	protected $storyCreatePostResults;
	protected $photoTitleId;
	protected $jsonDecodedPhotoResponse;
	protected $incrementVal;
	protected $resultInt;
	protected $returnInt;
	protected $storyIdReturn;
	protected $projectIdReturn;
	protected $storyNameReturn;
	protected $storyDescriptionReturn;
	protected $storyUrlReturn;
	protected $searchExportArray = array(
	'story_ids' => null
	);
	protected $searchExportArrayJsonEncoded;
	protected $curlApiCsv;
	protected $infoCurlResponse;
	protected $jsonDecodeCsvFile;
	protected $curlHttpErrorNum;
	protected $curlSentStoryPt;
	protected $dateConsersionStartFix;
	protected $dateConsersionEndFix;
	protected $dateConversionCurrentFix;
	protected $projectIdEmail;
	protected $curlEmailResponse;
	protected $dateConversion;
	protected $dateConversionFix;
	protected $dateConversonIso;
	protected $created_after_dateIso;
	protected $created_before_dateIso;
	protected $curlEmailErrorDiag = null;
	protected $curlUploadErrorDiag_A = null;
	protected $curlUploadErrorDiag_B = null;
	protected $curlStoryErrorDiag = null;
	protected $addPhotoNotesArrayAQuery;


	public function __construct(){
		//User Name 
		add_action('plugins_loaded', array($this, 'userNameForApi'));
		$this->userNameForApi();
		
		// Enabling Session Variables 
		add_action('init', array($this, 'createSessionId'));
		$this->createSessionId();
		
		//cUrl Methods
		
		/*
		 *	Important! Keep for Comment Creation 
		 */
		$this->returnInt = $_POST['hiddenInt'];
		
		/*
		 *	Search Stories
		 */
		if (isset($_POST['submitSearch'])) :
		$this->projectIdSearch = $_POST['pivotalProjectType'];
		$this->storyState = $_POST['pivotalStoryState'];
		$this->created_after_date = $_POST['pivotalStartDate'];
		$this->created_before_date = $_POST['pivotalEndDate'];
		$this->search_name_param = $_POST['searchNameParam'];
		$this->search_label_param = $_POST['searchLabelParam'];
		
		//Date Conversion 
		$dateConversionStart = substr($_POST['pivotalStartDate'], 0, 10);
		$this->dateConsersionStartFix = date("n/j/Y", strtotime($dateConversionStart));
		$dateConversionEnd = substr($_POST['pivotalEndDate'], 0, 10);
		$this->dateConsersionEndFix = date("n/j/Y", strtotime($dateConversionEnd));
		
		$this->dateConversionCurrentFix = date("n/j/Y", strtotime($dateConversionCurrentFix.'+1 day'));
		
		$urlDate = $this->url.'/'.$this->projects.'/'.$this->projectIdSearch .'/'.'iterations?scope=current_backlog'; 	
		
		$this->curlSearchAPI($urlDate);
		
		endif;
		
		/*
		 *	Story Report Export
		 */
		if (isset($_POST['submitExportSearch'])) :
		
		$this->projectIdSearch = $_POST['pivotalProjectType'];
		$this->storyState = $_POST['pivotalStoryState'];
		$this->search_name_param = $_POST['searchNameParam'];
		$this->search_label_param = $_POST['searchLabelParam'];
		$this->created_after_date = $_POST['pivotalStartDate'];
		$this->created_before_date = $_POST['pivotalEndDate'];

		$urlDate = $this->url.'/'.$this->projects.'/'.$this->projectIdSearch.'/'.'iterations?scope=current_backlog';
		
		$this->curlSearchDateAPI($urlDate);
		
		endif;
		
		/*
		 *	Creating Story with Email and File Uploads Into Notes
		 */
		if (isset($_POST['submitStoryPhotoComment'])) :
		
		//1) Upload File to Wordpress and Send to API -- Decode Response
		$this->conditionA = $_POST['pivotalProjectName'];
		$this->conditionB = $_POST['pivotalProjectNameType'];
		
		if($this->conditionA == 'software' && $this->conditionB == 'feature'){
			$this->projectId = 'Enter Project Id As String'; 
			$this->projectIdEmail = 'Enter Project Name As String';
		}
		elseif($this->conditionA == 'software' && $this->conditionB == 'chore'){
			$this->projectId = 'Enter Project Id As String';
			$this->projectIdEmail = 'Enter Project Name As String';
		}
		elseif($this->conditionA == 'websites' && $this->conditionB == 'feature'){
		    $this->projectId = 'Enter Project Id As String';
			$this->projectIdEmail = 'Enter Project Name As String';
		}
		elseif($this->conditionA == 'websites' && $this->conditionB == 'chore'){
			$this->projectId = 'Enter Project Id As String';
			$this->projectIdEmail = 'Enter Project Name As String';
		}
		elseif($this->conditionA == 'it_tech' && $this->conditionB == 'feature'){
			$this->projectId = 'Enter Project Id As String';
			$this->projectIdEmail = 'Enter Project Name As String';
		}
		elseif($this->conditionA == 'it_tech' && $this->conditionB == 'chore'){
			$this->projectId = 'Enter Project Id As String';
			$this->projectIdEmail = 'Enter Project Name As String';
		}
		
		$this->incrementjobnumbers();	
		
		$this->requestUrgencyForm = $_POST['requestUrgency'];
		$this->updateStoryArray();
		
		/* .msg Creation */
		$this->createMsgFile();
		$this->pullMsgFile();
		$this->killMsgFile();
		
		$this->addEmailNotesArray['file'] = '@'.$this->userUploadEmail;
		$this->curlAddEmailPivotalServer();
		$this->jsonDecodedEmailResponse = json_decode($this->showEmailResults);	
		$this->createStoryArray['comments'][0]['text'] = 'Please see email attachment'; 		
		$this->createStoryArray['comments'][0]['file_attachments'][0] = $this->jsonDecodedEmailResponse;

		if (file_exists($_FILES["uploadFilePivotal_A"]["tmp_name"]) || is_uploaded_file($_FILES["uploadFilePivotal_A"]["tmp_name"])) : 
		$this->uploadFileToWordpress();
		$this->addPhotoNotesArrayA['file'] = '@'.$this->userUploadFileA;
		$this->curlAddPhotoPivotalServerA();   
		$this->jsonDecodedPhotoResponseA = json_decode($this->showPhotoResultsA);	
		$this->createStoryArray['comments'][0]['text'] = 'Please see attachment'; 		
		$this->createStoryArray['comments'][0]['file_attachments'][1] = $this->jsonDecodedPhotoResponseA; 
		endif;
		
		if (file_exists($_FILES["uploadFilePivotal_B"]["tmp_name"]) || is_uploaded_file($_FILES["uploadFilePivotal_B"]["tmp_name"])) : 
		$this->uploadSecondFileToWordpress();
		$this->addPhotoNotesArrayB['file'] = '@'.$this->userUploadFileB;
	    $this->curlAddPhotoPivotalServerB();
		$this->jsonDecodedPhotoResponseB = json_decode($this->showPhotoResultsB);
		$this->createStoryArray['comments'][0]['file_attachments'][2] = $this->jsonDecodedPhotoResponseB; 
		endif;
		
		$this->curlCreateStory();
		
		$_SESSION['curlSentStoryPt'] = $this->createStoryArray;
		$_SESSION['conditionA'] = $this->conditionA; 
		$_SESSION['conditionB'] = $this->conditionB; 
		$_SESSION['storyPostResultsSession'] = $this->storyCreatePostResults;
		$_SESSION['curlHttpErrorNum'] = $this->curlHttpErrorNum; 
		$_SESSION['curlEmailAttachment'] = $this->showEmailResults;
		$_SESSION['curlAttachmentA'] = $this->showPhotoResultsA;
		$_SESSION['curlAttachmentB'] = $this->showPhotoResultsB;
		$_SESSION['curlUploadErrorDiag_A'] = $this->curlUploadErrorDiag_A;
		$_SESSION['curlUploadErrorDiag_B'] = $this->curlUploadErrorDiag_B;
		$_SESSION['curlEmailErrorDiag'] = $this->curlEmailErrorDiag;
		$_SESSION['curlStoryErrorDiag'] = $this->curlStoryErrorDiag;
		
		if ($this->curlHttpErrorNum !== 200){
			$this->fireOffEmailFailure();
			$this->diagnosticEmailFailure();
		}
		else{
			$this->fireOffEmail();
		}
		
		/* Delete Email Files After Upload to PT and Email Generation */
		if (file_exists($this->userUploadFileA)){
			unlink($this->userUploadFileA);
		}
		if (file_exists($this->userUploadFileB)){
			unlink($this->userUploadFileB);
		}
		unlink($this->userUploadEmail);
		
		header('Location: /submitted-story/');

		exit;

		endif;
		
		/*
		 *	ADD COMMENT AND/OR PHOTO TO STORY
		 */

	    if(isset($_POST['pivotalNote'])):

		$this->storyId = $_POST['storyIdReturn'];
		$this->projectId = $_POST['projectIdReturn'];
		$this->storyNameReturn = $_POST['storyNameReturn'];
		$this->storyDescriptionReturn = $_POST['storyDescriptionReturn'];
		$this->storyUrlReturn = $_POST['storyUrlReturn'];
		
		// 1)Uploading Files to PT and Return Results -- If Exist
		if (file_exists($_FILES["uploadFilePivotal_A"]["tmp_name"]) || is_uploaded_file($_FILES["uploadFilePivotal_A"]["tmp_name"])) : 
		$this->uploadFileToWordpress();
		$this->addPhotoNotesArrayA['file'] = '@'.$this->userUploadFileA;
		$this->curlAddPhotoPivotalServerA();
		$this->jsonDecodedPhotoResponseA = json_decode($this->showPhotoResultsA);
		$this->addCommentNotesArray['file_attachments'][0] = $this->jsonDecodedPhotoResponseA;
		endif;
		
		if (file_exists($_FILES["uploadFilePivotal_B"]["tmp_name"]) || is_uploaded_file($_FILES["uploadFilePivotal_B"]["tmp_name"])) : 
		$this->uploadSecondFileToWordpress();
		$this->addPhotoNotesArrayB['file'] = '@'.$this->userUploadFileB;
	    $this->curlAddPhotoPivotalServerB();
		$this->jsonDecodedPhotoResponseB = json_decode($this->showPhotoResultsB);
		$this->addCommentNotesArray['file_attachments'][1] = $this->jsonDecodedPhotoResponseB;
		endif;
		
		// 2) Create Array and jsonEncode
		
		$this->addCommentNotesArray['text'] = $_POST['pivotalNote'];
		
		$this->curlCreateStoryNotes();
		$this->fireOffEmailComments();
		endif;
		/*
		 *	END ADD COMMENT AND/OR PHOTO TO STORY
		 */
		
		
		//User Name Shortcodes
		add_shortcode('user_name', array($this, 'helloUserHtml'));
		
		//API Response Shortcodes
		add_shortcode('story_create', array($this, 'displayStoryCreation'));
		add_shortcode('story_create_test', array($this, 'displayStoryCreationTest'));
		
		//API Search Results Shortcodes
		add_shortcode('search_results', array($this, 'displaySearchResults'));
		add_shortcode('search_results_test', array($this, 'displaySearchResultsTest'));
		
		//Testing Responses Shortcodes 
		add_shortcode('display_post', array($this, 'postAPIData'));
		add_shortcode('display_results', array($this, 'retrieveAPIData'));
		add_shortcode('display_testhtml', array($this, 'testHtml'));
		
		//PopUp Code
		add_shortcode('modal_popup', array($this, 'modalPopUps'));
	
	}
	

	//Session Start -- Required for $_SESSION
	public function createSessionId(){
		if (!session_id()){
		 session_start();
		}
	}
	
	//GET-- Stories By Story ID
	public function curlGetStoryById(){
		$this->chGetStory = curl_init($this->testUrl);
		curl_setopt($this->chGetStory, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($this->chGetStory, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->chGetStory, CURLOPT_HTTPHEADER, array(
			'X-TrackerToken: ' . $this->adminToken
		));
		curl_setopt($this->chGetStory, CURLINFO_HEADER_OUT, true);
		$this->apiGETResults = curl_exec($this->chGetStory);
		curl_close($this->chGetStory);
	}

	//POST-- Create Stories 
	public function curlCreateStory(){
		$this->encodecreateStoryArray = json_encode($this->createStoryArray);
		$this->chPostStory = curl_init($this->url.'/'.$this->projects.'/'.$this->projectId.'/'.$this->stories);
		curl_setopt($this->chPostStory, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($this->chPostStory, CURLOPT_POSTFIELDS, $this->encodecreateStoryArray);                                           
		curl_setopt($this->chPostStory, CURLOPT_RETURNTRANSFER, 1);                                                                      
		curl_setopt($this->chPostStory, CURLOPT_HTTPHEADER, array(     
		'X-TrackerToken: ' . $this->adminToken, 
		'Content-Type: application/json',
		'Accept: application/json',		
		'Content-Length: ' . strlen($this->encodecreateStoryArray))                                                                       
		); 
		curl_setopt($this->chPostStory, CURLINFO_HEADER_OUT, 1);
		curl_setopt($this->chPostStory, CURLOPT_VERBOSE, 1);
		
		$this->storyCreatePostResults = curl_exec($this->chPostStory);
		$this->curlHttpErrorNum = curl_getinfo($this->chPostStory, CURLINFO_HTTP_CODE);

		//Error Diag
		if (curl_error($this->chPostStory)) {
		  $this->curlStoryErrorDiag = curl_error($this->chPostStory);
		} 

		curl_close($this->chPostStory);
		return $this->storyCreatePostResults;
	}
	
	//POST Add Images to Pivotal Tracker
	public function curlAddEmailPivotalServer(){
		$this->chPostEmail = curl_init($this->url.'/'.$this->projects.'/'.$this->projectId.'/'.'uploads');
		curl_setopt($this->chPostEmail, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($this->chPostEmail, CURLOPT_POSTFIELDS, $this->addEmailNotesArray);	 
		curl_setopt($this->chPostEmail, CURLOPT_VERBOSE, 1);
		curl_setopt($this->chPostEmail, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->chPostEmail, CURLOPT_HTTPHEADER, array(
		'X-TrackerToken: ' . $this->adminToken
			)
		);
		curl_setopt($this->chPostEmail, CURLINFO_HEADER_OUT, 1);
		$this->showPhotoHeader = curl_getinfo($this->chPostEmail, CURLINFO_HEADER_OUT);
		$this->showEmailResults = curl_exec($this->chPostEmail);
		curl_close($this->chPostEmail);
		return $this->showEmailResults;
	}
	
	public function curlAddPhotoPivotalServerA(){
		$this->chPostPhoto = curl_init($this->url.'/'.$this->projects.'/'.$this->projectId.'/'.'uploads'); 
		curl_setopt($this->chPostPhoto, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($this->chPostPhoto, CURLOPT_POSTFIELDS, $this->addPhotoNotesArrayA);	 
		curl_setopt($this->chPostPhoto, CURLOPT_VERBOSE, 1);
		curl_setopt($this->chPostPhoto, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->chPostPhoto, CURLOPT_HTTPHEADER, array(
		'X-TrackerToken: ' . $this->adminToken
			)
		);
		curl_setopt($this->chPostPhoto, CURLINFO_HEADER_OUT, 1);
		$this->showPhotoHeader = curl_getinfo($this->chPostPhoto, CURLINFO_HEADER_OUT);
		$this->showPhotoResultsA = curl_exec($this->chPostPhoto);

		//Error Diag
		if(curl_error($this->chPostPhoto)){
			$this->curlUploadErrorDiag_A = curl_error($this->chPostPhoto);
		}

		curl_close($this->chPostPhoto);
		return $this->showPhotoResultsA;
	}
	public function curlAddPhotoPivotalServerB(){
		$this->chPostPhoto = curl_init($this->url.'/'.$this->projects.'/'.$this->projectId.'/'.'uploads');
		curl_setopt($this->chPostPhoto, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($this->chPostPhoto, CURLOPT_POSTFIELDS, $this->addPhotoNotesArrayB);	 
		curl_setopt($this->chPostPhoto, CURLOPT_VERBOSE, 1);
		curl_setopt($this->chPostPhoto, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->chPostPhoto, CURLOPT_HTTPHEADER, array(
		'X-TrackerToken: ' . $this->adminToken
			)
		);
		curl_setopt($this->chPostPhoto, CURLINFO_HEADER_OUT, 1);
		$this->showPhotoHeader = curl_getinfo($this->chPostPhoto, CURLINFO_HEADER_OUT);
		$this->showPhotoResultsB = curl_exec($this->chPostPhoto);

		//Error Diag
		if(curl_error($this->chPostPhoto)){
			$this->curlUploadErrorDiag_B = curl_error($this->chPostPhoto);
		}

		curl_close($this->chPostPhoto);
		return $this->showPhotoResultsB;
	}
	
	//POST-- Add Notes
	public function curlCreateStoryNotes(){
		$this->encodecreateStoryArray = json_encode($this->addCommentNotesArray);
		$this->chPostNotes = curl_init($this->url.'/'.$this->projects.'/'.$this->projectId.'/'.$this->stories.'/'.$this->storyId.'/comments');
		curl_setopt($this->chPostNotes, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($this->chPostNotes, CURLOPT_POSTFIELDS, $this->encodecreateStoryArray);                                                   
		curl_setopt($this->chPostNotes, CURLOPT_RETURNTRANSFER, 1);                                                                      
		curl_setopt($this->chPostNotes, CURLOPT_HTTPHEADER, array(     
		'X-TrackerToken: ' . $this->adminToken, 
		'Content-Type: application/json',
		'Accept: application/json',		
		'Content-Length: ' . strlen($this->encodecreateStoryArray))                                                                       
		); 
		$this->apiPOSTResults = curl_exec($this->chPostNotes);
		curl_close($this->chPostNotes);
		return $this->apiPOSTResults;
	}
	
	//GET-- Search Stories By Label
	public function curlSearchAPI($url){
		$this->curlSearch = curl_init($url);
		curl_setopt($this->curlSearch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($this->curlSearch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curlSearch, CURLOPT_HTTPHEADER, array(
			'X-TrackerToken: ' . $this->adminToken, 
			'Content-Type: application/json',
			'Accept: application/json'
		));
		curl_setopt($this->curlSearch, CURLINFO_HEADER_OUT, true);
		$this->curlSearchResponse = curl_exec($this->curlSearch);
		curl_close($this->curlSearch);
		return $this->curlSearchResponse;
	}
	
	/* GET-- Search Stories with Label and by date and create CSV Report */
	public function curlSearchDateAPI($url){
		/* Curl Actions */
		$this->curlSearch = curl_init($url);
		curl_setopt($this->curlSearch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($this->curlSearch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curlSearch, CURLOPT_HTTPHEADER, array(
			'X-TrackerToken: ' . $this->adminToken, 
			'Content-Type: application/json'
		));
		curl_setopt($this->curlSearch, CURLOPT_VERBOSE, true);
		curl_setopt($this->curlSearch, CURLINFO_HEADER_OUT, true);
		$this->curlSearchResponse = curl_exec($this->curlSearch);
		curl_close($this->curlSearch);
		
		/* Create and write to csv */
		$this->jsonDecodeCsvFile = json_decode($this->curlSearchResponse, true);
		if(!empty($this->jsonDecodeCsvFile)){
		/* Create CSV */
		$f = plugin_dir_path( __FILE__ ).'pivotal_report_inenvi.csv';
		$file = fopen($f, "w+");
		/* $titleArray = array("Status","Title","Type","Created At","Description"); */
		$titleArray = array("Pivotal Id","Title","Created At", "Labels","Current Status","Description");
		fputcsv($file, $titleArray, ',');
		fclose($file);
		$fileAppend = fopen($f, "a");
		
		/* Descending Order By Date */
		$dateArrayA = array();
		$dateArrayB = array();
		$labelsArray = array();
		
		if(!empty($this->created_after_date)){
			$this->created_after_dateIso = date("U", strtotime($this->created_after_date));
			}
		
		if(!empty($this->created_before_date)){
			$this->created_before_dateIso = date("U", strtotime($this->created_before_date));
			}
		
		foreach($this->jsonDecodeCsvFile as $key => $value){
			foreach($value as $keyA => $valueA){
				foreach($valueA as $keyB => $valueB){
					
					/* Simplify and Labels Nested Array */
					$labelsArray = array();
					foreach($valueB as $valueC){
						foreach($valueC as $valueD){
							$labelsArray[] = $valueD['name'];
							$labelsCleaned = implode(', ', array_filter($labelsArray));
						}
					}
					  
					/* Date Conversion and Simplified Sortable Array */
					$this->dateConversion = substr($valueB['created_at'], 0, 10);   
					$this->dateConversionFix = date("m/d/Y", strtotime($this->dateConversion));
					$this->dateConversonIso = date("U", strtotime($this->dateConversion));
					
					$dateArrayB[] = $valueB['created_at'];
					
					$dateArrayA[] = array('project_id' => $valueB['project_id'],
										'created_at' => $this->dateConversionFix, 
										'date_created_iso' => $this->dateConversonIso,
										'name' => $valueB['name'],
										'description' => $valueB['description'], 
										'story_type' => $valueB['story_type'], 
										'current_state' => $valueB['current_state'], 
										'id' => $valueB['id'], 
										'url' => $valueB['url'], 
										'labels' => $labelsCleaned, 
										'labels_array' => array_filter($labelsArray)
										);
				}
			}
		}
		
		/* Sort Results Descending By Date */
		array_multisort($dateArrayB, SORT_DESC, $dateArrayA);
		
		$noResults = '<h1>Sorry, no results.</h1><br><br><a  href="/search-for-stories/"><button class="btn btn-default">Back</button></a>';
		
		/* Exluding for Incorp Reporting */
		$excludeDefaults = array('Add String Values to Exclude');

		$dateArrayA = array_filter($dateArrayA, function($newFilterValue) use ($excludeDefaults){			
			if (array_intersect($excludeDefaults, $newFilterValue['labels_array'])){
				unset($newFilterValue);
			}
			else{
				return $newFilterValue;
			}
		});
		
		/* Filter $dataArrayA based upon Story State: Unstarted, Started, Completed, Etc. */
		$storyStatus = array('accepted', 'started', 'unstarted');
		
		if ( !empty($this->storyState)){
			$dateArrayA = array_filter($dataArrayA, function($newFilterValue) use ($storyStatus){
				if(array_intersect($storyStatus, $newFilterValue['current_state'])){
					return array_intersect($storyStatus, $newFilterValue['current_state']);
				}
				else{
					unset($newFilterValue);
				}
			});
		}

		$searchName = $this->search_name_param;
		$searchLabel = strtolower($this->search_label_param);
		
		if (strpos($searchLabel, ',') || strpos($searchLabel, ".")){
			$searchLabelExplode = str_replace(array(',', '.'), ' ', $searchLabel);
			$searchLabelExplode = array_filter(explode(' ', $searchLabelExplode));
		}
		else{
			$searchLabelExplode = explode(' ', $searchLabel);
		}

		$searchLabelCount = count($searchLabelExplode);  

		if (!empty($this->search_name_param && $this->search_label_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName, $searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
				return array_intersect($searchLabelExplode, $newFilterValue['labels_array'])
				&& preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
				}
			});
			if (!empty($filterArray)) { echo "all param";}
			elseif (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_label_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
				return array_intersect($searchLabelExplode, $newFilterValue['labels_array'])
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue){
				return $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		} 
		
		elseif (!empty($this->created_after_dateIso && $this->search_name_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso && $this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return array_intersect($searchLabelExplode, $newFilterValue['labels_array']) 
					&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param && $this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName, $searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return preg_match("/\b$searchName\b/i", $newFilterValue['name'])
					&& array_intersect($searchLabelExplode, $newFilterValue['labels_array']);
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue){    
				return $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']);
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}

		elseif (!empty($this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return array_intersect($searchLabelExplode, $newFilterValue['labels_array']);  
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		else{
			$filterArray = $dateArrayA;
		}

		foreach($filterArray as $key){
			$createdAt = $key['created_at'];
			$dateConvert = substr($createdAt, 0, 10);
			/* // $line = array($value['current_state'], $value['name'], $value['story_type'], $dateConvert, $value['description']); */
			$line = array($key['id'], $key['name'], $dateConvert, $key['labels'], $key['current_state'], $key['description'] );
				$fixArray = array('/(’)+/', '/(“)+/', '/(”)+/', '/(…)+/');
				$replaceArray = array('\'', '"', '"', '...');
				$replacePreg = preg_replace($fixArray, $replaceArray, $line);
			fputcsv($fileAppend,$replacePreg, ',');
		}
		fclose($fileAppend);
		// /* Send File  */
		header("Content-Description: File Transfer");
		header("Content-Type: text/csv"); 
	    header("Content-Disposition: attachment; filename=pivotal_report_inenvi.csv");
		readfile(plugin_dir_path( __FILE__ )."pivotal_report_inenvi.csv");
		unlink(plugin_dir_path( __FILE__ )."pivotal_report_inenvi.csv");
		exit;
		} 

		return $this->curlSearchResponse;
	}
	
	//POST CSV of Stories
	public function curlStoryExportAPI($exportCsvUrl){
	$this->searchExportArrayJsonEncoded = json_encode($this->searchExportArray);
	$this->curlApiCsv = fopen(plugin_dir_path( __FILE__ ).'pivotal_report.csv', "w+");
	$ch = curl_init($exportCsvUrl);
	$timeout = 20;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'X-TrackerToken: ' . $this->adminToken, 
		'Content-Type: application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $this->searchExportArrayJsonEncoded);  
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_FILE, $this->curlApiCsv);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $this->curlSearchResponse = curl_exec($ch);
	curl_close($ch);

	//This is required to force download of file
    header("Content-Type: text/csv"); 
	header("Content-Disposition: attachment;");
	header("Content-Description: File Transfer");
	header("Content-Type: text/csv"); 
	header("Content-Disposition: attachment; filename=pivotal_report.csv");
	readfile(plugin_dir_path( __FILE__ )."pivotal_report.csv");
	unlink(plugin_dir_path( __FILE__ )."pivotal_report.csv");
	exit;

	return $this->curlSearchResponse;

	}
	
	/*****************************************************************************
	 *
	 *	Relies upon http://www.independentsoft.de/msg/ .msg plugin
	 *
	 *****************************************************************************/
	 
	/*
	 *	POST and Create .msg with Webservice 
	 */
	 
	 public function createMsgFile(){
		$curlEmailMsg = curl_init();
		  
		  $emailPostFields = array();
		  $emailPostFields['Action'] = urlencode("create");
		  $emailPostFields['DisplayName'] = urlencode($this->userApi->display_name);
		  $emailPostFields['EmailAddress'] = urlencode($this->userApi->user_email);
		  $emailPostFields['Subject'] = urlencode('New ') . urlencode($this->createStoryArray['name']) . urlencode(' in ') .  urlencode($this->projectIdEmail);
		  $emailPostFields['Body'] = urlencode('<b>Story Title: </b>') . urlencode($this->createStoryArray['name']) . urlencode('<br/><br/>') . 
		  urlencode('<b>Description: </b>') . urlencode($this->createStoryArray['description']) . urlencode('<br/><br/>') . 
		  urlencode('<b>Story Type: </b>Feature') . urlencode('<br/><br/>') . 
		  urlencode('<b>Requested By: </b>') . urlencode($this->userApi->user_firstname) . urlencode(' ') . urlencode($this->userApi->user_lastname) . urlencode('<br/><br/>') . 
		  urlencode('<b>Current Status: </b>') . urlencode(ucwords($this->createStoryArray['current_state'])) . urlencode('<br/><br/>') . 
		  urlencode('<b>Project Queue: </b>') . urlencode($this->projectIdEmail) . urlencode('<br/><br/>') .
		  urlencode('<b>Created On: </b>') . urlencode(date("m/d/y")) . urlencode('<br/><br/>') .
		  urlencode('<b>Request Urgency: </b>') . urlencode($_POST['requestUrgency']) . urlencode('<br/><br/>'); 
		  
		curl_setopt_array($curlEmailMsg, array(
		  CURLOPT_URL => "Web Service End Point",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POST => true, 
		  CURLOPT_POSTFIELDS => $emailPostFields,
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: multipart/form-data"
			),
		));

		$this->curlEmailResponse = curl_exec($curlEmailMsg);

		//Error Diag
		if(curl_error($curlEmailMsg)){
			$this->curlEmailErrorDiag = curl_error($curlEmailMsg);
		}

		curl_close($curlEmailMsg);
		
		return $this->curlEmailResponse;
	 }
	 
	/* 
	 *	Pull .msg From Webservice
	 */
	 
	 public function pullMsgFile(){
		$emailSourceUrl = $this->curlEmailResponse;
		$curlEmailDownload = curl_init();
		curl_setopt($curlEmailDownload, CURLOPT_URL, $emailSourceUrl);
		curl_setopt($curlEmailDownload, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlEmailDownload, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curlEmailDownload, CURLOPT_SSL_VERIFYHOST, false);
		$data = curl_exec ($curlEmailDownload);
		$error = curl_error($curlEmailDownload); 
		curl_close ($curlEmailDownload);

		/* Create .msg file on server */
		$this->userUploadEmail = '/home/admin/public_html/temp_files_upload/PT_Ticket_#'.$this->incrementVal.'_email.msg';
		$file = fopen($this->userUploadEmail, "w+");
		fputs($file, $data);
		fclose($file);
	 }
	 
	 /* 
	  *	Delete .msg from Webservice Server
	  */
	 public function killMsgFile(){
		$curlKillMsg = curl_init();
		  
		  $emailPostFields = array();
		  $emailPostFields['Action'] = urlencode('kill');
		  $emailPostFields['filename'] = urlencode($this->curlEmailResponse);
		  
		curl_setopt_array($curlKillMsg, array(
		  CURLOPT_URL => "https://www.incorp.com/webservice_createoutlookmsg.aspx",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POST => true, 
		  CURLOPT_POSTFIELDS => $emailPostFields,
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: multipart/form-data"
			),
		));

		$this->curlKillResponse = curl_exec($curlKillMsg);
		$err = curl_error($curlKillMsg);

		curl_close($curlKillMsg);
	 }
	 
	
	public function userNameForApi(){
		$this->userApi = wp_get_current_user();
	}
	

	//Construct Story Array
	public function updateStoryArray(){
		$this->createStoryArray['name'] = 'PT Ticket: #' . $this->incrementVal . ' ' . $_POST['pivotalName'];
		$this->createStoryArray['description'] = $_POST['pivotalDescription'];
		$this->createStoryArray['labels'][0]['name'] = $this->userApi->user_firstname;
		$this->createStoryArray['story_type'] = $_POST['pivotalProjectNameType'];
	}
	
	//Construct Story Array Test
	public function updateStoryArrayTest(){
		$this->createStoryArrayTest['name'] = 'PT Ticket: #' . $this->incrementVal . ' ' . $_POST['pivotalName'];
		$this->createStoryArrayTest['description'] = $_POST['pivotalDescription'];
		$this->createStoryArrayTest['labels'][0]['name'] = $this->userApi->user_firstname;
		$this->createStoryArrayTest['story_type'] = $_POST['pivotalProjectNameType'];
	}
	
	
	//Display Story Creation 
	public function displayStoryCreation(){		
		// echo $_SESSION['storyPostResultsSession'];
		
		if ($_SESSION['curlHttpErrorNum'] == 200){
			// $this->jsonDecodeStoryResults = json_decode($this->storyCreatePostResults, true); 
			$this->jsonDecodeStoryResults = json_decode($_SESSION['storyPostResultsSession'], true); 
			
			if($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
				$project_id_inenvi_2 = 'Project Name as String';
			}
			elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
				$project_id_inenvi_2 = 'Project Name as String';
			}
			elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
				$project_id_inenvi_2 = 'Project Name as String';
			}
			elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
				$project_id_inenvi_2 = 'Project Name as String';
			}
			elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
				$project_id_inenvi_2 = 'Project Name as String';
			}
			
			//Date Fix
			$dateConversion = substr($this->jsonDecodeStoryResults['created_at'], 0, 10);
			$dateConsersionFix = date("n-j-Y", strtotime($dateConversion));
			
			echo '<div class="container" style="border: 1px solid #666;">';
			echo '<div style="height: 25px;"></div>';
			echo '<div><p><b>Story Title:</b>&nbsp;&nbsp;&nbsp;' . $this->jsonDecodeStoryResults['name'] . '</p></div>';
			echo '<div><p><b>Description:</b>&nbsp;&nbsp;&nbsp;' . $this->jsonDecodeStoryResults['description'] . '</p></div>';
			echo '<div><p><b>Story Type:</b>&nbsp;&nbsp;&nbsp;' . ucwords($this->jsonDecodeStoryResults['story_type']) . '</p></div>';
			echo '<div><p><b>Requested By:</b>&nbsp;&nbsp;&nbsp;' . $this->userApi->user_firstname . ' ' . $this->userApi->user_lastname . '</p></div>';
			echo '<div><p><b>Current Status:</b>&nbsp;&nbsp;&nbsp;' . ucwords($this->jsonDecodeStoryResults['current_state']) . '</p></div>';
			echo '<div><p><b>Story Id:</b>&nbsp;&nbsp;&nbsp;' . $this->jsonDecodeStoryResults['id'] . '</p></div>';
			echo '<div><p><b>Pivotal Tracker Story Link:</b>&nbsp;&nbsp;&nbsp;' . '<a href="'.$this->jsonDecodeStoryResults['url'] .'" target="_blank">' . $this->jsonDecodeStoryResults['url']  . '</a>' . '<p></div>';
			echo '<div><p><b>Project Queue:</b>&nbsp;&nbsp;&nbsp;' . $project_id_inenvi_2 . '</p></div>';
			echo '<div><p><b>Created On:</b>&nbsp;&nbsp;&nbsp;' . $dateConsersionFix . '</p></div>';
			echo '<div style="height: 25px;"></div>';
			echo '</div>';
			}
		
		// On failure 
		if (empty($this->jsonDecodeStoryResults['name']) && $_SESSION['curlHttpErrorNum'] != 200 && !empty($_SESSION['curlHttpErrorNum'])) {
			// $_SESSION['curlSentStoryPt']
			// $this->jsonDecodeStoryResults = json_decode($_SESSION['storyPostResultsSession'], true); 
			echo '<h4>We are sorry, but your story was not submitted to Pivotal Tracker. We have recieved an email notification and your story will be created by our project manager.</h4>';
			echo '<div style="height: 10px;"></div>';
			echo '<div class="container" style="border: 1px solid #666;">';
			echo '<div style="height: 25px;"></div>';
			echo '<div><p><b>Story Title:</b>&nbsp;&nbsp;&nbsp;' . $_SESSION['curlSentStoryPt']['name'] . '</p></div>';
			echo '<div><p><b>Description:</b>&nbsp;&nbsp;&nbsp;'.$_SESSION['curlSentStoryPt']['description'].'</p></div>';
			echo '<div><p><b>Story Type:</b>&nbsp;&nbsp;&nbsp;' . ucwords($_SESSION["conditionB"]) . '</p></div>';
			echo '<div><p><b>Requested By:</b>&nbsp;&nbsp;&nbsp;' . $this->userApi->user_firstname . ' ' . $this->userApi->user_lastname . '</p></div>';
			echo '<div><p><b>Current Status:</b>&nbsp;&nbsp;&nbsp;' . ucwords($_SESSION['curlSentStoryPt']['current_state']) . '</p></div>';
			echo '<div><p><b>Project Queue:</b>&nbsp;&nbsp;&nbsp;' . ucwords($_SESSION["conditionA"]) . '</p></div>';
			echo '<div><p><b>Created On:</b>&nbsp;&nbsp;&nbsp;' . date("m/d/y") . '</p></div>';
			echo '<div style="height: 25px;"></div>';
			echo '</div>';
			}
			
		if (empty($_SESSION['curlHttpErrorNum'])){
			echo '<div style="margin-top: 15px;"><h4>Thank you for using the Inenvi Support Portal.</h4></div>';
		}
			
			unset($_SESSION['conditionA']);
			unset($_SESSION['conditionB']);
			unset($_SESSION['curlSentStoryPt']);
			unset($_SESSION['storyPostResultsSession']);
			unset($_SESSION['curlHttpErrorNum']);
			unset($_SESSION['curlEmailAttachment']);
			unset($_SESSION['storyPostResultsSession']);
			unset($_SESSION['curlAttachmentA']);
			unset($_SESSION['curlAttachmentB']);
			unset($_SESSION['curlUploadErrorDiag_A']);
			unset($_SESSION['curlUploadErrorDiag_B']);
			unset($_SESSION['curlEmailErrorDiag']);
			unset($_SESSION['curlStoryErrorDiag']);
	}

	//Display Search Results
	public function displaySearchResults(){

		$this->jsonDecodeStoryResults = json_decode($this->curlSearchResponse, true);

		/* If Empty */
		if (empty($this->jsonDecodeStoryResults)){
			echo '<h1>Sorry, no results.</h1><br><br>';
			echo '<a  href="/search-for-stories/"><button class="btn btn-default">Back</button></a>';
		}
		
	    $this->resultInt = -1;
		
		/* Descending Order By Date */
		$dateArrayA = array();
		$dateArrayB = array();
		
		
		if(!empty($this->created_after_date)){
			$this->created_after_dateIso = date("U", strtotime($this->created_after_date));
			}
		
		if(!empty($this->created_before_date)){
			$this->created_before_dateIso = date("U", strtotime($this->created_before_date));
			}
		
		foreach($this->jsonDecodeStoryResults as $key => $value){
			foreach($value as $keyA => $valueA){
				foreach($valueA as $keyB => $valueB){
					
					/* Simplify and Labels Nested Array */
					$labelsArray = array();
					foreach($valueB as $valueC){
						foreach($valueC as $valueD){
							$labelsArray[] = $valueD['name'];
							$labelsCleaned = implode(', ', array_filter($labelsArray));
						}
					}
					  
					/* Date Conversion and Simplified Sortable Array */
					$this->dateConversion = substr($valueB['created_at'], 0, 10);   
					$this->dateConversionFix = date("m/d/Y", strtotime($this->dateConversion));
					$this->dateConversonIso = date("U", strtotime($this->dateConversion));
					
					$dateArrayB[] = $valueB['created_at'];
					
					$dateArrayA[] = array('project_id' => $valueB['project_id'],
										'created_at' => $this->dateConversionFix, 
										'date_created_iso' => $this->dateConversonIso,
										'name' => $valueB['name'],
										'description' => $valueB['description'], 
										'story_type' => $valueB['story_type'], 
										'current_state' => $valueB['current_state'], 
										'id' => $valueB['id'], 
										'url' => $valueB['url'], 
										'labels' => $labelsCleaned, 
										'labels_array' => array_filter($labelsArray)
										);
				}
			}
		}
		
		/* Sort Results Descending By Date */
		array_multisort($dateArrayB, SORT_DESC, $dateArrayA);
		
		$noResults = '<h1>Sorry, no results.</h1><br><br><a  href="/search-for-stories/"><button class="btn btn-default">Back</button></a>';
		
		/* Exluding for Reporting */
		$excludeDefaults = array('Create Filter Array to Exclude');
		
		$dateArrayA = array_filter($dateArrayA, function($newFilterValue) use ($excludeDefaults){			
			if (array_intersect($excludeDefaults, $newFilterValue['labels_array'])){
				unset($newFilterValue);
			}
			else{
				return $newFilterValue;
			}
		});
		
		$searchName = $this->search_name_param;
		$searchLabel = strtolower($this->search_label_param);
		
		if (strpos($searchLabel, ',') || strpos($searchLabel, ".")){
			$searchLabelExplode = str_replace(array(',', '.'), ' ', $searchLabel);
			$searchLabelExplode = array_filter(explode(' ', $searchLabelExplode));
		}
		else{
			$searchLabelExplode = explode(' ', $searchLabel);
		}

		$searchLabelCount = count($searchLabelExplode);  

		if (!empty($this->search_name_param && $this->search_label_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName, $searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
				return array_intersect($searchLabelExplode, $newFilterValue['labels_array'])
				&& preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_label_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
				return array_intersect($searchLabelExplode, $newFilterValue['labels_array'])
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue){
				return $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		} 
		
		elseif (!empty($this->created_after_dateIso && $this->search_name_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso && $this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return array_intersect($searchLabelExplode, $newFilterValue['labels_array']) 
					&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param && $this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName, $searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return preg_match("/\b$searchName\b/i", $newFilterValue['name'])
					&& array_intersect($searchLabelExplode, $newFilterValue['labels_array']);
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue){
				return $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']);
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}

		elseif (!empty($this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return array_intersect($searchLabelExplode, $newFilterValue['labels_array']);  
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		else{
			$filterArray = $dateArrayA;
		}
		
		if($value['project_id'] == 'Project Id as String'){
			$project_id_inenvi_1 = 'Project Name as String';
		}
		elseif($value['project_id'] == 'Project Id as String'){
			$project_id_inenvi_1 = 'Project Name as String';
		}
		elseif($value['project_id'] == 'Project Id as String'){
			$project_id_inenvi_1 = 'Project Name as String';
		}
		elseif($value['project_id'] == 'Project Id as String'){
			$project_id_inenvi_1 = 'Project Name as String';
		}
		elseif($value['project_id'] == 'Project Id as String'){
			$project_id_inenvi_1 = 'Project Name as String';
		}
		
		echo '<h3>Results For ' . $project_id_inenvi_1 . '</h3>';
		
		foreach($filterArray as $value){
		
		$this->resultInt++;
		
		$this->dateConversionFix = date("n/j/Y", strtotime($value['created_at']));
		

			
			echo '<div class="container" style="border-bottom: 1px solid #666;">';
			echo '<div style="height: 25px;"></div>';
			echo '<div><p><b>Story Title:</b>&nbsp;&nbsp;&nbsp;' . $value['name'] . '<p></div>';
			echo '<div><p><b>Description:</b>&nbsp;&nbsp;&nbsp;' . $value['description'] . '<p></div>';
			echo '<div><p><b>Story Type:</b>&nbsp;&nbsp;&nbsp;' . ucwords($value['story_type']) . '<p></div>';
			echo '<div><p><b>Current Status:</b>&nbsp;&nbsp;&nbsp;' . ucwords($value['current_state']) . '<p></div>';
			echo '<div><p><b>Story Id:</b>&nbsp;&nbsp;&nbsp;' . $value['id'] . '<p></div>';
			echo '<div><p><b>Pivotal Tracker Story Link:</b>&nbsp;&nbsp;&nbsp;' . '<a href="'.$value['url'] .'" target="_blank">' . $value['url']  . '</a>' . '<p></div>';
			echo '<div><p><b>Project Queue:</b>&nbsp;&nbsp;&nbsp;' . $project_id_inenvi_1 . '<p></div>';
			echo '<div><p><b>Created On:</b>&nbsp;&nbsp;&nbsp;' . $this->dateConversionFix . '<p></div>';
			echo '<div><p><b>Labels:</b>&nbsp;&nbsp;&nbsp;' . $value['labels'] . '<p></div>';
			echo '<div style="height: 15px"></div>';
			echo '<div id="hiddenCounterInt" style="display: none;">' . $this->resultInt . '</div>';
			//Form
			echo 
				'<div>
					<div type="button" class="btn btn-default" data-toggle="collapse" data-target="#collapseMenuNotes-' . $this->resultInt . '" style="border: none;">Add Note 	&nbsp;&nbsp;&nbsp;<i class="fa fa-chevron-right"></i></div>
				</div>
				<div class="collapse" id="collapseMenuNotes-' . $this->resultInt . '">
				<form class="notesUploadForm" id="notesUploadForm-'. $this->resultInt . '" method="post" enctype="multipart/form-data" action="">
					<div class="form-group">
						<label for="describe">Please enter a new comment for this story.</label>
						<textarea id="describe-'. $this->resultInt .'" class="form-control" name="pivotalNote" placeholder="Required*" rows="4" value="" required></textarea>
					</div>
					<!-- Add File Uploader Here -->
					<div class="form-group">
						<label>Please upload files or screenshots to be included with this comment.</label>
						<div style="height: 5px;"></div>
						<input type="file" name="uploadFilePivotal_A" id="uploadFilePivotal_A-' . $this->resultInt . '">
						<div style="height: 10px;"></div>
						<label>Please upload additional files or screenshots to be included with this comment.</label>
						<div style="height: 5px;"></div>
						<input type="file" name="uploadFilePivotal_B" id="uploadFilePivotal_B-' . $this->resultInt . '">
					</div>
					<div style="height: 25px;"></div>
					<input id="hiddenInt-' . $this->resultInt . '" type="hidden" name="hiddenInt" value="' . $this->resultInt . '">
					<input id="storyIdReturn-' . $this->resultInt . '" type="hidden" name="storyIdReturn" value="' . $value['id'] . '">
					<input id="storyNameReturn-' . $this->resultInt . '" type="hidden" name="storyNameReturn" value="' . $value['name'] . '">
					<input id="storyDescriptionReturn-' . $this->resultInt . '" type="hidden" name="storyDescriptionReturn" value="' . $value['description'] . '">
					<input id="projectIdReturn-' . $this->resultInt . '" type="hidden" name="projectIdReturn" value="' . $value['project_id'] . '">
					<input id="storyUrlReturn-' . $this->resultInt . '" type="hidden" name="storyUrlReturn" value="' . $value['url'] . '">
					<div class="col-md-12">
					<div class="row">
					<div class="col-md-2">
					<button type="submit" class="btn btn-default" name="submitStoryNewNote-' . $this->resultInt . '" style="background: #745cf9;
					color: #fff;">Submit</button>
					</div>
					<div class="col-md-4 statusIconSpin" style="display: none;">
						<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
						<span class="">&nbsp;&nbsp;&nbsp;Loading...</span>
					</div>
					</div>
					</div>
				</form>
				</div>';
			// End Form
			echo '</div>';
		}
	}
	


		$this->jsonDecodeStoryResults = json_decode($this->curlSearchResponse, true);
		// print_r($this->jsonDecodeStoryResults);

		/* If Empty */
		if (empty($this->jsonDecodeStoryResults)){
			echo '<h1>Sorry, no results.</h1><br><br>';
			echo '<a  href="/search-for-stories/"><button class="btn btn-default">Back</button></a>';
		}
		
	    $this->resultInt = -1;
		
		/* Descending Order By Date */
		$dateArrayA = array();
		$dateArrayB = array();
		
		
		if(!empty($this->created_after_date)){
			$this->created_after_dateIso = date("U", strtotime($this->created_after_date));
			}
		
		if(!empty($this->created_before_date)){
			$this->created_before_dateIso = date("U", strtotime($this->created_before_date));
			}
		
		foreach($this->jsonDecodeStoryResults as $key => $value){
			foreach($value as $keyA => $valueA){
				foreach($valueA as $keyB => $valueB){
					
					/* Simplify and Labels Nested Array */
					$labelsArray = array();
					foreach($valueB as $valueC){
						foreach($valueC as $valueD){
							$labelsArray[] = $valueD['name'];
							$labelsCleaned = implode(', ', array_filter($labelsArray));
						}
					}
					  
					/* Date Conversion and Simplified Sortable Array */
					$this->dateConversion = substr($valueB['created_at'], 0, 10);   
					$this->dateConversionFix = date("m/d/Y", strtotime($this->dateConversion));
					$this->dateConversonIso = date("U", strtotime($this->dateConversion));
					
					$dateArrayB[] = $valueB['created_at'];
					
					$dateArrayA[] = array('project_id' => $valueB['project_id'],
										'created_at' => $this->dateConversionFix, 
										'date_created_iso' => $this->dateConversonIso,
										'name' => $valueB['name'],
										'description' => $valueB['description'], 
										'story_type' => $valueB['story_type'], 
										'current_state' => $valueB['current_state'], 
										'id' => $valueB['id'], 
										'url' => $valueB['url'], 
										'labels' => $labelsCleaned, 
										'labels_array' => array_filter($labelsArray)
										);
				}
			}
		}
		
		/* Sort Results Descending By Date */
		array_multisort($dateArrayB, SORT_DESC, $dateArrayA);
		
		$noResults = '<h1>Sorry, no results.</h1><br><br><a  href="/search-for-stories/"><button class="btn btn-default">Back</button></a>';
		
		$searchName = $this->search_name_param;
		$searchLabel = strtolower($this->search_label_param);
		
		if (strpos($searchLabel, ',') || strpos($searchLabel, ".")){
			$searchLabelExplode = str_replace(array(',', '.'), ' ', $searchLabel);
			$searchLabelExplode = array_filter(explode(' ', $searchLabelExplode));
		}
		else{
			$searchLabelExplode = explode(' ', $searchLabel);
		}

		$searchLabelCount = count($searchLabelExplode);  

		if (!empty($this->search_name_param && $this->search_label_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName, $searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
				return array_intersect($searchLabelExplode, $newFilterValue['labels_array'])
				&& preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
				}
			});
			if (!empty($filterArray)) { echo "all param";}
			elseif (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_label_param && $this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
				return array_intersect($searchLabelExplode, $newFilterValue['labels_array'])
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso && $this->created_before_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue){
				return $newFilterValue['date_created_iso'] >= $this->created_after_dateIso 
				&& $newFilterValue['date_created_iso'] <= $this->created_before_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		} 
		
		elseif (!empty($this->created_after_dateIso && $this->search_name_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']) 
				&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso && $this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return array_intersect($searchLabelExplode, $newFilterValue['labels_array']) 
					&& $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param && $this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName, $searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return preg_match("/\b$searchName\b/i", $newFilterValue['name'])
					&& array_intersect($searchLabelExplode, $newFilterValue['labels_array']);
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->created_after_dateIso)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue){
				return $newFilterValue['date_created_iso'] >= $this->created_after_dateIso;
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		elseif (!empty($this->search_name_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchName){
				return preg_match("/\b$searchName\b/i", $newFilterValue['name']);
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}

		elseif (!empty($this->search_label_param)){
			$filterArray = array_filter($dateArrayA, function($newFilterValue) use ($searchLabelExplode){
				if ( !array_diff($searchLabelExplode, $newFilterValue['labels_array'])){
					return array_intersect($searchLabelExplode, $newFilterValue['labels_array']);  
				}
			});
			if (empty($filterArray)){
				echo $noResults;
			}
		}
		
		else{
			$filterArray = $dateArrayA;
		}
		
		// print_r($filterArray);
		foreach($filterArray as $value){
		
		$this->resultInt++;
		
		$this->dateConversionFix = date("n/j/Y", strtotime($value['created_at']));
		
			if($value['project_id'] == '1509754'){
				$project_id_inenvi_1 = 'Website New Development';
			}
			elseif($value['project_id'] == '1508596'){
				$project_id_inenvi_1 = 'Website Support';
			}
			elseif($value['project_id'] == '1508532'){
				$project_id_inenvi_1 = 'Enterprise New Development';
			}
			elseif($value['project_id'] == '1508660'){
				$project_id_inenvi_1 = 'Enterprise Support';
			}
			elseif($value['project_id'] == '1509792'){
				$project_id_inenvi_1 = 'Operations Support';
			}
			
			echo '<div class="container" style="border-bottom: 1px solid #666;">';
			echo '<div style="height: 25px;"></div>';
			echo '<div><p><b>Story Title:</b>&nbsp;&nbsp;&nbsp;' . $value['name'] . '<p></div>';
			echo '<div><p><b>Description:</b>&nbsp;&nbsp;&nbsp;' . $value['description'] . '<p></div>';
			echo '<div><p><b>Story Type:</b>&nbsp;&nbsp;&nbsp;' . ucwords($value['story_type']) . '<p></div>';
			echo '<div><p><b>Current Status:</b>&nbsp;&nbsp;&nbsp;' . ucwords($value['current_state']) . '<p></div>';
			echo '<div><p><b>Story Id:</b>&nbsp;&nbsp;&nbsp;' . $value['id'] . '<p></div>';
			echo '<div><p><b>Pivotal Tracker Story Link:</b>&nbsp;&nbsp;&nbsp;' . '<a href="'.$value['url'] .'" target="_blank">' . $value['url']  . '</a>' . '<p></div>';
			echo '<div><p><b>Project Queue:</b>&nbsp;&nbsp;&nbsp;' . $project_id_inenvi_1 . '<p></div>';
			echo '<div><p><b>Created On:</b>&nbsp;&nbsp;&nbsp;' . $this->dateConversionFix . '<p></div>';
			echo '<div><p><b>Labels:</b>&nbsp;&nbsp;&nbsp;' . $value['labels'] . '<p></div>';
			echo '<div style="height: 15px"></div>';
			echo '<div id="hiddenCounterInt" style="display: none;">' . $this->resultInt . '</div>';
			//Form
			// action="' . plugin_dir_path( __FILE__ ). 'pivotal_tracker_api.php' .'" 
			echo 
				'<div>
					<div type="button" class="btn btn-default" data-toggle="collapse" data-target="#collapseMenuNotes-' . $this->resultInt . '" style="border: none;">Add Note 	&nbsp;&nbsp;&nbsp;<i class="fa fa-chevron-right"></i></div>
				</div>
				<div class="collapse" id="collapseMenuNotes-' . $this->resultInt . '">
				<form class="notesUploadForm" id="notesUploadForm-'. $this->resultInt . '" method="post" enctype="multipart/form-data" action="">
					<div class="form-group">
						<label for="describe">Please enter a new comment for this story.</label>
						<textarea id="describe-'. $this->resultInt .'" class="form-control" name="pivotalNote" placeholder="Required*" rows="4" value="" required></textarea>
					</div>
					<!-- Add File Uploader Here -->
					<div class="form-group">
						<label>Please upload files or screenshots to be included with this comment.</label>
						<div style="height: 5px;"></div>
						<input type="file" name="uploadFilePivotal_A" id="uploadFilePivotal_A-' . $this->resultInt . '">
						<div style="height: 10px;"></div>
						<label>Please upload additional files or screenshots to be included with this comment.</label>
						<div style="height: 5px;"></div>
						<input type="file" name="uploadFilePivotal_B" id="uploadFilePivotal_B-' . $this->resultInt . '">
					</div>
					<div style="height: 25px;"></div>
					<input id="hiddenInt-' . $this->resultInt . '" type="hidden" name="hiddenInt" value="' . $this->resultInt . '">
					<input id="storyIdReturn-' . $this->resultInt . '" type="hidden" name="storyIdReturn" value="' . $value['id'] . '">
					<input id="storyNameReturn-' . $this->resultInt . '" type="hidden" name="storyNameReturn" value="' . $value['name'] . '">
					<input id="storyDescriptionReturn-' . $this->resultInt . '" type="hidden" name="storyDescriptionReturn" value="' . $value['description'] . '">
					<input id="projectIdReturn-' . $this->resultInt . '" type="hidden" name="projectIdReturn" value="' . $value['project_id'] . '">
					<input id="storyUrlReturn-' . $this->resultInt . '" type="hidden" name="storyUrlReturn" value="' . $value['url'] . '">
					<div class="col-md-12">
					<div class="row">
					<div class="col-md-2">
					<button type="submit" class="btn btn-default" name="submitStoryNewNote-' . $this->resultInt . '" style="background: #745cf9;
					color: #fff;">Submit</button>
					</div>
					<div class="col-md-4 statusIconSpin" style="display: none;">
						<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
						<span class="">&nbsp;&nbsp;&nbsp;Loading...</span>
					</div>
					</div>
					</div>
				</form>
				</div>';
			// End Form
			echo '</div>';
		}
	}
	public function modalPopUps(){
		$modalSuccessPop = '
		 <div class="modal fade" id="getSuccessModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="top: 20%;">
		   <div class="modal-dialog modal-md">
			  <div class="modal-content" style="border-radius: 0 !important;">
			   <div class="modal-header">
				 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				 <div style="height: 45px;"></div>
				 <h4 class="modal-title text-center" id="myModalLabel"> Additional Story Note Update Successful</h4>
				 <div style="height: 50px;"></div>
			   </div>
			</div>
		   </div>
		 </div>
		';
		return $modalSuccessPop;
		$modalFailurePop = '
		 <div class="modal fade" id="getSuccessModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="top: 20%;">
		   <div class="modal-dialog modal-md">
			  <div class="modal-content" style="border-radius: 0 !important;">
			   <div class="modal-header">
				 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				 <div style="height: 45px;"></div>
				 <h4 class="modal-title text-center" id="myModalLabel"> Additional Story Note Update Failed. Please Try Again.</h4>
				 <div style="height: 50px;"></div>
			   </div>
			</div>
		   </div>
		 </div>
		';
		return $modalFailurePop;
	}
	
	//Email
	public function fireOffEmail(){
		//Remember-- Changed Default email settings in pluggable.php lines 375, 391

		$this->jsonDecodeStoryResults = json_decode($this->storyCreatePostResults, true);
		if($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
			$project_id_inenvi = 'Project Name as String';
		}
		elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
			$project_id_inenvi = 'Project Name as String';
		}
		elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
			$project_id_inenvi = 'Project Name as String';
		}
		elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
			$project_id_inenvi = 'Project Name as String';
		}
		elseif($this->jsonDecodeStoryResults['project_id'] == 'Project Id as String'){
			$project_id_inenvi = 'Project Name as String';
		}
		
		$userLoggedInInfo = wp_get_current_user();
		$from_email = $userLoggedInInfo->user_email;
		
		//Date Fix
		$dateConversion = substr($this->jsonDecodeStoryResults['created_at'], 0, 10);
		$dateConsersionFix = date("n-j-Y", strtotime($dateConversion));
		
		//DoMyLLC Users Fix
		if($this->userApi->user_email == "Excluded Email" || $this->userApi->user_email == "Excluded Email"){ 
			$to = array(
				$this->userApi->user_email, 
				'Specified Email'
			);
		} 
		else{
			$to = array(
				$this->userApi->user_email
			);
		}
		
		if ($project_id_inenvi == 'Project Id as String'){
			$bcc = array(
				'Specified Email Addresses'
			);
		}
		
		if ($project_id_inenvi == 'Project Id as String'){
			$bcc = array(
				'Specified Email Addresses'
			);
		}
		
		if ($project_id_inenvi == 'Project Id as String' || $project_id_inenvi == 'Project Id as String'){
			$bcc = array(
				'Specified Email Addresses'
			);
		}
		
		if ($project_id_inenvi == 'Project Id as String'){
			$bcc = array(
				'Specified Email Addresses'
			);
		}
		
		$attachments = array();
		array_push($attachments, $this->userUploadFileA); 
		array_push($attachments, $this->userUploadFileB);
		$headers = array( "Content-Type: text/html; charset=UTF-8", "From: ". $from_email, "BCC: ". implode(",", $bcc) ); 
		$subject = 'New ' . $this->jsonDecodeStoryResults['name'] . ' in ' . $project_id_inenvi;
		$content = "<b>Story Title: </b>" . $this->jsonDecodeStoryResults['name'] . "<br/><br/>" . '<b>Description: </b>' . $this->jsonDecodeStoryResults['description'] . "<br/><br/>" . '<b>Story Type: </b>' . ucwords($this->jsonDecodeStoryResults['story_type']) . "<br/><br/>" . '<b>Requested By: </b>' . $this->userApi->user_firstname . ' ' . $this->userApi->user_lastname . "<br/><br/>" . '<b>Current Status: </b>' . ucwords($this->jsonDecodeStoryResults['current_state']) . "<br/><br/>" . '<b>Story Id: </b>' . $this->jsonDecodeStoryResults['url'] . "<br/><br/>" . '<b>Project Queue: </b>' . $project_id_inenvi . "<br/><br/>" .'<b>Created On: </b>' . $dateConsersionFix . "<br/><br/>" . '<b>Urgency of Request: </b>' . $this->requestUrgencyForm;
		$sent = wp_mail($to, $subject, $content, $headers, $attachments); 
		return $sent;
	}
	
	// Failure Email System 
	public function fireOffEmailFailure(){

		$userLoggedInInfo = wp_get_current_user();
		$from_email = $userLoggedInInfo->user_email;
		
		$createReplaceArray = array("T", "Z");
		$createSubArray = array("&nbsp;&nbsp;&nbsp;", "");
		
		$to = array(
			$this->userApi->user_email
		);
		$bcc = array(
			'Specified Email Addresses'
		);
		
		$attachments = array();
		array_push($attachments, $this->userUploadFileA); 
		array_push($attachments, $this->userUploadFileB);
		$headers = array( "Content-Type: text/html; charset=UTF-8", "From: ". $from_email, "BCC: ". implode(",", $bcc));  
		$subject ="***STORY CREATION FAILURE NOTICE***   " . 'New ' . $this->createStoryArray['name'] . ' in ' . $project_id_inenvi;
		$content ="<b><u>The Following Story Was Not Submitted To Pivotal Tracker:</u></b>" . "<br/><br/><br/>" . "<b>Story Title: </b>" . $this->createStoryArray['name'] . "<br/><br/>" . '<b>Description: </b>' . $this->createStoryArray['description'] . "<br/><br/>" . '<b>Story Type: </b>Feature' . "<br/><br/>" . '<b>Requested By: </b>' . $this->userApi->user_firstname . ' ' . $this->userApi->user_lastname . "<br/><br/>" . '<b>Current Status: </b>' . ucwords($this->createStoryArray['current_state']) . "<br/><br/>" . '<b>Project Queue: </b>' . ucwords($this->conditionA) . "<br/><br/>" .'<b>Created On: </b>' . date("m/d/y") . "<br/><br/>" . '<b>Urgency of Request: </b>' . $this->requestUrgencyForm;
		$sent = wp_mail($to, $subject, $content, $headers, $attachments); 
		return $sent;
	}
	
	/* Diagnostic Failure Email */
		public function diagnosticEmailFailure(){

		$from_email = 'Specified Email Addresses';
		
		$createReplaceArray = array("T", "Z");
		$createSubArray = array("&nbsp;&nbsp;&nbsp;", "");
		
		$to = array(
			'Specified Email Addresses'
		);

		$emailAttachCurlError = $_SESSION['curlEmailErrorDiag'];
		$emailAttachment = json_decode($_SESSION['curlEmailAttachment'], true);
		$curlErrorNumber = $_SESSION['curlHttpErrorNum'];
		$storyCurlError = $_SESSION['curlStoryErrorDiag'];
		$storyResult =  json_decode($_SESSION['storyPostResultsSession'], true);
		$attachmentCurlError_A = $_SESSION['curlUploadErrorDiag_A'];
		$attachmentA = json_decode($_SESSION['curlAttachmentA'], true);
		$attachmentCurlError_B = $_SESSION['curlUploadErrorDiag_B'];
		$attachmentB = json_decode($_SESSION['curlAttachmentB'], true);


		$attachments = array();
		array_push($attachments, $this->userUploadFileA); 
		array_push($attachments, $this->userUploadFileB);
		$headers = array( "Content-Type: text/html; charset=UTF-8", "From: ". $from_email);
		$subject ="***Support.Inenvi.com Error Diagnostic Email***";
		$content = "<b><u>There were the following issues with Pivotal Tracker:</u></b>" . "<br/><br/>" . "<b>cUrl Error Number: </b>" . $curlErrorNumber;
		$contentA = "<br /><br /><b>cUrl Story Error Response: </b>" . print_r($storyCurlError, true);
		$contentB = "<br /><br /><b>Pivotal Tracker Story Error Response: </b>" . print_r(implode(" ", $storyResult), true);
		$contentC = "<br /><br /><b>cUrl Email Error Response: </b>" . print_r($emailAttachCurlError, true);
		$contentD = "<br /><br /><b>Pivotal Tracker Email Attachment Error Response: </b>" . print_r(implode(" ", $emailAttachment), true);
		$contentE = "<br /><br /><b>cUrl First File Attachment Error Response: </b>" . print_r($attachmentCurlError_A, true);
		$contentF = "<br /><br /><b>First Pivotal Tracker File Attachment Response: </b>" . print_r(implode(" ", $attachmentA), true);
		$contentG = "<br /><br /><b>cUrl Second File Attachment Error Response: </b>" . print_r($attachmentCurlError_B, true);
		$contentH = "<br /><br /><b>Second Pivotal Tracker File Attachment Response: </b>" . print_r(implode(" ", $attachmentB), true);
		$msg = array($content, $contentA, $contentB, $contentC, $contentD, $contentE, $contentF, $contentG, $contentH);
		$sent = wp_mail($to, $subject, implode(" ", $msg), $headers, $attachments); 
		return $sent;
	}
	

	public function fireOffEmailComments(){

		$this->jsonDecodeStoryResults = json_decode($this->apiPOSTResults, true);
		
		if($this->projectId == 'Project Id as String'){
			$project_id_inenvi_a = 'Project Name as String';
		}
		elseif($this->projectId  == 'Project Id as String'){
			$project_id_inenvi_a = 'Project Name as String';
		}
		elseif($this->projectId  == 'Project Id as String'){
			$project_id_inenvi_a = 'Project Name as String';
		}
		elseif($this->projectId  == 'Project Id as String'){
			$project_id_inenvi_a = 'Project Name as String';
		}
		elseif($this->projectId  == 'Project Id as String'){
			$project_id_inenvi_a = 'Project Name as String';
		}

				$userLoggedInInfo = wp_get_current_user();
		$from_email = $userLoggedInInfo->user_email;

		$to = array(
			$this->userApi->user_email
		);
		$attachments = array();
		array_push($attachments, $this->userUploadFileA); 
		array_push($attachments, $this->userUploadFileB);
		$headers = array( "Content-Type: text/html; charset=UTF-8", "From: ". $from_email, "BCC: Specified Email" ); 
		$subject = 'New Notes Added To Story: ' . $this->storyNameReturn . ' in ' . $project_id_inenvi_a;
		$content = "<b>Story Title: </b>" . $this->storyNameReturn . "<br/><br/>" . '<b>Description: </b>' . $this->storyDescriptionReturn . "<br/><br/>" . '<b>Requested By: </b>' . $this->userApi->user_firstname . ' ' . $this->userApi->user_lastname . "<br/><br/>" . '<b>Story Id: </b>' . $this->storyUrlReturn . "<br/><br/>" . '<b>Notes: </b>' . $this->jsonDecodeStoryResults['text'];
		$sent = wp_mail($to, $subject, $content, $headers, $attachments); 
		return $sent;
	}
	
	public function helloUserHtml(){
		$userNameHtml = '<h3>Hey '. $this->userApi->user_firstname .'!</h3>';
		return $userNameHtml;
	}
	
	public function uploadFileToWordpress(){
		$this->userUploadFileA = "/home/admin/public_html/temp_files_upload/" . basename($_FILES["uploadFilePivotal_A"]["name"]);
		move_uploaded_file($_FILES["uploadFilePivotal_A"]["tmp_name"], $this->userUploadFileA);
	}
	
	public function uploadSecondFileToWordpress(){
		$this->userUploadFileB = "/home/admin/public_html/temp_files_upload/" . basename($_FILES["uploadFilePivotal_B"]["name"]);
		move_uploaded_file($_FILES["uploadFilePivotal_B"]["tmp_name"], $this->userUploadFileB);		
	}
	
	//Incremental Numbering System
	public function incrementJobNumbers(){
		$counterFile = plugin_dir_path( __FILE__ ).'counter.txt';
		$this->incrementVal = (int)file_get_contents($counterFile);
		$this->incrementVal++;
		file_put_contents($counterFile, $this->incrementVal);
	}
	
}


$pivotalTrackerAPI = new PivotalTrackerAPI();



