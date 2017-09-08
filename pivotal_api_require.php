<?php

/*
 *	Required jQuery/Javascript/CSS for Plugin
 */
 
 class PivotalJavaScript{
	 public function __construct(){
		 add_action('wp_enqueue_scripts', array($this, 'pivotal_api_javascript'), 100);
		 add_action('wp_enqueue_scripts', array($this, 'pivotal_api_javascript_page_bottom'), true);
	 }
	 
	 public function pivotal_api_javascript(){
		 wp_register_style('datePickerCss', plugins_url('/pivotal-tracker/Datepicker/pikaday.css'));
		 wp_enqueue_style('datePickerCss');
		 wp_register_script('ajaxNotesFormScript', plugins_url().'/pivotal-tracker/Js/ajaxNotesForm.js', NULL, '1.0.420', false);
		 wp_enqueue_script('ajaxNotesFormScript');
		 wp_register_script('bootstrapJs', plugins_url().'/pivotal-tracker/Js/bootstrap.js');
		 wp_enqueue_script('bootstrapJs');
	 }
	 
	 public function pivotal_api_javascript_page_bottom(){
		 wp_register_script('momentJs', plugins_url().'/pivotal-tracker/Datepicker/moment.js', '', '', true);
		 wp_enqueue_script('momentJs');
		 wp_register_script('datePickerJs', plugins_url().'/pivotal-tracker/Datepicker/pikaday.js', '', '', true);
		 wp_enqueue_script('datePickerJs');
		 wp_register_script('datePickerCustomJs', plugins_url().'/pivotal-tracker/Datepicker/datePickerCustomJs.js', '', '', true);
		 wp_enqueue_script('datePickerCustomJs');
		 if (!is_page( array('Report Creation', 'Search For Stories', 'Search Results', 'Export Results'))){
			 wp_register_script('drawImageCanvasJs', plugins_url().'/pivotal-tracker/Js/drawImageCanvas.js', '', '', true);
			 wp_enqueue_script('drawImageCanvasJs');
		 }
		 wp_register_script('clearForm', plugins_url().'/pivotal-tracker/Js/clearForm.js', '', '', true);
		 wp_enqueue_script('clearForm');
	 }

 }
 
 new PivotalJavaScript();