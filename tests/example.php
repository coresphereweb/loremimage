<?php
	require ('../lib/LoremImage/LoremImage.php');
	require ('../lib/LoremImage/Arkantas/RouterController.php');
	require ('../lib/LoremImage/Arkantas/Image.php');

	$lorem = new \LoremImage\LoremImage('/loremimage/tests/');
	$lorem->setPathImages('C:\Apache24\htdocs\coresphere/img/loremimage/');
	//$lorem->setPathImages('/var/www/loremimage/img/');

	$lorem->render(); exit;
?>