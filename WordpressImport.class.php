<?php

class WordpressImport {
	
	public $post_id;
	public $filename;
	public $fd;
	public $post;
	public $cmds;
	public $categoryId;

	private $fields;
	private $categoryCreated;
	private $categories;


	public function __construct($filename = 'wp_imports.cli') {
		
		// command stack
		$this->cmds = [];
		$this->categoryId = 1; // Uncategorised is always present
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

	public function makePost() {

		$post = $this->post;
		$cmds = 'wp post create';		

		foreach ($this->fields as $field) {

			if (is_array($post->$field)) {

				if ($field === 'post_category') {

					$myCategories = [];
					foreach($post->post_category as $category) {
						if (empty($this->categories[$category])) { 
							$this->categoryId++;
							$this->categories[$category] = $this->categoryId;	
						}
						foreach ($this->categories as $cat => $id) {
							if ($cat === $category) {
								$myCategories[] = $this->categories[$category];
							}
						}
					}
					$cmd = ' --'.$field.'='.json_encode(implode(',', $myCategories));
				} else {
					$cmd = ' --'.$field.'='.json_encode($post->$field);
				}				
			} else {
				$cmd = ' --'.$field."='". $post->$field ."'";
			}
			$cmds .= $cmd;
		}
		$cmds .= "\n";
		$this->cmds[] = $cmds;
	}

	public function writePosts() {
		foreach ($this->cmds as $cmds) {
			fputs($this->fd, $cmds);
		}
	}

	public function makePostCategory($post) {
//var_dump($post);
		foreach ($post->post_category as $category) {
			$cmd = sprintf('wp post term set %d %s category', $post->post_id, $category);
			$this->postCategories[] = $cmd;
		}
	}

	public function writePostCategories() {
		foreach ($this->postCategories as $postCategory) {
			fputs($this->fd, $postCategory. "\n");
		}
	}

	// TODO - APPEARS UNUSED CRUFT
	private function makeCategory($category) {
die('makeCategory');
		//$this->categoryId++;
		$createCategory = "wp term create category '" . ucfirst($category) . "\'\n";
		fputs($this->fd, $createCategory);
		//return $this->categoryId;
	}

	public function writeCategories() {
		foreach ($this->categories as $category => $key) {
			if (!in_array($category, $this->categoryCreated)) {
				$this->categoryCreated[] = $category;
				$createCategory = 'wp term create category "' . ucfirst($category) . "\"\n";
				fputs($this->fd, $createCategory);
			}
		}
	}

	public function makePage() {
	}
	public function makeTag() {
	}

	public function makeImage() {
	}
}
