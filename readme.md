LoremImage - Random image system for PHP
========================

[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/coresphereweb/loremimage/blob/master/license.md)


It requires PHP 5
and is licensed under the New BSD License. You can obtain the latest version from
our [GitHub repository](https://github.com/coresphereweb/loremimage/releases) or install it via Composer:

```
composer require coresphereweb/loremimage
```

Usage
-----

Render:

```php
$lorem = new \LoremImage\LoremImage();
$lorem->setPathUrl('/loremimage/');
$lorem->setPathImages(PATH . 'img/loremimage/');

$lorem->render();
```

Routes (optional)
-----
1. width and height: /loremimage/600/400
2. category: /loremimage/cars
3. width, height and category: /loremimage/600/400/cars
4. width, height, category and select picture by ASC: /loremimage/600/400/cars/1

* IMPORTANT: .htaccess is necessary to routes work

Conditions by _GET
-----
- (int) width
- (int) height
- (string) category (folder in setPathImages())
- (int) picture
- (string) effect
- (int) qtd


Effects 
-----
- pixelate
- smooth
- noise
- negative
- emboss
- edge
- contrast
- colorize
- grayscale
- brightness
- blur2
- blur
- sharpen

Some effects respond to _GET['qtd'], with recursive does the same procedure by a number inputed.


-----
(c) Coresphere Tecnologia, 2016 (http://coresphe.re)
