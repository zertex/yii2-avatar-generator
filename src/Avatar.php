<?php
/**
 * Created by Error202
 * Date: 27.06.2018
 */

namespace zertex\avatar_generator;

class Avatar
{
	private $username;
	private $img; // GD image data
	/* @var $options AvatarOptions */
	private $options;

	private $color_allocate;
	private $rgb = [];

	private $transit = false;

	private function __construct() {
	}

	// save / filename

	public function get_file_name(): string
	{
		$image_file = $this->getFileName($this->username, $this->options->width);

		if ($this->transit) {
			return $this->options->images_url . '/' . $image_file;
		}

		$origin = $this->options->images_folder . '/' . $image_file;
		if (!file_exists($this->options->images_folder)) {
			mkdir($this->options->images_folder, 0777, true);
		}
		imagepng($this->thumb($this->img, $this->options->width, $this->options->width), $origin);
		imagedestroy($this->img);
		return $this->options->images_url . '/' . $image_file;
	}

	// text

	public function text(): self
	{
		if ($this->transit) {
			return $this;
		}
		$parts = explode(' ', $this->username);
		$text = is_array($parts) && count($parts)>1 ? mb_substr($parts[0],0,1,"UTF-8") . mb_substr($parts[1],0,1,"UTF-8") : mb_substr($this->username,0,2,"UTF-8");
		$box = imageftbbox( $this->options->font_size, 0, $this->options->font, $text);
		$x = (500 - ($box[2] - $box[0])) / 2;
		$y = (500 - ($box[1] - $box[7])) / 2;
		$y -= $box[7];
		imagettftext($this->img, $this->options->font_size, 0, $x, $y, $this->color_allocate, $this->options->font, $text);
		return $this;
	}

	// texture

	public function texture($texture): self
	{
		if ($this->transit) {
			return $this;
		}

		if (is_array($texture)) {
			$texture = $texture[rand(0, count($texture)-1)];
		}

		// preparing texture
		$color   = $this->checkLightness( $this->rgb['r'], $this->rgb['g'], $this->rgb['b'] ) ? 'black' : 'white';
		$texture = imagecreatefrompng( $this->options->textures_folder . '/' . $texture . '-' . $color . '.png' );
		$virtual_image = $this->thumb($texture, 500, 500);
		imagecopy( $this->img, $virtual_image, 0, 0, 0, 0, 500, 500 );
		return $this;
	}

	// prepare GD data

	public function username(): self
	{
		if ($this->transit) {
			return $this;
		}
		$this->img = imagecreatetruecolor(500, 500);
		$background_color = substr(md5($this->username), 0, 6);
		list($this->rgb['r'], $this->rgb['g'], $this->rgb['b']) = sscanf($background_color, "%02x%02x%02x");
		$this->rgb['rgb'] = $this->rgb['b'] + ($this->rgb['g'] << 0x8) + ($this->rgb['r'] << 0x10);
		$contrast = $this->RgbContrast($this->rgb['r'], $this->rgb['g'], $this->rgb['b']);
		$fillColor = imagecolorallocate($this->img, $this->rgb['r'], $this->rgb['g'], $this->rgb['b']);
		imagefill($this->img, 0,0, $fillColor);
		$this->color_allocate = imagecolorallocate($this->img, $contrast['r'], $contrast['g'], $contrast['b']);
		return $this;
	}

