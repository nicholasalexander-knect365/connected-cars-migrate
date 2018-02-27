<?php

require "DBConnect.class.php";
require "WordpressImport.class.php";

$wpdb = new DBConnect($dbname = 'wordpress', $user = 'wp',    $pass = 'wp',     $server = 'localhost');

$primaryXml = 'document.xml';
$pagesXml   = 'doc_pages.xml';
$imagesXml  = 'doc_images.xml';
$import = new WordpressImport();


try {
	$primary = simplexml_load_file($primaryXml);
	$pages   = simplexml_load_file($pagesXml);
	$images  = simplexml_load_file($imagesXml);
} catch (Exception $e) {
	die( $e->getMessage());
}

$primaryKeys = array_keys((array)$primary->row);
$pagesKeys = array_keys((array)$pages->row);
$imagesKeys = array_keys((array)$images->row);
$doc_id = 0;

$pageContent = [];
foreach($pages as $page) {
	$pageId = (integer)$page->page_id;
	$docId  = (integer)$page->doc_id;
	$pageContent[$docId]=(string)$page->page_html;
}

//var_dump($pageContent);

foreach($primary as $row) {

	$post = $import->initPost();

	$docId = (string)$row->doc_id;

$post->post_id = $docId;
	$post->post_author = (string)$row->doc_byline;

	// convert
	$post->date = (string)$row->doc_published;
	
	$post->content = $pageContent[$docId];
	
	$post->content_filtered = (string)$row->doc_summary_med;
	
	$post->post_title  = (string)$row->doc_headline;
	// TODO: use a custom field for subhead? it appears to contain tags
	$post->post_excerpt = (string)$row->doc_subhead;
	
	$post->post_status = (string)$row->doc_pool === 'Public' ? 'publish' : 'draft';
	$post->post_type = 'post';
	$post->comment_status = 'closed';
	$post->ping_status = 'open';
	$post->post_password = '';
	$post->post_name = $post->post_title;
	$post->to_ping = '';
	$post->pinged = '';
	$d = date_parse((string)$row->doc_published);
	$post->post_modified = sprintf('%04d-%02d-%02d %0d2:%0d2:%02d', $d['year'], $d['month'], $d['day'], $d['hour'], $d['minute'], $d['second']);

	$post->post_modified_gmt = $post->post_modified;
	$post->post_parent = 0;
	// TODO: verify menu order - using doc_section as may be natural?
	$post->menu_order = 0;
	$post->mime_type = '';
	$post->guid = 0;
	$post->post_category = explode(',', (string)$row->section_name);
	$post->tags_input =(string)$row->doc_subhead;
	$post->tax_input = '';
	$post->meta_input = [
		'test-name' => 'Connected Cars', 
		'description' => 'Connected Car News: Covering the latest news and analysis across telematics, driverless technology, infotainment, security and more.', 
		'keywords' => 'Infotainment, Apps, Security, Telematics, Driverless Cars'];
	$import->makePost($post);
}
