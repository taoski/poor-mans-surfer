<?php

ini_set('xdebug.var_display_max_depth', -1 );
ini_set('error_reporting', E_ALL ^ E_NOTICE);
//error_reporting(E_ALL ^ E_NOTICE);

$nakedurls = 'https://nymag.com/strategist/article/best-cat-litter.html
https://www.petmoneysaver.co.uk/blog/best-cat-litter/
https://www.petsathome.com/shop/en/pets/cat/kitten/kitten-litter
https://www.battersea.org.uk/pet-advice/cat-advice/cat-litter-trays
https://allaboutcats.com/best-cat-litter-for-kittens
https://www.foundanimals.org/best-litter-for-kittens/
https://www.cathealth.com/cat-care/safety/2300-best-litter-for-kittens-is-clumping-litter-safe
https://www.bustle.com/p/the-4-best-non-clumping-cat-litters-for-kittens-22947267
https://www.kittycatter.com/best-litter-for-kittens/
https://wereallaboutpets.com/best-cat-litter-for-kittens';

$urls = explode("\n",$nakedurls);

if($_GET['parse'] == "no")
{

// var_dump($urls);

$count = 0;

foreach ($urls as $geturl) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$geturl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 3);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,3);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $pagecontents  = curl_exec($ch);
  //$pagecontents = file_get_contents($geturl);
  file_put_contents($count.".txt",$pagecontents);
  $count++;
}
}

if($_GET['parse'] == "yes")

{
$filecount = 0;
$all_text = "";
  do {
    $html = "";
    //$errors = file_get_contents($error);
    $heading_count = 0;
    error_log("getting file:".$filecount.".txt");
    $html = file_get_contents($filecount.".txt");

    //add url to array
    $masterlist[$filecount]['url'] = $urls[$filecount];

    $pattern = "/<p>(.*?)<\/p>/"; // Global & Multiline
    preg_match_all($pattern,$html,$matchesp1,PREG_PATTERN_ORDER);
    $masterlist[$filecount]['paragraph_count'] = count($matchesp1[1]);
    foreach($matchesp1[1] as $item)
    {
      $item = preg_replace("/<(.*?)>/","",$item);
      $item = preg_replace("/\{(.*?)\}/","",$item);
      $item = preg_replace("/\[(.*?)\]/","",$item);
      //echo "<textarea>".$item. "</textarea></br>";
      $masterlist[$filecount]['p'][]  = $item;//echo "<pre>".strip_tags($item). "</pre></br>";
      $masterlist[$filecount]['alltext']  .= $item . " ";//echo "<pre>".strip_tags($item). "</pre></br>";
    }

    $pattern = "/<p\s.*?>(.*?)<\/p>/"; // Global & Multiline

    preg_match_all($pattern,$html,$matchesp2,PREG_PATTERN_ORDER);
    $masterlist[$filecount]['paragraph_count'] = $masterlist[$filecount]['paragraph_count'] + count($matchesp2[1]);

    //var_dump($matches[1]);
    foreach($matchesp2[1] as $item)
    {
      $item = preg_replace("/<(.*?)>/","",$item);
      $item = preg_replace("/\{(.*?)\}/","",$item);
      $item = preg_replace("/\[(.*?)\]/","",$item);
      //echo "<textarea>".$item. "</textarea></br>";
      $masterlist[$filecount]['p'][] = $item;//echo "<pre>".strip_tags($item). "</pre></br>";
      $masterlist[$filecount]['alltext']  .= $item . " ";//echo "<pre>".strip_tags($item). "</pre></br>";
    }
    //array_unique($sorting);
    //var_dump($sorting);
    $masterlist[$filecount]['p'] = array_unique($masterlist[$filecount]['p']);


    $site_headings = getHeadingTags($html);
    $masterlist[$filecount]['headings'] = $site_headings;
    $masterlist[$filecount]['headings']['h1'] = array_unique($masterlist[$filecount]['headings']['h1']);
    $masterlist[$filecount]['headings']['h2'] = array_unique($masterlist[$filecount]['headings']['h2']);
    $masterlist[$filecount]['headings']['h3'] = array_unique($masterlist[$filecount]['headings']['h3']);
    $masterlist[$filecount]['headings']['h4'] = array_unique($masterlist[$filecount]['headings']['h4']);

      foreach($filecount['headings']['h1'] as $item){$head[] = $item;}
      foreach($filecount['headings']['h2'] as $item){$head[] = $item;}
      foreach($filecount['headings']['h3'] as $item){$head[] = $item;}
      foreach($filecount['headings']['h4'] as $item){$head[] = $item;}

      $head = array_unique($head);
      foreach($head as $item){$masterlist[$filecount]['alltext'] = $item . " ";}

      $masterlist[$filecount]['alltext'] = strtolower($masterlist[$filecount]['alltext']);

      $masterlist[$filecount]['alltext'] = html_entity_decode($masterlist[$filecount]['alltext'], ENT_QUOTES | ENT_XML1, 'UTF-8');

      $masterlist[$filecount]['alltext'] = preg_replace("/[^a-z0-9\'\’]/", ' ', $masterlist[$filecount]['alltext']);


      $masterlist[$filecount]['alltext'] = removeCommonWords($masterlist[$filecount]['alltext']);
      $masterlist[$filecount]['alltext'] = preg_replace('/[^\da-z\s]/i', '', $masterlist[$filecount]['alltext']);
      $masterlist[$filecount]['alltext'] = preg_replace("/\s+/", ' ', $masterlist[$filecount]['alltext']);

      //echo "<textarea>".$masterlist[$filecount]['alltext']."</textarea>";
    //$sorting = array_unique($sorting);

    $pattern = "/<img.*?>/"; // Global & Multiline
    $masterlist[$filecount]['image_count'] = preg_match_all($pattern,$html);
    $imgcount[] = $masterlist[$filecount]['image_count'];

    $pattern = "/<ol.*?>/"; // Global & Multiline
    $masterlist[$filecount]['ol_count'] = preg_match_all($pattern,$html);
    $olcount[] = $masterlist[$filecount]['ol_count'];

    $pattern = "/<ul.*?>/"; // Global & Multiline
    $masterlist[$filecount]['ul_count'] = preg_match_all($pattern,$html);
    $ulcount[] = $masterlist[$filecount]['ul_count'];

   //echo "<textarea>.".$clean_html."</textarea>";
    //add word count to array
    $wordcount = preg_split("/[\s,]+/", $masterlist[$filecount]['alltext']);
    $masterlist[$filecount]['word_count'] = count($wordcount);
    $wcount[] = count($wordcount);

    //echo "<textarea>".$masterlist[$filecount]['alltext']."</textarea>";

    $h1count[] = count($masterlist[$filecount]['headings']['h1']);
    $h2count[] = count($masterlist[$filecount]['headings']['h2']);
    $h3count[] = count($masterlist[$filecount]['headings']['h3']);
    $h4count[] = count($masterlist[$filecount]['headings']['h4']);

    $pcount[] = count($masterlist[$filecount]['p']);
    //$wcount[] = count($masterlist[$filecount]['word_count']);

    $filecount++;


  } while ($filecount < 10);

}



