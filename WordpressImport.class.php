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

	// public function makePostSQL() {
	// 	foreach ($this->fields as $field) {

	// 	}
	// }
	public function makePost() {
		$post = $this->post;

		$cmds = 'wp post create';		

		foreach ($this->fields as $field) {

			if (is_array($post->$field)) {
				if ($field === 'post_category') {
					$myCategories = [];
					foreach($post->post_category as $category) {
// print "\nCategory is ".$category."\n";
// var_dump($this->categories);
						if (empty($this->categories[$category])) { 
// var_dump('EMPTY!');
							$this->categoryId++;
							$this->categories[$category] = $this->categoryId;	
						}
// var_dump($this->categoryId);
						foreach ($this->categories as $cat => $id) {
							if ($cat === $category) {
								$myCategories[] = $this->categories[$category];
							}
						}
					}
// var_dump( $myCategories);
					$cmd = ' --'.$field.'='.json_encode(implode(',', $myCategories));
//var_dump($cmd);
				} else {
					$cmd = ' --'.$field.'='.json_encode($post->$field);
				}				
			} else {
//var_dump($field . ' was not an ARRAY');
				$cmd = ' --'.$field."='". $post->$field ."'";
			}
			$cmds .= $cmd;
		}
		//$cmds .= ' --porcelain`';
		$cmds .= "\n";
		$this->cmds[] = $cmds;
	}


	public function writePosts() {
		foreach ($this->cmds as $cmds) {
			//print $cmds;
			fputs($this->fd, $cmds);
		}
	}

	public function makePostCategory($post) {
		$cmd = sprintf('wp post term set %d %s category', $post->post_id, $post->category);
		$this->postCategories[] = $cmd;
	}

	public function writePostCategories() {
		foreach ($this->postCategories as $postCategory) {
			fputs($this->fd, $postCategory. "\n");
		}
	}

	private function makeCategory($category) {
		//$this->categoryId++;
		$createCategory = "wp term create category '" . ucfirst($category) . "\'\n";
		fputs($this->fd, $createCategory);
		//return $this->categoryId;
	}

	public function writeCategories() {
//var_dump($this->categories);
		foreach ($this->categories as $category => $key) {
			if (!in_array($category, $this->categoryCreated)) {
				$this->categoryCreated[] = $category;
				$createCategory = 'wp term create category "' . ucfirst($category) . "\"\n";
//var_dump($createCategory);
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
