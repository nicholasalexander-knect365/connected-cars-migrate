<?php

class WordpressImport {
	
	public $post_id;
	public $filename;
	public $fd;
	public $post;
	private $fields;
	private $categoryCreated;
	private $categories;

	public function __construct($filename = 'wp_imports.cli') {
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
		return $this->makePostWPCLI();
	}

	public function makePostWPCLI() {
		$post = $this->post;

		$cmds = 'wp post create';		

		foreach ($this->fields as $field) {

			if (is_array($post->$field)) {

				if ($field === 'post_category') {
					foreach($post->$field as $category) {
						$this->categories[$category] = json_encode($category);
					}
				} else {
					$cmd = ' --'.$field.'='.json_encode($post->$field);
				}
			} else {
				$cmd = ' --'.$field."='". $post->$field ."'";
			}
			$cmds .= $cmd;
print "\n".$cmd;

		}
print "\n\n";
		$cmds .= "\n";

print $cmds;
		$this->makeCategories($cmds);
	}

	private function makeCategories(String $cmds) {
		foreach ($this->categories as $key => $category) {			
			if (!in_array($category, $this->categoryCreated)) {
				$this->categoryCreated[] = $category;
				$createCategory = 'wp term create category ' . ucfirst($category) . "\n";
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