$array = array_filter($h1count);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "H1 = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($h2count);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "H2 = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($h3count);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "H3 = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($h4count);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "H4 = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($pcount);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "P = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($wcount);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "Words = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($imgcount);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "Images = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($olcount);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "OL = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);

$array = array_filter($ulcount);
$array = remove_outliers($array);
sort($array);
$average = array_sum($array)/count($array);
echo "UL = (".$array[0]."-".$array[count($array)-1].") (".round($average).")<br>";
unset($array);
// work out averages for headings etc.



foreach($masterlist as $item)
{
  $output_all_text .= $item['alltext']  . " ";
}

$limit = 30;

echo "<table border=1>";
echo "<tr>";
echo "<td>1</td><td>2</td><td>3</td><td>4</td><td>5</td>";
echo "</tr>";
echo "<td>";
$phrasecount = getPhraseCount($output_all_text,1,$limit); // return top 10 list of 2 word phrase count
foreach($phrasecount as $itemtitle => $itemcountout)
{
  echo $itemtitle;
  $filecount = 0;
  do
  {
    $pattern = "/\s".$itemtitle."\s/i";
    $itemtitlecount = preg_match_all($pattern,$masterlist[$filecount]['alltext']);
    $array[] = $itemtitlecount;

  $filecount++;
} while ($filecount <10);

$array = array_filter($array);
sort($array);
echo " (".$array[0]."-".$array[count($array)-1].") (".$itemcountout.")<br>";
unset($array);

}

