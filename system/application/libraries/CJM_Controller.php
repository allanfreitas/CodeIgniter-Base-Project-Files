<?php

	/**
	* 
	*/
	class CJM_Controller extends Controller
	{
		protected $data = array();

		public function __construct(){
			parent::__construct();
			
			//Handle the different types of flash notifications we might get.
			if($this->session->flashdata('error')){
				$this->ocular->set_message($this->session->flashdata('error'), 'error', false);
			}else if($this->session->flashdata('attention')){
				$this->ocular->set_message($this->session->flashdata('attention'), 'attention', false);
			}else if($this->session->flashdata('information')){
				$this->ocular->set_message($this->session->flashdata('information'), 'information', false);
			}else if($this->session->flashdata('success')){
				$this->ocular->set_message($this->session->flashdata('success'), 'success', false);
			}
			
			//We add more keys to $this->data[] as we need more common data for our view files.
			$this->data['categories'] = Doctrine::getTable('Category')->findAll();
			// sets view data for pages which don't add their own
			$this->ocular->set($this->data);
		}
		public function Controller(){ $this->__construct(); }
	}
	