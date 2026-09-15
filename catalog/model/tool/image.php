<?php
class ModelToolImage extends Model {
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			// Локальна розробка: image/catalog живе тільки на проді, віддаємо прод-URL (на проді константа не визначена)
			if (defined('DEV_IMAGE_FALLBACK') && $filename) {
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;
				return DEV_IMAGE_FALLBACK . 'image/' . str_replace(' ', '%20', $image_new);
			}

			// Товар може посилатись на видалений файл — віддаємо плейсхолдер, щоб не
			// лишати на сторінці порожні <img src="">.
			if ($filename != 'placeholder.png' && is_file(DIR_IMAGE . 'placeholder.png')) {
				return $this->resize('placeholder.png', $width, $height);
			}

			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP))) { 
				if ($this->request->server['HTTPS']) {
					return $this->config->get('config_ssl') . 'image/' . $image_old;
 				} else {
					return $this->config->get('config_url') . 'image/' . $image_old;
				}
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}

	/**
	 * Мініатюра «по товару»: обрізає порожні (білі) поля навколо предмета і вписує
	 * його у квадрат $width×$height з невеликим відступом. Фото каталогу зазвичай
	 * мають багато білого довкола пляшки, і звичайний resize() лишав товар маленьким
	 * посеред картки. Кеш: image/cache/<файл>-WxHfit.<ext>.
	 */
	public function fit($filename, $width, $height, $padding = 0.06) {
		if (!$filename || !is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return $this->resize($filename, $width, $height);
		}

		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . 'fit.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			$info = @getimagesize(DIR_IMAGE . $image_old);

			if (!$info || !in_array($info[2], array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP))) {
				return $this->resize($filename, $width, $height);
			}

			$src = @imagecreatefromstring(file_get_contents(DIR_IMAGE . $image_old));

			if (!$src) {
				return $this->resize($filename, $width, $height);
			}

			$wo = imagesx($src);
			$ho = imagesy($src);

			// Межі предмета: пікселі, помітно відмінні від фону (колір кутка) і не білі.
			// Проходимо з кроком, щоб великі фото не коштували секунд.
			$step = max(1, (int)floor(max($wo, $ho) / 500));
			$bg = imagecolorsforindex($src, imagecolorat($src, 0, 0));
			$minx = $wo; $miny = $ho; $maxx = -1; $maxy = -1;

			for ($y = 0; $y < $ho; $y += $step) {
				for ($x = 0; $x < $wo; $x += $step) {
					$c = imagecolorsforindex($src, imagecolorat($src, $x, $y));

					if (isset($c['alpha']) && $c['alpha'] > 100) {
						continue;
					}

					$diff = abs($c['red'] - $bg['red']) + abs($c['green'] - $bg['green']) + abs($c['blue'] - $bg['blue']);
					$white = $c['red'] > 238 && $c['green'] > 238 && $c['blue'] > 238;

					if ($diff > 40 && !$white) {
						if ($x < $minx) $minx = $x;
						if ($x > $maxx) $maxx = $x;
						if ($y < $miny) $miny = $y;
						if ($y > $maxy) $maxy = $y;
					}
				}
			}

			$bw = $maxx - $minx + 1;
			$bh = $maxy - $miny + 1;

			// нічого не знайшли або предмет і так займає майже все — беремо повний кадр
			if ($maxx < 0 || $bw * $bh < $wo * $ho * 0.02 || ($bw >= $wo * 0.92 && $bh >= $ho * 0.92)) {
				$minx = 0; $miny = 0; $bw = $wo; $bh = $ho;
			}

			$side = (int)ceil(max($bw, $bh) * (1 + 2 * $padding));
			$cx = $minx + $bw / 2;
			$cy = $miny + $bh / 2;
			$sx = (int)round($cx - $side / 2);
			$sy = (int)round($cy - $side / 2);

			// квадратний кадр на білому: частина, що виходить за фото, лишається білою
			$canvas = imagecreatetruecolor($side, $side);
			$white = imagecolorallocate($canvas, 255, 255, 255);
			imagefill($canvas, 0, 0, $white);

			$cx0 = max(0, $sx); $cy0 = max(0, $sy);
			$cx1 = min($wo, $sx + $side); $cy1 = min($ho, $sy + $side);

			if ($cx1 > $cx0 && $cy1 > $cy0) {
				imagecopy($canvas, $src, $cx0 - $sx, $cy0 - $sy, $cx0, $cy0, $cx1 - $cx0, $cy1 - $cy0);
			}

			$dst = imagecreatetruecolor((int)$width, (int)$height);
			imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
			imagecopyresampled($dst, $canvas, 0, 0, 0, 0, (int)$width, (int)$height, $side, $side);

			$path = '';

			foreach (explode('/', dirname($image_new)) as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			switch ($extension) {
				case 'png':  imagepng($dst, DIR_IMAGE . $image_new, 6); break;
				case 'gif':  imagegif($dst, DIR_IMAGE . $image_new); break;
				case 'webp': imagewebp($dst, DIR_IMAGE . $image_new, 88); break;
				default:     imagejpeg($dst, DIR_IMAGE . $image_new, 90);
			}

			imagedestroy($src); imagedestroy($canvas); imagedestroy($dst);
		}

		$image_new = str_replace(' ', '%20', $image_new);

		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
}
