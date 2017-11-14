<?php
	require ('../lib/LoremImage/LoremImage.php');
	require ('../lib/LoremImage/Arkantas/RouterController.php');
	require ('../lib/LoremImage/Arkantas/Image.php');

	use \LoremImage\LoremImage as LoremImage;

	$lorem = new LoremImage('/loremimage/tests/');
	
	$lorem->setPathImages('/var/www/loremimage/img/');

	$lorem->setFolderPermission("/var/www/loremimage/img/profile", false);
	$lorem->setFolderPermission("/var/www/loremimage/img/logo", false);


	$lorem->render(); exit;
?>