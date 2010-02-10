<?php
//header('Content-Type: application/x-json;charset=utf-8');

$twitter_request="http://search.twitter.com/search.json?q=the&rpp=50";
//$twitter_request="http://localhost:8888/flitter/search.json";
$twitter_json = file_get_contents($twitter_request);

//echo "<pre>";
//var_dump(json_decode($twitter_json,true));
//echo "</pre>";
$twitter_result=json_decode($twitter_json,true);
//echo "<pre>";
//var_export($twitter_result);
//echo "</pre>";
$created_at=$twitter_result['results'][0]['created_at'];
echo $created_at." -- ".date("Y-m-d h:n:s",strtotime($created_at)+1);
die();


$my_twit=null;
$i=0;
$regexp_min_length="/(\b\w{3,}\b)+/";
$regexp_no_at_http_short="/@|\bhttp/";
while(!$mytwit && $i<count($twitter_result['results'])) {
	if(
		preg_match($regexp_min_length,$twitter_result['results'][$i]['text']) && 
		!preg_match($regexp_no_at_http_short,$twitter_result['results'][$i]['text']) &&
		!in_array($twitter_result['results'][$i]['id'],explode("|",$_GET['twts'])) &&
		$twitter_result['results'][$i]['to_user']==null
	) {
		$mytwit=$twitter_result['results'][$i]['text'];
		
		$twitter=array(
			"t"=>utf8_encode($mytwit),
			"u"=>utf8_encode($twitter_result['results'][$i]['from_user']),
			"twts"=>ltrim(implode("|",array_slice(array_merge(array($twitter_result['results'][$i]['id']),explode("|",$_GET['twts'])),0,6)),"|")
		);
	}
		
	$i++;
}
//echo $mytwit;

$regexp_short="/\b(\w{1,3})\b/";
preg_match_all($regexp_short,$mytwit,$short_matched);
//echo "<p>Short:";
//var_export($short_matched[1]);
//echo "</p>";

$regexp_long="/\b(\w{6,})\b/";
preg_match_all($regexp_long,$mytwit,$long_matched);
//echo "<p>Long:";
//var_export($long_matched[1]);
//echo "</p>";

$regexp_longer="/\b(\w{9,})\b/";
preg_match_all($regexp_longer,$mytwit,$longer_matched);
//echo "<p>Longer:";
//var_export($longer_matched[1]);
//echo "</p>";

$regexp_longest="/\b(\w{12,})\b/";
preg_match_all($regexp_longest,$mytwit,$longest_matched);
//echo "<p>Longest:";
//var_export($longest_matched[1]);
//echo "</p>";

$regexp_uppercase="/\b(\w*[A-Z]+\w{2,})\b/";
preg_match_all($regexp_uppercase,$mytwit,$uppercase_matched);
//echo "<p>Uppercase:";
//var_export($uppercase_matched[1]);
//echo "</p>";

$regexp_symbols="/(\w{2,})[!?,\.:;_\-\=]/";
preg_match_all($regexp_symbols,$mytwit,$symbols_matched);
//echo "<p>Symbol:";
//var_export($symbols_matched[1]);
//echo "</p>";

$regexp_quotes="/[\"\'`](\w{2,})[\'\"`]/";
preg_match_all($regexp_quotes,$mytwit,$regexp_quotes);
//echo "<p>Quotes:";
//var_export($regexp_quotes[1]);
//echo "</p>";

$tmp_tags=array();
$strong_tags=array();
foreach(array_merge($uppercase_matched[1],$symbols_matched[1],$regexp_quotes[1],$short_matched[1],$long_matched[1],$longer_matched[1],$longest_matched[1]) AS $tag) {
	if(!$tmp_tags[$tag]) {
		$tmp_tags[$tag]++;
	}
	$strong_tags[$tag]++;	
	
	if(in_array($tag,$uppercase_matched[1])){
		$tmp_tags[$tag]+=0.55;
		$strong_tags[$tag]+=0.85;
	}
	
	if(in_array($tag,$symbols_matched[1])){
		$tmp_tags[$tag]+=0.25;
		$strong_tags[$tag]+=0.5;
	}
	if(in_array($tag,$regexp_quotes[1])){
		$tmp_tags[$tag]+=0.5;
		$strong_tags[$tag]+=0.75;
	}
	if(in_array($tag,$short_matched[1])){
		$tmp_tags[$tag]-=0.5;
		$strong_tags[$tag]-=0.8;
	}
	if(in_array($tag,$long_matched[1])){
		$tmp_tags[$tag]+=0.1;
		$strong_tags[$tag]+=0.1;
	}
	if(in_array($tag,$longer_matched[1])){
		$tmp_tags[$tag]+=0.2;
		$strong_tags[$tag]+=0.2;
	}
	if(in_array($tag,$longest_matched[1])){
		$tmp_tags[$tag]+=0.3;
		$strong_tags[$tag]+=0.3;
	}
}
//var_export($strong_tags);

