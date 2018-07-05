<?php
/**
 * Created by Error202
 * Date: 18.08.2017
 */

namespace zertex\avatar_generator;

use zertex\avatar\Avatar;
use zertex\avatar\AvatarOptions;
use Yii;

/**
 * Class AvatarGenerator
 * @package zertex\avatar_generator
 */

class AvatarGenerator
{
	public $images_folder;
	public $images_url;

	public $size_width = 300;
	public $salt = 'my_random_salt';

	public $font = '@vendor/zertex/yii2-avatar-generator/src/Play-Bold.ttf';
	public $font_size = 200;

	public $texture = ['sun', 'rain'];
	public $texture_folder= '@vendor/zertex/yii2-avatar-generator/src/images';
	public $text_over_image = true;
	public $texture_over_image = true;

	public function show(string $username, int $width = null, string $file = null, string $result_name = null): string
	{
		$options = AvatarOptions::create()
		                        ->setFont(Yii::getAlias($this->font))
		                        ->setWidth($width ?: $this->size_width)
		                        ->setFontSize($this->font_size)
		                        ->setTexturesFolder(Yii::getAlias($this->texture_folder))
		                        ->setImagesFolder(Yii::getAlias($this->images_folder))
		                        ->setImagesUrl(Yii::getAlias($this->images_url))
		                        ->setSalt($this->salt);

		return Avatar::init($username, $options, $result_name)
		             ->{($file==null) ? 'username' : 'file'}($file)
		             ->{($this->texture_over_image || $file == null) ? 'texture' : '_blank'}($this->texture)
		             ->{($this->text_over_image || $file == null) ? 'text' : '_blank'}()
		             ->get_file_name();
	}

	public function update(string $username, int $width = null, string $file = null, string $result_name = null): string
	{
		$options = AvatarOptions::create()
		                        ->setFont(Yii::getAlias($this->font))
		                        ->setWidth($width ?: $this->size_width)
		                        ->setFontSize($this->font_size)
		                        ->setTexturesFolder(Yii::getAlias($this->texture_folder))
		                        ->setImagesFolder(Yii::getAlias($this->images_folder))
		                        ->setImagesUrl(Yii::getAlias($this->images_url))
		                        ->setSalt($this->salt)
								->setUpdate($this->update());

		return Avatar::init($username, $options, $result_name)
		             ->{($file==null) ? 'username' : 'file'}($file)
		             ->{($this->texture_over_image || $file == null) ? 'texture' : '_blank'}($this->texture)
		             ->{($this->text_over_image || $file == null) ? 'text' : '_blank'}()
		             ->get_file_name();
	}
}
