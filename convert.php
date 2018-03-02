<?php

require "DBConnect.class.php";
require "WordpressImport.class.php";
require "PostProcess.class.php";
require "Images.class.php";

$wpdb = new DBConnect($dbname = 'wordpress', $user = 'wp',    $pass = 'wp',     $server = 'localhost');

$primaryXml = 'document.xml';
$pagesXml   = 'doc_pages.xml';
// only two images in this xml: do them by hand!
$imagesXml  = 'doc_images.xml';
$keywordsXml = 'doc_keywords.xml';

try {
	$primary = simplexml_load_file($primaryXml);
	$pages   = simplexml_load_file($pagesXml);
	$images  = simplexml_load_file($imagesXml);
	$keywords = simplexml_load_file($keywordsXml);
} catch (Exception $e) {
	print 'Error reading XML';
	die( $e->getMessage());
}

$doc_id = 0;
$pageContent = [];


$primaryKeys = array_keys((array)$primary->row);
$pagesKeys = array_keys((array)$pages->row);
$imagesKeys = array_keys((array)$images->row);
$keywordsKeys = array_keys((array)$keywords->row);

$import = new WordpressImport();
$postedit = new PostProcess();

foreach($pages as $page) {
	$pageId = (integer)$page->page_id;
	$docId  = (integer)$page->doc_id;
	$pageContent[$docId] = (string)$page->page_html;
}

function prepare($str) {
	$str = html_entity_decode($str);
	$str = str_replace(array("\r\n", "\r", "\n"), '', $str);
	$str = preg_replace('/\'/', '&apos;', $str);
	return $str;
}

foreach($primary as $row) {

	$post = $import->initPost();
	$docId = (string)$row->doc_id;
	// TODO: make a meta of the docId?
	// $post->post_id = $docId;
	$post->post_author = (string)$row->doc_byline;

	// convert
	$post->date = (string)$row->doc_published;

	if (strlen($pageContent[$docId])) {
		// some pages do not have content - they are events
		$content = prepare($pageContent[$docId]);
		
		$postedit->setPost($content);
		$postedit->localiseUrls();
		$postedit->localiseImages();
		$content = $postedit->getPost();

		$post->content_filtered = $content;
		// $post->post_content = $postProcess->extractUrl($content);
		$post->post_content = $content;
	}
	if (strlen($images[$docId])) {

	}
	$post->post_title  = prepare((string)$row->doc_headline);

	// subhead contains a list of tags
	$post->post_excerpt = prepare((string)$row->doc_summary_med);
	$post->post_status = (string)$row->doc_pool === 'Public' ? 'publish' : 'draft';
	$post->post_type = 'post';
	$post->comment_status = 'closed';
	$post->ping_status = 'open';
	$post->post_password = '';
	$post->post_name = $post->post_title;
	$post->to_ping = '';
	$post->pinged = '';
	$d = date_parse((string)$row->doc_published);

	$post->post_modified = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $d['year'], $d['month'], $d['day'], $d['hour'], $d['minute'], $d['second']);

	$post->post_modified_gmt = $post->post_modified;
	$post->post_parent = 0;
	// TODO: verify menu order - using doc_section as may be natural?
	$post->menu_order = 0;
	$post->mime_type = '';
	
	$post->guid = ''; //$docId;

	$post->post_category = explode(',', (string)$row->section_name);

	//var_dump($docId, (string)$row->section_name, $post->post_category);

	$post->tags_input = prepare((string)$row->doc_subhead);
	$post->tax_input = '';

	// meta causes problems ... debug!
	// $post->meta_input = [
	// 	'test-name' => 'Connected Cars', 
	// 	'description' => 'Connected Car News: Covering the latest news and analysis across telematics, driverless technology, infotainment, security and more.', 
	// 	'keywords' => 'Infotainment, Apps, Security, Telematics, Driverless Cars'];

	$image = new Images($docId);
	$image->setUrl((string)$row->doc_image);

	// $import->makePostCategory($post);
	$import->makePost($post);
	$import->attachImage($post, $image);
	//var_dump($post); die;
}
// var_dump('posts made');
$import->writeCategories();
$import->writePosts();

// $process = new PostProcess();
// $import->writePostCategories();
$import->writeImages();
