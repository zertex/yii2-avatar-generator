<?php
/**
 * Created by Error202
 * Date: 18.08.2017
 */

namespace zertex\avatar_generator;

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
	public $font_size = 100;

	public $texture = ['sun', 'rain'];
	public $texture_folder = '@vendor/zertex/yii2-avatar-generator/src/images';
	public $text_over_image = true;
	public $texture_over_image = true;

	public function show(string $username, int $width = null, string $file = null): string
	{
		$options = new AvatarOptions();
		$options->font = Yii::getAlias($this->font);
		$options->textures_folder = Yii::getAlias($this->texture_folder);
		$options->images_folder = Yii::getAlias($this->images_folder);
		$options->width = $width;
		$options->images_url = $this->images_url;

		if ($file == null) {
			$avatar = Avatar::init($username, $options)->username();
		}
		else {
			$avatar = Avatar::init($username, $options)->file($file);
		}

		if ($this->texture_over_image || $file == null) {
			$avatar->texture($this->texture);
		}

		if ($this->text_over_image || $file == null) {
			$avatar->text();
		}

		return $avatar->get_file_name();
	}
}
