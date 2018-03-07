<?php

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

unlink($pagesXml . '.out');
unlink($pagesXml . '.old');

$inFd = fopen($pagesXml, 'r');
$outFd = fopen($pagesXml . '.out', 'w');

$testing = false;

while($line = fgets($inFd)) {
	if ( $testing && $c = preg_match('/a href\=http[^\s]+ target/', $line) ) {
		if ($c !== 0) {
			print $line;
		}
	}
	$revised = preg_replace('/a href\=http([^\s]+) target/', 'a href="http${1}" target', $line);
	if ($revised !== $line) {
		print "\n" . $line;
	}	
	fputs($outFd, $revised);
}
fclose($inFd);
fclose($outFd);

rename($pagesXml, $pagesXml . '.old');
rename($pagesXml . '.out', $pagesXml);

