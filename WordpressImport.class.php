<?php

class WordpressImport {
	
	public $post_id;
	public $filename;
	public $fd;
	public $post;
	private $fields;
	private $categoryCreated;

	public function __construct($filename = 'wp_imports.cli') {
		$this->categoryCreated = [];	
		$this->filename = $filename;
		
		$this->fd = fopen($this->filename, 'w+') or die('can not open cli command file: ' . $file);

		$this->fields = [ 'post_id', 'post_author', 'post_date', 'post_date_gmt', 
					'post_content', 'post_content_filtered', 'post_title', 
					'post_excerpt', 'post_status', 'post_type', 
					'comment_status', 'ping_status', 
					'post_password', 'post_name', 'to_ping', 'pinged', 
					'post_modified', 'post_modified_gmt', 'post_parent',
					'menu_order', 'post_mime_type', 'guid', 
					'post_category', 'tags_input', 'tax_input', 'meta_input'];
	}

	public function __destruct() {
		fclose($this->fd);
	}

	public function initPost() {
		$post = new stdclass();
		$fields = $this->fields;
		foreach ($fields as $field) {
			$post->$field = '';
		}

		$post->post_parent = 0;
		$post->menu_order = 0;
		$post->status = 'Draft';
		$this->post = $post;
		return $post;
	}

	public function makePost() {
		$post = $this->post;

		$cmds = 'wp post create';
		foreach ($this->fields as $field) {

			if (is_array($post->$field)) {
				if ($field === 'post_category') {
					$categories = '';
					foreach($post->$field as $category) {
						$categories[$category] = $category;
					}
				} else {
					$cmd = ' --'.$field.'='.json_encode($post->$field);
				}
			} else {
				$cmd = ' --'.$field."='".$post->$field."'";
			}
			//var_dump($cmd);
			$cmds .= $cmd;
		}
		$cmds .= "\n";		

		foreach ($categories as $key => $category) {
			$createCategory = 'wp term create category "' . ucfirst($category) . "\"\n";
			if (!$this->categoryCreated[$category]) {
				$this->categoryCreated[$category] = 1;
				fputs($this->fd, $createCategory);
			}
		}
		fputs($this->fd, $cmds);
	}
	public function makePage() {

	}
	public function makeCategory() {

	}
	public function makeTag() {

	}

	public function makeImage() {

	}
}