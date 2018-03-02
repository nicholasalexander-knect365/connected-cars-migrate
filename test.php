<?php 

$url = 'https://usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment'; 
$url = 'https://www.prnewswire.com/news-releases/qualcomm-and-leading-automotive-companies-across-the-globe-drive-the-commercialization-of-c-v2x-300602527.html';
$url = 'https://www.theconnectedcar.com/author.asp?section_id=627&amp;doc_id=740672';

var_dump(parse_url($url));
if ($url === unparse_url(parse_url($url))) { 
  print "YES, they match!\n"; 
} 

function unparse_url($parsed_url) { 
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
  $pass     = ($user || $pass) ? "$pass@" : ''; 
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

  return "$scheme$user$pass$host$port$path$query$fragment"; 
}