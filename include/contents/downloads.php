<?php 
#   Copyright by: Manuel Staechele
#   Support: www.ilch.de


defined ('main') or die ( 'no direct access' );


function get_cats_title ( $catsar ) {
  $l = '';
	foreach($catsar as $k => $v) {
	  if ( $k != '' AND $v != '' ) {
		  $l = $v.' :: '.$l;
		}
	}
	return ($l);
}

function get_cats_urls ( $catsar ) {
  $l = '';
	foreach($catsar as $k => $v) {
	  if ( $k != '' AND $v != '' ) {
		  $l = '<a class="smalfont" href="?downloads-'.$k.'">'.$v.'</a><b> &raquo; </b>'.$l;
		}
	}
	return ($l);
}

function get_cats_array ( $cid , $ar ) {
	if ( empty($cid) ) {
	  return ($ar);
	} else { 
	  $erg = db_query("SELECT cat,id,name FROM prefix_downcats WHERE id = ".$cid);
		$row = db_fetch_assoc($erg);
		$ar[$row['id']] = $row['name'];
	  return ( get_cats_array($row['cat'],$ar) );
	}
	if ( $r ) {
	  return ($l);
	}
}

function get_download_size($file) {
  $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");  
  $size = @filesize($file); if ($size == 0) { return('n/a'); } else {
  return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
}

function count_files ($cid) {
  $zges = 0;
	$e = db_query("SELECT id FROM prefix_downcats WHERE cat = ".$cid);
	if ( db_num_rows($e) > 0 ) {
	  while ($r = db_fetch_assoc($e) ) {
	    $zges = $zges + count_files ( $r['id'] );
	  }
	}
	$zges = $zges + db_count_query("SELECT COUNT(*) FROM prefix_downloads WHERE cat = ".$cid);
	return ( $zges );
}



switch ( $menu->get(1) ) {
  default :
		$cid = ( $menu->get(1) ? escape($menu->get(1), 'integer') : 0 );
		$erg = db_query("SELECT cat,name FROM prefix_downcats WHERE id = ".$cid." ORDER BY pos");
		if ( db_num_rows($erg) > 0 ) {
		  $row = db_fetch_assoc($erg);
			$array = get_cats_array($row['cat'],'');
			if ( !empty($array) ) {
			  $titelzw = get_cats_title($array);
			  $namezw = get_cats_urls($array);
			} else {
			  $titelzw = '';
				$namezw = '';
			}
		  $cattitle = ':: '.$titelzw.$row['name'];
			$catname = '<b> &raquo; </b>'.$namezw.$row['name'];	
		} else {
		  $cattitle = '';
			$catname = '';
		}
		$title = $allgAr['title'].' :: Downloads '.$cattitle;
    $hmenu = '<a class="smalfont" href="?downloads">Downloads</a>'.$catname;
    $design = new design ( $title , $hmenu );
	  $design->header();
		$tpl = new tpl ('downloads' );
    $tpl->set('cid', $cid);
    $erg = db_query("SELECT id,name,`desc` FROM prefix_downcats WHERE cat = ".$cid." AND recht >= ".$_SESSION['authright']." ORDER BY pos");
    if ( db_num_rows($erg) > 0 ) {
		  $tpl->out(1); $class = 'Cnorm';
			while ($row = db_fetch_assoc($erg) ) {
			  $row['files'] = count_files($row['id']);
			  $class = ( $class == 'Cmite' ? 'Cnorm' : 'Cmite' );
				$row['class'] = $class;
			  $tpl->set_ar_out($row,2);
      }
		  $tpl->out(3);
		}
		
    # sortierung festlegen
    $sortierung = 'pos ASC';
    $DOM = 'ASC';
    $POM = 'ASC';
    $DAM = 'ASC';
    
    switch ($menu->get(2)) {
      case 'positionDESC' : $sortierung = 'pos DESC'; break;
      case 'positionASC' : $sortierung = 'pos ASC';  $POM = 'DESC'; break;
      case 'downsDESC' : $sortierung = 'downs DESC'; break;
      case 'downsASC' : $sortierung = 'downs ASC'; $DOM = 'DESC'; break;
      case 'dateDESC' : $sortierung = 'time DESC'; break;
      case 'dateASC' : $sortierung = 'time ASC'; $DAM = 'DESC'; break;
    }

    $tpl->set ('POM', $POM);
    $tpl->set ('DOM', $DOM);
    $tpl->set ('DAM', $DAM);
    
		$erg = db_query("select id,name,version,ssurl,`desc`,downs,DATE_FORMAT(time,'%d.%m.%Y') as datum from prefix_downloads WHERE cat = ".$cid." ORDER BY ".$sortierung);
		if ( db_num_rows($erg) > 0 ) {
		  $tpl->out(4); $class = 'Cnorm';
			while($row = db_fetch_assoc($erg) ) {
        # smal screenshot url
				$row['ssurl'] = ( (file_exists($row['ssurl']) AND $row['ssurl'] != '') ? '<img src="'.$row['ssurl'].'" alt="'.$row['name'].' '.$row['version'].'" title="'.$row['name'].' '.$row['version'].'" style="float:left; border: none; padding-right:3px;" />' : '' );
        $class = ( $class == 'Cmite' ? 'Cnorm' : 'Cmite' );
				$row['class'] = $class;
			  $tpl->set_ar_out($row,5);
			}
			$tpl->out(6);
		}
    
    if ( $cid == 0 AND $allgAr['archiv_down_userupload'] == 1 AND loggedin() AND is_writeable ( 'include/downs/downloads/user_upload' ) ) {
      $tpl->out(7);
    }
    
		$design->footer();
	  break;
	case 'show' :
	  
    $fid = escape($menu->get(2), 'integer');
    $erg = db_query("SELECT prefix_downloads.cat,ssurl,surl,url,hits,vote_klicks,vote_wertung,prefix_downloads.name,version,creater,downs,descl,prefix_downloads.id,DATE_FORMAT(time,'%d.%m.%Y') as datum FROM prefix_downloads LEFT JOIN prefix_downcats ON prefix_downcats.id = prefix_downloads.cat WHERE prefix_downloads.id = ".$fid." AND (".$_SESSION['authright']." <= prefix_downcats.recht OR (prefix_downloads.cat = 0 AND prefix_downcats.recht IS NULL))");    
    if (@db_num_rows($erg) <> 1) {
      $title = $allgAr['title'].' :: Downloads ';
      $hmenu = '<a class="smalfont" href="?downloads">Downloads</a>';
      $design = new design ( $title , $hmenu );
      $design->header();
      echo 'Der Download wurde nicht gefunden';
      $design->footer(1);
    }   
    
		$row = db_fetch_assoc($erg);
    
    # umfrage einen hoch zaehlen ...
    if ( $menu->getA(3) == 'z' AND is_numeric($menu->getE(3)) AND !isset ($_SESSION['downDoVote'][$row['id']]) AND loggedin() ) {
      $_SESSION['downDoVote'][$row['id']] = 'o';
      $row['vote_wertung'] = round ( ( ( $row['vote_wertung'] * $row['vote_klicks'] ) + $menu->getE(3) ) / ( $row['vote_klicks'] + 1 ) , 3 ); 
      $row['vote_klicks']++;
      db_query("UPDATE prefix_downloads SET vote_wertung = ".$row['vote_wertung'].", vote_klicks = ".$row['vote_klicks']." WHERE id = ".$row['id']);
    }
    if ( !isset ($_SESSION['downDoKlick'][$row['id']]) ) {
      $_SESSION['downDoKlick'][$row['id']] = 'o';
      db_query("UPDATE prefix_downloads SET hits = hits +1 WHERE id = ".$fid);
    }
    
		$cid = $row['cat'];
		$erg1 = db_query("SELECT id,cat,name FROM prefix_downcats WHERE id = ".$cid);
		if ( db_num_rows($erg1) > 0 ) {
		  $row1 = db_fetch_assoc($erg1);
			$array = get_cats_array($row1['cat'],'');
			if ( !empty($array) ) {
			  $titelzw = get_cats_title($array);
			  $namezw = get_cats_urls($array);
			} else {
			  $titelzw = '';
				$namezw = '';
			}
		  $cattitle = ':: '.$titelzw.$row1['name'].' :: '.$row['name'].' '.$row['version'];
			$catname = '<b> &raquo; </b>'.$namezw.'<a class="smalfont" href="?downloads-'.$row1['id'].'">'.$row1['name'].'</a><b> &raquo; </b>'.$row['name'].' '.$row['version'];	
		} else {
		  $cattitle = '';
			$catname = '';
		}
		$tpl = new tpl ('downloads_show' );
		$row['ssurl'] = ( $row['ssurl'] != '' ? '<img src="'.$row['ssurl'].'" alt="'.$row['name'].' '.$row['version'].'" title="'.$row['name'].' '.$row['version'].'" style="float:left; border: none; padding-right:5px;" />' : '' );
    $row['surl']  = ( empty($row['surl']) ? '' : '&nbsp;&nbsp;&nbsp; <a href="'.$row['surl'].'" target="_blank">Demo/Screenshot</a>' );
		$row['size']  = get_download_size($row['url']);
		$row['descl'] = bbcode($row['descl']);
    $title = $allgAr['title'].' :: Downloads '.$cattitle;
    $hmenu = '<a class="smalfont" href="?downloads">Downloads</a>'.$catname;
    $design = new design ( $title , $hmenu );
	  $design->header();
		$tpl->set_ar_out($row,0);
    $design->footer();
	  break;
	case 'down' :
    $fid = $menu->get(2);
	  db_query("UPDATE prefix_downloads SET downs = downs +1 WHERE id = ".$fid);
		$row = db_fetch_assoc(db_query("SELECT url FROM prefix_downloads WHERE id = ".$fid));
    header('location: '.iurlencode($row['url']));
	  break;
  case 'upload' :
    if ( $allgAr['archiv_down_userupload'] == 1 AND loggedin() AND is_writeable ( 'include/downs/downloads/user_upload' ) ) {
      $title = $allgAr['title'].' :: Downloads :: User - Upload';
      $hmenu = '<a class="smalfont" href="?downloads">Downloads</a><b> &raquo; </b>User - Upload';
      $design = new design ( $title , $hmenu );
	    $design->header();
      
	    $finsert = false;
	    $uinsert = true;
	
	    $name = escape($_POST['name'],'string');
	    $version = escape($_POST['version'],'string');
	    $autor = escape($_POST['autor'],'string');
	    $surl = escape($_POST['surl'],'string');
	    $ssurl = escape($_POST['ssurl'],'string');
      $url = ( empty($_POST['url']) ? '' : escape($_POST['url'],'string') );
	    $desc = escape($_POST['desc'],'string');
	    $descl = escape($_POST['descl'],'textarea');
	    if ( !empty($name) AND !empty($desc) AND !empty($descl) ) {
	      $finsert = true;
	    }
  
	    if ( !empty ($_FILES['file']['name']) ) {
        $uinsert = false;
		    $type = $_FILES['file']['type'];
		    $fname = escape($_FILES['file']['name'],'string');
		    $fname = str_replace(' ','',$fname);
        if (
		      $type == 'application/zip' OR
		    	$type == 'application/rar' OR
		    	$type == 'application/x-rar-compressed' OR
		    	$type == 'application/x-zip-compressed' OR
          $type == 'application/x-rar' OR
          $type == 'application/x-zip' OR
          $type == 'application/octet-stream'
		 
		    ) {
		      if ( $_FILES['file']['size'] <= 2097000 ) { # 2 mb (2 097 152)
			      if ( !file_exists( 'include/downs/downloads/user_upload/'.$fname ) ) {
			        if ( move_uploaded_file($_FILES['file']['tmp_name'], 'include/downs/downloads/user_upload/'.$fname) ) {
			          $uinsert = true;
			    		  $url = 'include/downs/downloads/user_upload/'.$fname;
			        }
			    	} else {
			    	  $exists = true;
			    	}
			    }
		    }
	    } else {
		    if ( empty($url) ) {
		      $finsert = false;
		    }
	    }
	
	    if ( $finsert AND $uinsert ) {
	      db_query("INSERT INTO prefix_downloads (`time`,`cat`,`creater`,`version`,`url`,surl,`ssurl`,`name`,`desc`,`descl`,pos) VALUES (NOW(),-1,'".$autor."','".$version."','".$url."','".$surl."','".$ssurl."','".$name."','".$desc."','".$descl."','0')");
		    echo 'Erfolgreich eingetragen! ... ein Moderator oder Admin dieser Seite wird den Download in n&auml;chster Zeit freischalten.';
	    } else {
	      ?>
		      Es ist ein Fehler aufgetreten!<br /><br />
		    	- Folgende Felder sind Pflicht:<br />
		    	- - Name<br />
		    	- - Kurz Besch.<br />
		    	- - Beschreibung<br />
		    	- - Link oder Upload Datei<br /><br />
			
			    - Wenn eine Datei hochgeladen wird:<br />
		    	- - Die Datei darf NICHT gr&ouml;sser als 2 MBytes sein.<br />
		    	- - Die Datei darf nur die Endungen: .zip oder .rar haben.<br /><br />
		    	<?php
			    if ( isset ($exists) ) {
			      echo ' - - <b>Grund f&uuml;r den Abbruch:</b> Die Datei existiert bereits und kann nicht &uuml;berschrieben werden.<br />';
			    }
			    ?>
			    <a href="javascript:history.back()">zur&uuml;ck</a>
		    <?php
      }
      $design->footer();
    }
    break;
}
?>