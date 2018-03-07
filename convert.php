<?php

require "DBConnect.class.php";
require "WordpressImport.class.php";
require "PostProcess.class.php";
require "Images.class.php";

$wpdb = new DBConnect($dbname = 'wordpress', $user = 'wp',    $pass = 'wp',     $server = 'localhost');
$full = true;

if ($full) {
	$primaryXml = 'XML/document_full.xml';
	$pagesXml   = 'XML/doc_pages_full.xml';
	// only two images in this xml: do them by hand!
	$imagesXml  = 'XML/doc_images_full.xml';
	$keywordsXml = 'XML/doc_keywords_full.xml';
} else {
	$primaryXml = 'XML/example/document.xml';
	$pagesXml   = 'XML/example/doc_pages.xml';
	// only two images in this xml: do them by hand!
	$imagesXml  = 'XML/example/doc_images.xml';
	$keywordsXml = 'XML/example/doc_keywords.xml';
}

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
	///$str = preg_replace('/\"/', '&quot;', $str);

	return $str;
}

foreach($primary as $row) {

	$post = $import->initPost();
	$docId = (string)$row->doc_id;
	$keywordNodes = [];
	$imageNodes = [];
	$eventPages = [];

	foreach ($keywords->children() as $node) {
		foreach($node as $key => $value) {
			$key = (string)$key;
			$value = (string)$value;

			if ($key === 'doc_id' && $value === $docId) {
				if ((integer)$node->docnode_weight === 10) {
					$keywordNodes[] = (string)$node->node_name;
				} else {
					$keywordNodes[] = (string)$node->node_name;
				}
			}
		}
	}
	// in case there are images... 
	foreach ($images->children() as $node) {
		//var_dump($node);
		foreach ($node as $key => $value) {
			$key = (string)$key;
			$value = (string)$value;
			if ($key == 'doc_id' && $value === $docId) {
				$imageNodes[] = (string)$value;
			}		
		}
	}
	//var_dump($keywordNodes, $imageNodes);
	$post->post_author = (string)$row->doc_byline;

	// convert
	$post->date = (string)$row->doc_published;

	if (strlen($pageContent[$docId])) {
		// some pages do not have content - they are events
		$content = prepare($pageContent[$docId]);
		$postedit->setPost($content);
	} else {
		$summary = prepare((string)$row->doc_summary_short);
		$link    = (string)$row->doc_url;
		if (strlen($link)) {
			$text = '<a href="' . $link . '" target="event">' . $summary . '</a>';
		} else {
			$text = $summary;
		}
		$postedit->setPost($text);
//var_dump($summary, $link, $text);
	}

	$postedit->localiseUrls();
	$postedit->localiseImages();
	$content = $postedit->getPost();

	$post->content_filtered = $content;
	$post->post_content = $content;
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

	$post->menu_order = 0;
	$post->mime_type = '';
	$post->guid = ''; 
	$categories = explode(',', (string)$row->section_name);
	$post->post_category = array_merge($categories, $keywordNodes);
// var_dump($keywordNodes);
// var_dump($post->post_category);die;
	$post->tags_input = prepare((string)$row->doc_subhead);
	$post->tax_input = '';

	$image = new Images($docId);
	$image->setUrl((string)$row->doc_image);

	$import->makePost($post);
	$import->attachImage($post, $image);
}

$import->writeCategories();
$import->writePosts();
$import->writeImages();

// DONE
