<?php
/**
 * Created by Error202
 * Date: 18.08.2017
 */

namespace zertex\avatar_generator;

use Yii;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use yii\web\NotFoundHttpException;

/**
 * Class AvatarGenerator
 * @package zertex\avatar_generator
 */

class AvatarGenerator
{
	public $origin_image_path;
	public $cache_image_path;
	public $cache_url;

	public $size_width = 300;
	public $salt = 'my_random_salt';

	public $font = '@vendor/zertex/yii2-avatar-generator/src/Play-Bold.ttf';
	public $font_size = 100;


	/**
	 * @param string $username
	 * @param int|null $size
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
	private function image(string $username, int $size = null): string
	{
		$origin_name = $this->getFileName($username);
		$cache_name = $this->getFileName($username, $size);

		$origin = Yii::getAlias($this->origin_image_path) . '/' . $origin_name;
		$width = $size ?: $this->size_width;

		$cache = Yii::getAlias($this->cache_image_path) . '/' . $cache_name;
		$cacheUrl = $this->cache_url  . '/' . $cache_name;

		if (!file_exists($origin))
		{
			throw new NotFoundHttpException('Image "' . $origin_name . '" does not exists.');
		}

		if (!file_exists($cache))
		{
			FileHelper::createDirectory(Yii::getAlias($this->cache_image_path), 0755, true);
			Image::thumbnail($origin, $width, $width)->save($cache);
		}
		return $cacheUrl;
	}

	/**
	 * @param string $username
	 * @param int|null $size
	 * @param string|null $file
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
	public function show(string $username, int $size = null, string $file = null): string
	{
		if ($file != null) {
			$this->generateAvatarFromFile($username, $file);
		}
		else {
			$image_file = $this->getFileName($username);
			if (!file_exists(Yii::getAlias($this->origin_image_path) . '/' . $image_file)) {
				$this->generateAvatarByName($username);
			}
		}
		return $this->image($username, $size ?: $this->size_width);
	}

	/**
	 * @param string $username
	 * @param string $file
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	private function generateAvatarFromFile(string $username, string $file): string
	{
		$image_file = $this->getFileName($username);
		$origin = Yii::getAlias($this->origin_image_path) . '/' . $image_file;
		FileHelper::createDirectory(Yii::getAlias($this->origin_image_path), 0755, true);
		copy($file, $origin);
		return $image_file;
	}

	/**
	 * @param string $username
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	private function generateAvatarByName(string $username): string
	{
		$fontSize = 100;
		$font = Yii::getAlias($this->font);

		$parts = explode(' ', $username);
		$text =  is_array($parts) && count($parts)>1 ? mb_substr($parts[0],0,1,"UTF-8") . mb_substr($parts[1],0,1,"UTF-8") : mb_substr($username,0,2,"UTF-8");

		$image_file = 'avatar_' . md5($username . $this->salt) . '.png';

		$origin = Yii::getAlias($this->origin_image_path) . '/' . $image_file;
		FileHelper::createDirectory(Yii::getAlias($this->origin_image_path), 0755, true);

		$img = imagecreatetruecolor($this->size_width, $this->size_width);

		$bgcolor = substr(md5($username), 0, 6);

		$rgb = [];
		list($rgb['r'], $rgb['g'], $rgb['b']) = sscanf($bgcolor, "%02x%02x%02x");
		$rgb['rgb'] = $rgb['b'] + ($rgb['g'] << 0x8) + ($rgb['r'] << 0x10);

		$contrast = $this->RgbContrast($rgb['r'], $rgb['g'], $rgb['b']);

		$fillColor = imagecolorallocate($img, $rgb['r'], $rgb['g'], $rgb['b']);
		imagefill($img, 0,0, $fillColor);

		$cor = imagecolorallocate($img, $contrast['r'], $contrast['g'], $contrast['b']);

		$box = imageftbbox( $fontSize, 0, $font, $text );
		$x = ($this->size_width - ($box[2] - $box[0])) / 2;
		$y = ($this->size_width - ($box[1] - $box[7])) / 2;
		$y -= $box[7];

		imagettftext($img, $fontSize, 0, $x, $y, $cor, $font, $text);
		imagepng($img, $origin);
		imagedestroy($img);
		return $image_file;
	}

	/**
	 * @param string $username
	 */
	public function remove(string $username): void
	{
		$image_file = $this->getFileName($username);
		$image_id = $this->getFileID($username);

		if (file_exists(Yii::getAlias($this->origin_image_path) . '/' . $image_file)) {
			unlink(Yii::getAlias($this->origin_image_path) . '/' . $image_file);
		}

		// remove cache files
		array_map('unlink', glob(Yii::getAlias($this->cache_image_path) . '/avatar_' . $image_id . '_*.*'));
	}

	/**
	 * @param $r
	 * @param $g
	 * @param $b
	 *
	 * @return array
	 */
	private function RgbContrast($r, $g, $b)
	{
		return array(
			'r' => ($r < 128) ? 255 : 0,
			'g' => ($g < 128) ? 255 : 0,
			'b' => ($b < 128) ? 255 : 0
		);
	}

	private function getFileName(string $username, $size = null)
	{
		return $size ? 'avatar_' . $this->getFileID($username) . '_' . $size . '.png' : 'avatar_' . $this->getFileID($username) . '.png';
	}

	private function getFileID(string $username)
	{
		return md5($username . $this->salt);
	}
}