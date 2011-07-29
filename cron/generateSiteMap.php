<?php

    //Initialized the exponent
    require_once("bootstrap.php");    
    
	//Get the filename to be use
    $filename = EXP_PATH . 'sitemap.xml';    
   
    //Header of the xml file
    $content="<?xml version='1.0' encoding='UTF-8'?>".chr(13).chr(10);
    $content.="<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>".chr(13).chr(10);
    
	 //Check if the file exist
	if (!$handle = fopen($filename, 'w')) {
		echo "Cannot open file ($filename)";
		exit;
	}
	
	//Check if the file is writable
	if (fwrite($handle, $content) == FALSE) {
		$action_msg = "ER";
	}
    
    $count=0;
    $columns = '';
    
	//Get all the sections
    $sections = $db->selectObjectsBySql('SELECT sef_name FROM exponent_section WHERE public = 1 and active = 1');
	
	foreach ($sections as $item) {            
		
		$columns = '<url>'.chr(13).chr(10);
	
		$columns.='<loc>';
		$columns.="http://www.militaryuniformsupply.com/".$item->sef_name;
		$columns.='</loc>'.chr(13).chr(10);

		$columns.='<lastmod>';
		$columns.= date('Y-m-d');
		$columns.='</lastmod>'.chr(13).chr(10);
		
		$columns.='<changefreq>';
		$columns.= "weekly";
		$columns.='</changefreq>'.chr(13).chr(10);

		$columns.='<priority>';
		$columns.= "0.5";
		$columns.='</priority>'.chr(13).chr(10);
		
		$columns.='</url>'.chr(13).chr(10);
		$count++;

		// Write the section content to our opened file.
		if (fwrite($handle, $columns.chr(13).chr(10)) == FALSE) {
			$action_msg = "ER";
		}
	}

	//Get all the active categories
	$categories = $db->selectObjectsBySql('SELECT sef_url FROM exponent_storeCategories WHERE is_active = 1');
	foreach ($categories as $item) {            
		
		$columns = '<url>'.chr(13).chr(10);
	
		$columns.='<loc>';
		$columns.="http://www.militaryuniformsupply.com/".$item->sef_url;
		$columns.='</loc>'.chr(13).chr(10);

		$columns.='<lastmod>';
		$columns.= date('Y-m-d');
		$columns.='</lastmod>'.chr(13).chr(10);
		
		$columns.='<changefreq>';
		$columns.= "weekly";
		$columns.='</changefreq>'.chr(13).chr(10);

		$columns.='<priority>';
		$columns.= "0.5";
		$columns.='</priority>'.chr(13).chr(10);
		
		$columns.='</url>'.chr(13).chr(10);
		$count++;
       
		// Write the active categories to our opened file.
		if (fwrite($handle, $columns.chr(13).chr(10)) == FALSE) {
			$action_msg = "ER";
		}
	}
	
	//Get all the active products
	$products = $db->selectObjectsBySql("SELECT sef_url FROM exponent_product WHERE (active_type = 0 or active_type = 1) and parent_id = 0");
	foreach ($products as $item) {            
		
		$columns = '<url>'.chr(13).chr(10);
	
		$columns.='<loc>';
		$columns.="http://www.militaryuniformsupply.com/".$item->sef_url;
		$columns.='</loc>'.chr(13).chr(10);

		$columns.='<lastmod>';
		$columns.= date('Y-m-d');
		$columns.='</lastmod>'.chr(13).chr(10);
		
		$columns.='<changefreq>';
		$columns.= "weekly";
		$columns.='</changefreq>'.chr(13).chr(10);

		$columns.='<priority>';
		$columns.= "0.5";
		$columns.='</priority>'.chr(13).chr(10);
		
		$columns.='</url>'.chr(13).chr(10);
		$count++;
	           
		// Write all the active products to our opened file.
		if (fwrite($handle, $columns.chr(13).chr(10)) == FALSE) {
			$action_msg = "ER";
		}
	}
	
    $content='</urlset>'.chr(13).chr(10);

    // Write the footer to our opened file.
    if (fwrite($handle, $content) == FALSE) {
        $action_msg = "ER";
    }
    $action_msg = "SC";
    fclose($handle);        
    echo "\r\nGenerated $count products in the feed.\r\n";       
	
?>