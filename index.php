<html>
<?php 

function isRegex($str0) {
	$regex = "/^\/[\s\S]+\/$/";
	return preg_match($regex, $str0);
}
function matchall($match,$name) { return true; }

$pruned_uri = $_SERVER['REQUEST_URI'];
if( $_SERVER['QUERY_STRING'] ) {
	$pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);
	$pruned_uri = substr($_SERVER['REQUEST_URI'],0,$pos-1);
}
$folder = str_replace($_SERVER['DOCUMENT_ROOT'],"",
		      str_replace("index.php","",$pruned_uri)
	);
$script_path = str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname($_SERVER["SCRIPT_FILENAME"]));
$target_folder = str_replace($script_path,getcwd(),$folder);
$script_path = str_replace("//","/","/".$script_path);
chdir( $target_folder  )
?>

<link rel="stylesheet" type="text/css" href="<?php echo $script_path."/res/theme.css"; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $script_path."/res/style.css"; ?>" />
<script language="javascript" type="text/javascript" src="<?php echo $script_path."/res/jquery.js" ?>" ></script>
<script language="javascript" type="text/javascript" src="<?php echo $script_path."/res/jquery-ui.js" ?>" ></script>
<script language="javascript" type="text/javascript" src="<?php echo $script_path."/res/style.js" ?>" ></script>
<script language="javascript" type="text/javascript">
$(function() {
          $(".numbers-row").append('<span class="button">+</span>&nbsp;&nbsp;<span class="button">-</span>');
          $(".button").click( function() {

                                  var $button = $(this);
                                  var oldValue = $button.parent().find("input").val();

                                  if ($button.text() == "+") {
                                          var newVal = parseFloat(oldValue) + 1;
                                  } else {
                                          // Don't allow decrementing below zero
                                          if (oldValue > 0) {
                                                  var newVal = parseFloat(oldValue) - 1;
                                          } else {
                                                  newVal = 0;
                                          }
                                  }

                                  $button.parent().find("input").val(newVal);
                                  $button.parent().parent().find("input").click();

                          });
});
</script>

<head>

</head>

