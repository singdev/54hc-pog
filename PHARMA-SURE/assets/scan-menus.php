<?php
	function check_array($exclude, $file) {
						foreach($exclude as $key => $arrayItem){
							if (strpos($file, $arrayItem) !== false) {
								return true;
							}		
						}		
					}		
	function scan($dir) {
		$avoidpages=trim($_GET['avoidpages']);
		if (!empty($avoidpages)) $exclude=explode(",",$avoidpages);
		else $exclude=array('');
		
		//$exclude = array('serverprobleme','werneck','ebern','rimpar','volkach','eltmann','knetzgau','arnstein','massbach','karlstadt','hassberge','kitzingen','wuerzburg','dettelbach','koenigsberg','mainfranken','gerolzhofen','wiesentheid','muennerstadt','unterfranken','maroldsweisach','stadtlauringen','hassfurt','bad-koenigshofen','neustadt','hofheimufr','bad-kissingen','it-techniker','azubi-fachinformatiker-systemintegration','karriere','links','impressum','datenschutz','kontakt','computer-service-fuer-arzt-praxen','index');		
					$action=1;
					$scanned_files[] = $dir;
					$files = scandir($dir);					
					if(!is_array($files)) {
						throw new Exception('Unable to scan directory ' . $dir . '.  check permissions.');
					}	
					foreach($files as $file) {			
							if ((strpos($dir, 'phone') !== false)||(strpos($dir, 'tablet') !== false)) {
								//Avoid /phone/ & /tablet/ folders in adaptive sites
								//echo $dir."/".$file;
							}
							else {
								
							//Just scan .html & htm files						
								if (((strpos($file, 'html') !== false)||(strpos($file, 'htm') !== false))&&((check_array($exclude, $file))==false)) {	
									if(is_file($dir.'/'.$file) && !in_array($dir.'/'.$file,$scanned_files)) {	
										//echo "<BR>PATH:".$dir.'/'.$file;			
										//if (check_array($exclude, $file)) echo "banned page";
										
										if (filesize($dir.'/'.$file)<=1000000) { /*echo $file;*/ @check(file_get_contents($dir.'/'.$file),$dir.'/'.$file,$action);}
										//else echo "<br>Big file halted: $dir/$file ".filesize($dir.'/'.$file);
									} elseif(is_dir($dir.'/'.$file) && substr($file,0,1) != '.') {
										//echo $dir.'/'.$file;		
										scan($dir.'/'.$file);
									}	
								} elseif(is_dir($dir.'/'.$file) && substr($file,0,1) != '.') {
										//echo $dir.'/'.$file;		
										scan($dir.'/'.$file);
									}	
								
							}							
						}				
	}	
	function pathToURL($path) {
	  //Replace backslashes to slashes if exists, because no URL use backslashes
	  $path = str_replace("\\", "/", realpath($path));
	  //echo "<hr>";
	  //$_SERVER['DOCUMENT_ROOT'];
	  //echo "<hr>";

	  //if the $path does not contain the document root in it, then it is not reachable
	  $pos = strpos($path, $_SERVER['DOCUMENT_ROOT']);
	  if ($pos === false) return false;

	  //just cut the DOCUMENT_ROOT part of the $path
	  return substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
	  //Note: usually /images is the same with http://somedomain.com/images,
	  //      So let's not bother adding domain name here.
	}
	
	function pathToURL2($path) {
	  //Replace backslashes to slashes if exists, because no URL use backslashes
	  $path = str_replace("\\", "/", realpath($path));
	  //echo "<hr>";
	  //$_SERVER['DOCUMENT_ROOT'];
	  //echo "<hr>";

	  //if the $path does not contain the document root in it, then it is not reachable
	  $pos = strpos($path, realpath($_SERVER['DOCUMENT_ROOT']));
	  if ($pos === false) return false;

	  //just cut the DOCUMENT_ROOT part of the $path
	  return substr($path, strlen(realpath($_SERVER['DOCUMENT_ROOT'])));
	  //Note: usually /images is the same with http://somedomain.com/images,
	  //      So let's not bother adding domain name here.
	}
	
	function url(){
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

?>

<?php						
				function check($contents,$file,$action) {
					$scanned_files[] = $file;		
					@$GLOBALS['countfiles']++;
					//Ignore this file
					if ($file !=__FILE__) {
						//$keybuscada=" ".@$_GET['keyword']." ";
						$keybuscada="".@$_GET['keyword']."";					
						$title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $contents, $matches) ? $matches[1] : null;						
						$thumb = preg_match('/<meta property="og:image" content="(.*?)" \/>/', $contents, $matches) ? $matches[1] : null;						
						$description = preg_match('/<meta property="og:description" content="(.*?)" \/>/', $contents, $matches) ? $matches[1] : null;
						//preg_match_all('~<\s*meta\s+property="(og:[^"]+)"\s+content="([^"]*)~i', $str, $matches);					
						//Key Plain Text
						if (!empty($keybuscada)){																					
							//body only, after menu
							//preg_match('/<nav[^>]*>(.*?)<\/body>/ims', $contents, $matches);							
							preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $contents, $matches);
							$bodycontent = $matches[1];		
							//Deletes menu content
							//$bodycontent=preg_replace('/<nav[\s\S]+?\/nav>/', '', $bodycontent);						
								//common tags							
								if (preg_match_all("/<(h1|h2|h3|h4|h5|p|title|span)(.*?)$keybuscada.*<\/(h1|h2|h3|h4|h5|p|title|span)>/i", $bodycontent, $matches2)) {									
										//echo $file;
										//echo "<hr><hr><hr><hr><hr>";
										//echo $file."FILE --->";
										$link = pathToURL($file);
										if (empty($link)) $link = pathToURL2($file);
										//echo realpath($file)." <- REAL ---";;
										//echo "<hr><hr><hr><hr><hr>";
										$filename= basename($file, '.html'); //$filename
										//Debug										
										//echo "<hr><textarea style='width:100%;height:30px;'>".$matches2[2]."</textarea><hr>";										
										//Get last match of document (avoid menu links)
										//var_dump($matches2);
										//$lastmatch=$matches2[0][count($matches2[0])-1];											
												$base=$_SERVER['SERVER_NAME'];
												echo "<a target='_blank' href='$link'><img class='thumbsearch' src='$thumb'></a><p class='titlesearch'><a href='$link'>$title</a></p><p class='descriptionsearch'>$description</p><p class='urlsearch'>$base$link</p><div class='separadorsearch'></div>";
												@$GLOBALS['counttotalfiles']++;													
									}
						}	
					}
				}
?>			
<?php
if (isset($_GET['keyword'])){
	//echo "<br>---".realpath($_SERVER['DOCUMENT_ROOT'])."---<br>";		
	$busqueda=trim($_GET['keyword']);
	$path=dirname(__FILE__);	
	$current = basename(dirname($_SERVER['PHP_SELF']));
	$definitePath=str_replace("/".$current,"",$path);	
	scan($definitePath);	
	if (@$GLOBALS['counttotalfiles']==0) { echo "<p>Your search did not match any documents.</p><p class='totalresults'><b>".@$GLOBALS['counttotalfiles']. " 0 results</b></p>"; }
	else echo "<p class='totalresults'><b>".@$GLOBALS['counttotalfiles']. " results</b></p>";
}
?>