function clean($tag) {
	global $avg;
	return ($tag>=$avg);
}

arsort($tmp_tags);
arsort($strong_tags);

// create the weak tags array
reset($tmp_tags);
$max=current($tmp_tags);
end($tmp_tags);
$min=current($tmp_tags);
$avg=array_sum($tmp_tags)/count($tmp_tags);
$tags=array_filter($tmp_tags,"clean");

// create the strong tags array
reset($strong_tags);
$max=current($strong_tags);
end($strong_tags);
$min=current($strong_tags);
$avg=array_sum($strong_tags)/count($strong_tags);
$s_tags=array_slice(array_filter($strong_tags,"clean"),0,2);


$flickr_key="01cc21c83d6c92f5d00c8e4c1426c2ff";

//call the flickr web service with the strong tags first
$flickr_search_tags=implode(",",array_keys($s_tags));
$flickr_search_text=implode(" ",array_keys($s_tags));

//echo "<p>".$flickr_search_tags."</p>";

$flickr_tag_mode="all";
$flickr_request="http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=".$flickr_key."&machine_tag_mode=any&media=photos&format=json&page=1&per_page=1&privacy_filter=1&nojsoncallback=1&tag_mode=".$flickr_tag_mode."&tags=".$flickr_search_tags;
//echo $flickr_request;
//$flickr_request="http://localhost:8888/flitter/flickr_search.json";
$flickr_json = file_get_contents($flickr_request);

$flickr_search=json_decode($flickr_json,true);

if(!count($flickr_search['photos']['photo'])) {
	//if the strong search does not work perform a weak search
	//call the flickr web service with the weak tags
	$flickr_search_tags=implode(",",array_keys($tags));
	$flickr_search_text=implode(" ",array_keys($tags));
	
	//echo "<p>".$flickr_search_tags."</p>";
	
	$flickr_tag_mode="any";
	
	$flickr_request="http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=".$flickr_key."&machine_tag_mode=any&media=photos&format=json&page=1&per_page=1&privacy_filter=1&nojsoncallback=1&tag_mode=".$flickr_tag_mode."&tags=".$flickr_search_tags;
	//echo $flickr_request;
	//$flickr_request="http://localhost:8888/flitter/flickr_search.json";
	$flickr_json = file_get_contents($flickr_request);

	$flickr_search=json_decode($flickr_json,true);
}

//var_export($flickr);
if(count($flickr_search['photos']['photo'])) {
	$pic_id=$flickr_search['photos']['photo'][0]['id'];
	//echo "id: $pic_id";
	$flickr_info_request="http://api.flickr.com/services/rest/?method=flickr.photos.getinfo&api_key=".$flickr_key."&photo_id=".$pic_id."&format=json&nojsoncallback=1";
	//$flickr_info_request="http://localhost:8888/flitter/flickr_info.json";
	$flickr_info_json = file_get_contents($flickr_info_request);
	
	$flickr_info=json_decode($flickr_info_json,true);
	//echo "<pre>";
	//var_export($flickr_info);
	//echo "</pre>";
	$flickr=array(
		"user"=>($flickr_info['photo']['owner']['realname']!='')?$flickr_info['photo']['owner']['realname']:$flickr_info['photo']['owner']['username'],
		"title"=>$flickr_info['photo']['title']['_content'],
		"date"=>date("Y-m-d",$flickr_info['photo']['dates']['posted']),
		"tag_mode"=>$flickr_tag_mode,
		"uid"=>$flickr_info['photo']['owner']['nsid'],
		"src"=>"http://farm".$flickr_info['photo']['farm'].".static.flickr.com/".$flickr_info['photo']['server']."/".$flickr_info['photo']['id']."_".$flickr_info['photo']['secret'].".jpg"
		//"src"=>"http://localhost:8888/flitter/pics/".$flickr_info['photo']['id']."_".$flickr_info['photo']['secret'].".jpg"
		//"src"=>"http://localhost:8888/flitter/pics/2930412011_4575292843.jpg"
		);
						
	$flitter=array("tags"=>implode(",",array_keys($tags)),"twitter"=>$twitter,"flickr"=>$flickr);
	
	echo "ompic.deliveredPic(".json_encode($flitter).");";
} else {
	
}

?>