<body>
<h1><?php echo substr($folder,1,-1)." on ".$_SERVER['SERVER_NAME'];  ?></h1>
<?php print "<a href=\"../\">[parent]</a> "; ?>
<?php
$has_subs = false;
$folders = array();
$allfiles = glob("*");
usort($allfiles, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
foreach ($allfiles as $filename) {
	if (is_dir($filename)) {
		$has_subs = true;
		array_push( $folders, $filename);
	}
}

if ($has_subs) {
    print "<div class=\"dirlinks\">\n";
    print "<h2>Subfolders\n";
    if( ! $_GET['depth'] || intval($_GET['depth']<2) ) {
	    print " <a href=\"?".$_SERVER['QUERY_STRING']."&depth=2\">(show plots in subfolders)</a>\n";
    } else {
	    print " <a href=\"?".$_SERVER['QUERY_STRING']."&depth=1\">(hide plots in subfolders)</a>\n";
    }
    print "</h2>\n";
    foreach ($folders as $filename) {
	    print " <a href=\"$filename\">[$filename]</a>";
    }
    print "</div>";
}

foreach (array("00_README.txt", "README.txt", "readme.txt") as $readme) {
    if (file_exists($readme)) {
        print "<pre class='readme'>\n"; readfile($readme); print "</pre>";
    }
}
?>

<h2><a name="plots">Plots</a></h2>
<p><form>Filter: <input type="text" name="match" size="30" 
	value="<?php if (isset($_GET['match'])) print htmlspecialchars($_GET['match']);  ?>" />
<input type="Submit" value="Go" />
<div class="numbers-row">
<label for="name">Levels to show</label>
<input name="depth" type="text" size="1" value="<?php if (isset($_GET['depth'])) { print htmlspecialchars($_GET['depth']); } else { print 1; } ?>" />
</div>
</form></p>
<div id="piccont">
<?php
$matchf = matchall;
$match = "";
if( isset($_GET['match']) ) {
	$match = $_GET['match'];
	if ( isRegex($match) ) {
		$matchf = preg_match;
		$match = $match;
	} else {
		$matchf = fnmatch;
		$match = '*'.$match.'*';
	}
}
$displayed = array();
if ($_GET['noplots']) {
    print "Plots will not be displayed.\n";
} else {
	$other_exts = array('.pdf', '.cxx', '.eps', '.ps', '.root', '.txt', ".C");
	$main_exts = array('.png','.gif','.jpg','.jpeg');
	$folders = array('*');
	if( intval($_GET['depth'])>1 ) {
		$wildc="*";
		for( $de=2; $de<=intval($_GET['depth']); $de++ ){
			$wildc = $wildc."/*";
			array_push( $folders, $wildc );
		}
	}
	$filenames = array();
	foreach ($folders as $fo) {
		foreach ($main_exts as $ex ) {
			$filenames = array_merge($filenames, glob($fo.$ex));
		}
	}
	sort($filenames);
	foreach ($filenames as $filename) {
		if( ! $matchf($match,$filename) ) { continue; }
		/// if (isset($_GET['match']) && !fnmatch('*'.$_GET['match'].'*', $filename)) continue;
		$path_parts = pathinfo($filename);
		if (PHP_VERSION_ID < 50200) {
			$path_parts['filename'] = str_replace('.'.$path_parts['extension'],"",$path_parts['basename']);
		}
		if( fnmatch("*_thumb", $path_parts['filename']) ) {
			continue;
		}
		$skip = false;
		foreach ($main_exts as $ex ){
			$other_filename=$path_parts['filename'].$ex;
			if( $other_filename == $path_parts['basename'] ) {
				break;
			} else if ( file_exists($other_filename) ) { 
				$skip = true;
				break;
			}
		}
		if( $skip ) { continue; }
		array_push($displayed, $filename);
		$others = array();
		$max=46;
		$asym=2;
		$len=strlen($filename);
		if ($len >= $max) {
			$short_filename=substr($filename, 0, $max/2-$asym). "..." .substr($filename, $len-1-$max/2-$asym,$len-1);
		} else {
			$short_filename=$filename;
		}
		$imgname=$path_parts['filename']."_thumb.".$path_parts['extension'];		
		if( !file_exists($imgname) ) {
			$imgname = $filename;
		} else {
			array_push($others, "<a class=\"file\" href=\"$filename\">[high res]</a>");
		}
		print "<div class='pic'>\n";
		print "<h3><a href=\"$filename\">$short_filename</a></h3>";
		// print "<a href=\"$filename\">";
		print "<img src=\"$imgname\" style=\"border: none; width: 40ex; \">";
		// print "</a>";
		                                         foreach ($other_exts as $ex) {
			                                 $other_filename = $path_parts['filename'].$ex;
			                                 if (file_exists($other_filename)) {
                                                         switch ($ex) {
                                                         case '.txt':                                                         
					$text = file_get_contents($other_filename);
					                 array_push($others, "<span class=\"txt\"><a class=\"file\" href=\"$other_filename\">[" . $ex . "]</a><span class=\"txt_cont\">". $text ."</span></span>");
                                                         break;
                                                         case '.root':
                                                         $pruned_uri = $_SERVER['REQUEST_URI'];
                                                         if( $_SERVER['QUERY_STRING'] ) {
	                                                 $pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);
	                                                 $pruned_uri = substr($_SERVER['REQUEST_URI'],0,$pos-1);
                                                         }
                                                         $folder = str_replace($_SERVER['DOCUMENT_ROOT'],"",
		                                         str_replace("index.php","",$pruned_uri)
	                                                 );
					                 array_push($others, "<a href=https://spigazzi.web.cern.ch/spigazzi/jsroot/index.htm?file=$folder$other_filename>[" . $ex . "]</a>");
					                 array_push($displayed, $other_filename);
                                                         break;
                                                         default :
					array_push($others, "<a class=\"file\" href=\"$other_filename\">[" . $ex . "]</a>");
					                 array_push($displayed, $other_filename);
				}
			}
		}
		if ($others) { print "<p>Also as ".implode(', ',$others)."</p>"; }
		else { print "<p>Also as [none]</p>"; }
		print "</div>";
	}
}
?>
</div>
<div style="display: block; clear:both;">
<h2><a name="files">Other files</a></h2>
<ul>
<?
foreach ($allfiles as $filename) {
    if ($_GET['noplots'] || !in_array($filename, $displayed)) {
	    /// if (isset($_GET['match']) && !fnmatch('*'.$_GET['match'].'*', $filename)) continue;
	    if( ! $matchf($match,$filename) ) { continue; }
	    if( fnmatch("*_thumb.*", $filename) ) {
		    continue;
	    }
	if ( substr($filename,-1) == "~" ) continue;
        if (is_dir($filename)) {
		// print "<li>[DIR] <a href=\"$filename\">$filename</a></li>";
        } else {
            print "<li><a href=\"$filename\">$filename</a></li>";
        }
    }
}
?>
</ul>
</div>
<p>
Like this page? <a href="https://github.com/musella/php-plots">Get it here.</a><br />
Credits: Giovanni Petrucciani.
</p>
</body>
</html>
