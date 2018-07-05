# Avatar Generator

Generate avatar for user by his name, file or url for Yii2.

[![Latest Stable Version](https://poser.pugx.org/zertex/yii2-avatar-generator/v/stable.png)](https://packagist.org/packages/zertex/yii2-avatar-generator)
[![Total Downloads](https://poser.pugx.org/zertex/yii2-avatar-generator/downloads.png)](https://packagist.org/packages/zertex/yii2-avatar-generator)

## Features
- Generate avatar by username
- Generate avatar from file or url (http only)
- Auto select background color by username
- Contrast color for text
- Font face and size customize 
- Texture for background (2 available now)

## Dependencies

* PHP 7
* PHP GD
* zertex/avatar-generator

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
        'class' => \zertex\avatar_generator\AvatarGenerator::class,
        'images_folder' => 'path_to_image_files',
        'images_url' => 'url_to_image_files',
        'size_width' => 300,            // default: 300
        'font' => 'path_to_ttf_font',   // default: Play-Bold // may use aliases
        'font_size' => 200,             // default: 200
        'salt' => 'random_salt',        // salt for image file names
        'texture' => ['sun', 'rain'],   // texture name
        'text_over_image' => true,      // draw text over image (for avatar from file)
        'texture_over_image' => true,   // draw texture over image (for avatar from file)
    ],
],
```

* images_folder - `required` Folder for images
* images_url - `required` Url to folder with images
* size_width - Origin image side width. Default: 300
* font - Path to TTF font file. Yii2 aliases ready. Default: Play-Bold.ttf
* font_size - Font size. Default: 300
* salt - Random garbage for images file name
* texture - Texture name: sun, rain. Default: empty
* text_over_image - Draw text over image. For avatar created from file. Default: true
* texture_over_image - Draw texture over image. For avatar created from file. Default: true

## Using

```
Yii::$app->avatar->show('username', [width], [file or url], [new_file_name]);
```

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
or
```html
<?= Yii::$app->avatar->show('John Smith', null, 'http://site.org/JM_Avatar.jpg') ?>
```

Image for existing file with 150 px sides
```html
<?= Yii::$app->avatar->show('John Smith', 150, '/path/JM_Avatar.jpg') ?>
```

#### Using without Yii2 wrap

You can use avatar generator without Yii2 wrap.
Just install 
https://github.com/zertex/avatar-generator

## Screenshot 

![alt text](http://zertex.ru/ext-banner3.png)

## Examples

https://zertex.ru/yii2-avatar-generator