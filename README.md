# Avatar Generator

Generate avatar for user by his name or file for Yii2.

[![Latest Stable Version](https://poser.pugx.org/zertex/yii2-avatar-generator/v/stable.png)](https://packagist.org/packages/zertex/yii2-avatar-generator)
[![Total Downloads](https://poser.pugx.org/zertex/yii2-avatar-generator/downloads.png)](https://packagist.org/packages/zertex/yii2-avatar-generator)

## Features
- Generate image by username
- Generate image by photo
- Auto select colors by username
- Cached images for different resolution
- Font face and size customize 
- Texture for background (2 available now)

## Installation

Install with composer:

```bash
composer require zertex/yii2-avatar-generator
```

or add

```bash
"zertex/yii2-avatar-generator": "*"
```

to the require section of your `composer.json` file.

## Configuration

Add to `common/config/main.php`
or `config/web.php`

```php
'components' => [
    ...
    'avatar' => [
        'class' => 'zertex\avatar_generator\AvatarGenerator',
        'origin_image_path' => 'path_to_image_files',
        'cache_image_path' => 'path_to_image_files',
        'cache_url' => 'url_to_image_files',
        'size_width' => 300,            // default: 300
        'font' => 'path_to_ttf_font',   // default: Play-Bold // may use aliases
        'font_size' => 100,             // default: 100
        'salt' => 'random_salt',        // salt for image file names
        'texture' => 'sun',             // texture name
    ],
],
```

* origin_image_path - Folder for origin image with `size_width` sides width
* cache_image_path - Folder for cache images with custom sides width
* cache_url - Url to cache folder for images
* size_width - `[optional]` Origin image side width. Default: 300
* font - `[optional]` Path to TTF font file. Yii2 aliases ready. Default: Play-Bold.ttf
* font_size - `[optional]` Font size. Default: 100
* salt - Random garbage for images file name
* texture - Texture name: sun, rain. Default: empty

## Using

Simple use with default image resolution 
```html
<?= Yii::$app->avatar->show('John Smith') ?>
```

Image with 150 px sides
```html
<?= Yii::$app->avatar->show('John Smith', 150) ?>
```

Image for existing file with default image resolution
```html
<?= Yii::$app->avatar->show('John Smith', null, '/path/JM_Avatar.jpg') ?>
```

Image for existing file with 150 px sides
```html
<?= Yii::$app->avatar->show('John Smith', 150, '/path/JM_Avatar.jpg') ?>
```

![alt text](http://zertex.ru/ext-banner2.png)