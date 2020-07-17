<?php
declare(strict_types=1);
namespace ntt;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use function array_diff;
use function base64_encode;
use function chr;
use function file_exists;
use function file_put_contents;
use function getimagesize;
use function imagecolorat;
use function in_array;
use function mkdir;
use function pathinfo;
use function scandir;
use function str_replace;
use function strtolower;
use function unlink;
use const PATHINFO_EXTENSION;

class Main extends PluginBase implements Listener{
	/** @var string */
	public $path;
	/** @var string */
	public $filePath;
	/** @var string[] */
	public $list;
	/** @var string[] */
	public $imageType = ['jpeg', 'png', 'xpm', 'xbm', 'bmp', 'wbmp', 'webp'];

	public function onEnable() : void{
		$this->path = $this->getServer()->getDataPath();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->filePhar = $this->path . "ItB64E/";
		if(!file_exists($this->filePhar)){
			@mkdir($this->filePhar, 0775, true);
			$this->getLogger()->info("§aFolder §fItB64E §aGenerated!");
		}
		if(!file_exists($this->filePhar . "Image-Input/")){
			@mkdir($this->filePhar . "Image-Input/", 0775, true);
			$this->getLogger()->info("§aFolder §fImage-Input §aGenerated!");
		}
		if(!file_exists($this->filePhar . "Data-Output/")){
			@mkdir($this->filePhar . "Data-Output/", 0775, true);
			$this->getLogger()->info("§aFolder §fData-Output §aGenerated!");
		}
		$this->getLogger()->info("§aPlugin enabled!");
		$this->list = array_diff(scandir($this->filePhar . "Image-Input/"), ['.', '..']);
		foreach($this->list as $f){
			$ff = strtolower(pathinfo($this->filePhar . "Image-Input/" . $f, PATHINFO_EXTENSION));
			$name = str_replace("." . $ff, "", $f);
			$ImagePhar = $this->filePhar . "Image-Input/" . $f;
			if(in_array($ff, $this->imageType, true)){
				$data = $this->getDataFromImage($ImagePhar, $ff);
				$data_output = $this->makeData($data, $name, $ff);
				$this->getLogger()->info("§f" . $data_output . "§aGenerated!");
			}else{
				$this->getLogger()->info("§c$ff is not allowed image type");
			}
			unlink($this->filePhar . "Image-Input/" . $f);
		}
	}
	
	public function getDataFromImage(string $file, string $type){
		switch($type){
			case 'jpeg':
				$im = \imagecreatefromjpeg($file);
				break;
			case 'png':
				$im = \imagecreatefrompng($file);
				break;
			case 'xpm':
				$im = \imagecreatefromxpm($file);
				break;
			case 'xbm':
				$im = \imagecreatefromxbm($file);
				break;
			case 'bmp':
				$im = \imagecreatefrombmp($file);
				break;
			case 'wbmp':
				$im = \imagecreatefromwbmp($file);
				break;
			case 'webp':
				$im = \imagecreatefromwebp($file);
				break;
		}

		return $im;
	}
    
	public function makeData(string $data, string $name, string $type) : string{
		$bytes = "";
		$m = getimagesize($this->filePhar . "Image-Input/" . $name . "." . $type)[0];
		$n = getimagesize($this->filePhar . "Image-Input/" . $name . "." . $type)[1];
		for($y = 0; $y < $n; ++$y){
			for($x = 0; $x < $m; ++$x){
				$colorat = imagecolorat($data, $x, $y);
				$a = ((~((int)($colorat >> 24))) << 1) & 0xff;
				$r = ($colorat >> 16) & 0xff;
				$g = ($colorat >> 8) & 0xff;
				$b = $colorat & 0xff;
				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		file_put_contents($this->filePhar . "Data-Output/" . $name . ".txt" , base64_encode($bytes));

		return $name . ".txt";
	}
}
