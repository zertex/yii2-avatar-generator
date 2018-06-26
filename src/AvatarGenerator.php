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

    public $font = '@vendor/zertex/avatar_generator/src/Play-Bold.ttf';
    public $font_size = 100;

    public function __construct() {
    	$this->origin_image_path = Yii::getAlias($this->origin_image_path);
	    $this->cache_image_path = Yii::getAlias($this->cache_image_path);
	    $this->font = Yii::getAlias($this->font);
    }

	/**
	 * @param string $name
	 * @param int|null $size
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    private function image(string $name, int $size = null): string
    {
        $origin = $this->origin_image_path . '/' . $name;
        $width = $size ?: $this->size_width;

        if (!file_exists($origin))
        {
            return '';
        }

        $cache = $this->cache_image_path . '/' . $name;
        $cacheUrl = $this->cache_url  . '/' . $name;

        if (!file_exists($origin))
        {
            throw new NotFoundHttpException('Image "' . $name . '" does not exists.');
        }

        if (!file_exists($cache))
        {
            FileHelper::createDirectory($this->cache_image_path, 0755, true);
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
    		$image_file = $this->generateAvatarFromFile($username, $file);
	    }
	    else {
		    $image_file = $this->getFileName($username);
		    if (!file_exists($this->origin_image_path . '/' . $image_file)) {
			    $image_file = $this->generateAvatarByName($username);
		    }
	    }
        return $this->image($image_file, $size ?: $this->size_width);
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
        $origin = $this->origin_image_path . '/' . $image_file;
        FileHelper::createDirectory($this->origin_image_path, 0755, true);
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
        $text =  is_array($parts) && count($parts)>1 ? mb_substr($parts[0],0,1,"UTF-8") . mb_substr($parts[1],0,1,"UTF-8") : mb_substr($username,0,1,"UTF-8");

	    $image_file = 'avatar_' . md5($username . $this->salt) . '.png';

        $origin = $this->origin_image_path . '/' . $image_file;
        FileHelper::createDirectory($this->origin_image_path, 0755, true);

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

        if (file_exists($this->origin_image_path . '/' . $image_file)) {
            unlink($this->origin_image_path . '/' . $image_file);
        }

        // remove cache files
	    array_map('unlink', glob($this->cache_image_path . '/avatar_' . $image_id . '_*.*'));
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

    private function getFileName(string $username)
    {
    	return 'avatar_' . $this->getFileID($username) . '.png';
    }

	private function getFileID(string $username)
	{
		return md5($username . $this->salt);
	}
}