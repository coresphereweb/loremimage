<?php
	require ('../src/LoremImage.php');
	require ('../src/Arkantas/RouterController.php');
	require ('../src/Arkantas/Image.php');

	$lorem = new \LoremImage\LoremImage('/loremimage/tests/');
	$lorem->setPathImages('C:\Apache24\htdocs\coresphere/img/loremimage/');
	//$lorem->setPathImages('/var/www/loremimage/img/');

	$lorem->render(); exit;
?>