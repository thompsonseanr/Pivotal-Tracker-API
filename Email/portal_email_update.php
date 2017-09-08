<?php

add_filter('wp_mail_from', array('PortalEmailUpdate', 'updated_mail_from'));
add_filter('wp_mail_from_name', array('PortalEmailUpdate', 'updated_mail_from_name'));

class PortalEmailUpdate{

	public function __construct(){
		$this->updated_mail_from();
		$this->updated_mail_from_name();
	}
	
	public function updated_mail_from(){
		return 'Specified Email';
	}
	
	public function updated_mail_from_name(){
		return 'Specified Name';
	}
	
}

new PortalEmailUpdate();