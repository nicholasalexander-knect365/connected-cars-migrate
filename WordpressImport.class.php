<?php

class WordpressImport {
	
	public $post_id;
	public $filename;
	public $fd;
	public $post;
	private $fields;

	public $cmds;
	private $categoryCreated;
	private $categories;

	public function __construct($filename = 'wp_imports.cli') {
		
		// command stack
		$this->cmds = [];

		$this->categoryCreated = [];
		$this->categories = [];	
		$this->filename = $filename;
		
		$this->fd = fopen($this->filename, 'w+') or die('can not open cli command file: ' . $file);

		$this->fields = [ 'post_id', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_title', 'post_excerpt', 'post_status', 'post_type', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_mime_type', 'guid', 'post_category', 'tags_input', 'tax_input', 'meta_input'];
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

	public function makePostSQL() {
		foreach ($this->fields as $field) {

		}
	}
	public function makePost() {
		$post = $this->post;

		$cmds = 'wp post create';		

		foreach ($this->fields as $field) {
			if (is_array($post->$field)) {
				if ($field === 'post_category') {
					foreach($post->$field as $category) {
						$this->categories[$category] = json_encode($category);
					}

				}
				$cmd = ' --'.$field.'='.json_encode($post->$field);
				
			} else {
				$cmd = ' --'.$field."='". $post->$field ."'";
			}
			$cmds .= $cmd;
		}
		$cmds .= "\n";
		$this->cmds[] = $cmds;
	}

	public function writeCategories() {
		$this->makeCategories();
	}

	public function writePosts() {
		foreach ($this->cmds as $cmds) {
			fputs($this->fd, $cmds);
		}
	}

	private function makeCategories() {
var_dump($this->categories);
		foreach ($this->categories as $key => $category) {
			if (!in_array($category, $this->categoryCreated)) {
				$this->categoryCreated[] = $category;
				$createCategory = 'wp term create category ' . ucfirst($category) . "\n";
var_dump($createCategory);
				fputs($this->fd, $createCategory);
			}
		}
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