echo "</td>";
echo "<td>";
$phrasecount = getPhraseCount($output_all_text,2,$limit); // return top 10 list of 2 word phrase count
foreach($phrasecount as $itemtitle => $itemcountout)
{
  echo $itemtitle;
  $filecount = 0;
  do
  {
    $pattern = "/\s".$itemtitle."\s/i";
    $itemtitlecount = preg_match_all($pattern,$masterlist[$filecount]['alltext']);
    $array[] = $itemtitlecount;

  $filecount++;
} while ($filecount <10);

$array = array_filter($array);
sort($array);
echo " (".$array[0]."-".$array[count($array)-1].") (".$itemcountout.")<br>";
unset($array);
}
echo "</td>";
echo "<td>";
$phrasecount = getPhraseCount($output_all_text,3,$limit); // return top 10 list of 2 word phrase count
foreach($phrasecount as $itemtitle => $itemcountout)
{
  echo $itemtitle;
  $filecount = 0;
  do
  {
    $pattern = "/\s".$itemtitle."\s/i";
    $itemtitlecount = preg_match_all($pattern,$masterlist[$filecount]['alltext']);
    $array[] = $itemtitlecount;

  $filecount++;
} while ($filecount <10);

$array = array_filter($array);
sort($array);
echo " (".$array[0]."-".$array[count($array)-1].") (".$itemcountout.")<br>";
unset($array);
}
echo "</td>";
echo "<td>";
$phrasecount = getPhraseCount($output_all_text,4,$limit); // return top 10 list of 2 word phrase count
foreach($phrasecount as $itemtitle => $itemcountout)
{
  echo $itemtitle;
  $filecount = 0;
  do
  {
    $pattern = "/\s".$itemtitle."\s/i";
    $itemtitlecount = preg_match_all($pattern,$masterlist[$filecount]['alltext']);
    $array[] = $itemtitlecount;

  $filecount++;
} while ($filecount <10);

$array = array_filter($array);
sort($array);
echo " (".$array[0]."-".$array[count($array)-1].") (".$itemcountout.")<br>";
unset($array);
}
echo "</td>";
echo "<td>";
$phrasecount = getPhraseCount($output_all_text,5,$limit); // return top 10 list of 2 word phrase count
foreach($phrasecount as $itemtitle => $itemcountout)
{
  echo $itemtitle;
  $filecount = 0;
  do
  {
    $pattern = "/\s".$itemtitle."\s/i";
    $itemtitlecount = preg_match_all($pattern,$masterlist[$filecount]['alltext']);
    $array[] = $itemtitlecount;

  $filecount++;
} while ($filecount <10);

$array = array_filter($array);
sort($array);
echo " (".$array[0]."-".$array[count($array)-1].") (".$itemcountout.")<br>";
unset($array);
}
echo "</td></tr></table>"	;


//var_dump($masterlist);



function getHeadingTags($html)
{
    preg_match_all( "#<h(\d)[^>]*?>(.*?)<[^>]*?/h\d>#i",
                    $html,
                    $matches,
                    PREG_PATTERN_ORDER
                  );
    $headings = array();
    foreach ($matches[1] as $key => $heading_key) {
        $headings["h$heading_key"][] = htmlspecialchars_decode(strip_tags($matches[2][$key]));
    }

    ksort($headings);
    //array_unique($headings);
    return $headings;
}

function cleanUp($start,$finish,$html)
{
do {
  $first = stripos($html,$start);
  $last = stripos($html,$finish,$first);
  $length = $last - $first + strlen($finish);
  $toremove = substr($html,$first,$length);
  $html = str_replace($toremove,"",$html);
} while ($first <> 0);
$html = str_replace("\t"," ",$html);
return $html;

}

function getPhraseCount($string, $numWords=1, $limit=0) {
  // make case-insensitive
  $string = strtolower($string);
  // get all words. Assume any 1 or more letter, number or ' in a row is a word
  preg_match_all("~[a-z0-9\'\’]+~",$string,$words);
  $words = $words[0];
  // foreach word...
  foreach($words as $k => $v) {
    // remove single quotes that are by themselves or wrapped around the word
    $words[$k] = trim($words[$k],"'");
  } // end foreach $words
  // remove any empty elements produced from ' trimming
  $words = array_filter($words);
  // reset array keys
  $words = array_values($words);
  // foreach word...
  foreach ($words as $k => $word) {
    // if there are enough words after the current word to make a $numWords length phrase...
    if (isset($words[$k+$numWords])) {
      // add the phrase to list of phrases
      $phrases[] = implode(' ',array_slice($words,$k,$numWords));
    } // end if isset
  } // end foreach $words
  // create an array of phrases => count
  $x = array_count_values($phrases);
  // reverse sort it (preserving keys, since the keys are the phrases
  arsort($x);
  // if limit is specified, return only $limit phrases. otherwise, return all of them
  return ($limit > 0) ? array_slice($x,0,$limit) : $x;
} // end getPhraseCount

function removeCommonWords($input){

 	// EEEEEEK Stop words
	$commonWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');
  $input = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $input)));
	return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$input);

}

function remove_outliers($dataset, $magnitude = 1) {

  $count = count($dataset);
  $mean = array_sum($dataset) / $count; // Calculate the mean
  $deviation = sqrt(array_sum(array_map("sd_square", $dataset, array_fill(0, $count, $mean))) / $count) * $magnitude; // Calculate standard deviation and times by magnitude

  return array_filter($dataset, function($x) use ($mean, $deviation) { return ($x <= $mean + $deviation && $x >= $mean - $deviation); }); // Return filtered array of values that lie within $mean +- $deviation.
}

function sd_square($x, $mean) {
  return pow($x - $mean, 2);
}

?>