	public function file(string $file): self
	{
		if ($this->transit) {
			return $this;
		}

		$image_file = $this->getFileName($this->username, $this->options->width);
		$origin = $this->options->images_folder . '/' . $image_file;
		if (!file_exists($this->options->images_folder)) {
			mkdir($this->options->images_folder, 0777, true);
		}
		copy($file, $origin);

		if ($origin && file_exists($origin) && $type = $this->getImageType($origin)) {
			switch ($type) {
				case 'image/jpeg':
					$this->img = $this->thumb(imagecreatefromjpeg($origin), 500, 500);
					break;
				case 'image/png':
					$this->img = $this->thumb(imagecreatefrompng($origin), 500, 500);
					break;
				case 'image/gif':
					$this->img = $this->thumb(imagecreatefromgif($origin), 500, 500);
					break;
				default:
					throw new \DomainException("Unknown file type");
					break;
			}
			// Detect main background color
			$image_donor =  imagecrop($this->img, array('x'=>0,'y'=>0,'width'=>imagesx($this->img),'height'=>imagesy($this->img)));
			$background_color = $this->getMainColor($image_donor);
			list($this->rgb['r'], $this->rgb['g'], $this->rgb['b']) = sscanf($background_color, "%02x%02x%02x");
			$this->rgb['rgb'] = $this->rgb['b'] + ($this->rgb['g'] << 0x8) + ($this->rgb['r'] << 0x10);
			$contrast = $this->RgbContrast($this->rgb['r'], $this->rgb['g'], $this->rgb['b']);
			$this->color_allocate = imagecolorallocate($this->img, $contrast['r'], $contrast['g'], $contrast['b']);
		}
		else {
			if (file_exists($origin)) {
				unlink( $origin );
			}
			throw new \DomainException("File must be JPG, GIF or PNG");
		}
		return $this;
	}

	// init

	public static function init(string $username, AvatarOptions $options = null)
	{
		$avatar = new static();
		$avatar->username = $username;
		$avatar->options = isset($options) ? $options : new AvatarOptions();

		$image_file = $avatar->getFileName($username, $avatar->options->width);
		$origin = $avatar->options->images_folder . '/' . $image_file;
		if (file_exists($origin)&& !$avatar->options->update) {
			$avatar->transit = true;
		}
		return $avatar;
	}

	// some functions

	private function getImageType ( $filename ) {
		$img = getimagesize( $filename );
		if ( !empty( $img[2] ) )
			return image_type_to_mime_type( $img[2] );
		return false;
	}

	private function RgbContrast($r, $g, $b)
	{
		return array(
			'r' => ($r < 128) ? 255 : 0,
			'g' => ($g < 128) ? 255 : 0,
			'b' => ($b < 128) ? 255 : 0
		);
	}

	private function checkLightness($r, $g, $b)
	{
		if (1 - (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255 < 0.5)
		{ // light
			return true;
		}
		// dark
		return false;
	}

	private function getFileName(string $username, $size = null)
	{
		return $size ? 'avatar_' . $this->getFileID($username) . '_' . $size . '.png' : 'avatar_' . $this->getFileID($username) . '.png';
	}

	private function getFileID(string $username)
	{
		return md5($username . $this->options->salt);
	}

	private function thumb($img, $width, $height)
	{
		$origin_width = imagesx($img);
		$origin_height = imagesy( $img );

		$origin_aspect = $origin_width / $origin_height;
		$thumb_aspect = $width / $height;

		if ( $origin_aspect >= $thumb_aspect ) {
			$new_height = $height;
			$new_width = $origin_width / ($origin_height / $height);
		}
		else {
			$new_width = $width;
			$new_height = $origin_height / ($origin_width / $width);
		}

		$virtual = imagecreatetruecolor($width, $height);
		imagealphablending($virtual,false);
		$col = imagecolorallocatealpha($virtual,255,255,255,127);
		imagefilledrectangle($virtual,0,0,$width, $height, $col);
		imagealphablending($virtual,true);

		imagealphablending( $img, false );
		imagesavealpha( $img, true );
		imagecopyresampled($virtual,
			$img,
			0 - ($new_width - $width) / 2,
			0 - ($new_height - $height) / 2,
			0, 0,
			$new_width, $new_height,
			$origin_width, $origin_height);
		return $virtual;
	}

	private function getMainColor($img, $palletSize=[16,8]){ // GET PALLET FROM IMAGE PLAY WITH INPUT PALLET SIZE
		$resizedImg=imagecreatetruecolor($palletSize[0],$palletSize[1]);
		imagecopyresized($resizedImg, $img , 0, 0 , 0, 0, $palletSize[0], $palletSize[1], 500, 500);
		imagedestroy($img);

		$colors=[];

		for($i=0;$i<$palletSize[1];$i++)
			for($j=0;$j<$palletSize[0];$j++)
				$colors[]=dechex(imagecolorat($resizedImg,$j,$i));

		imagedestroy($resizedImg);
		$colors= array_unique($colors);
		return $colors[0];
	}
}
