<?php

namespace A17\Twill\Services\MediaLibrary;

use Illuminate\Config\Repository as Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AwsImage implements ImageServiceInterface
{
    use ImageServiceDefaults;

    protected $config;
    private $awsapi;
    private $bucket;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->awsapi = $this->config->get('awsimage.host');
        $this->bucket = $this->config->get('filesystems.disks.s3.bucket');
	}


    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getUrl($id, array $params = [])
    {
        $defaultParams = config('awsimage.default_params');
		$par = array_replace($defaultParams, $params);

		//INPUT
    	// fm =jpg, gif
    	// q = quality, p.e. 60
    	// fit crop, max, min
    	// dpr DPR_QUALITIES 1 to 5 (1 = tanti punti quanti, 2 = 2x

		/* //OUTPUT 
        $richiesta = array(
        	'bucket' => $this->bucket,
        	'key' => $id,
        	'outputFormat' => 'jpg',
        	'edits' => array (
                "blur" => 0,
                "jpeg" => array( "quality" => 100),
                "extract" => array (
                	"left" => 30, "top" => 50, "width" => 320, "height" => 240,
				),
        		"resize" => array (
				      "position" => "top",
				      //"gravity" => "south",
				      "width" => 320,
				      "height" => 240,
				      "fit" => "cover" // contain fill inside outside
				      //"background" => array( 'r'=> 255, 'g'=> 255, 'b'=> 255, 'alpha' => 1 )
			    )
        	)
        );
        */
        $richiesta = array(
        	'bucket' => $this->bucket,
        	'key' => $id,
            'edits' => array ()
		);
 		if (Arr::has($par, 'fm')) {
 			$fmt = $par['fm'];
 			if ($fmt == "jpg") { $fmt = "jpeg"; }
           	$richiesta["outputFormat"] = $fmt;
            if (Arr::has($par, 'q')) {
 				$richiesta["edits"][$fmt] = array( 'quality' => 80); //$par['q']);			
 			}
 		}
		if (Arr::has($par, $this->cropParamsKeys)) {
            $richiesta["edits"]["extract"] = array (
                "left" => $par['crop_x'],
				"top" => $par['crop_y'],
                "width" => $par['crop_w'],
				"height" => $par['crop_h'],
			);
		}
 		if (Arr::has($par, "w") && Arr::has($par, "h")) {
 			$fit = "cover";
 			if (Arr::has($par, "fit")) {
 				$f = $par['fit'];
 				if (($f == "fill") || ($f == "contain") || ($f == "inside") || ($f == "outside")) {
 				 $fit = $f; 
 				}
 			}
            $richiesta["edits"]["resize"] = array (
            	"width" => $par['w'],
				"height" => $par['h'],
				"fit" => $fit
            );
 		}
 		if (Arr::has($par, "blur")) {
            $richiesta["edits"]["blur"] = $par['blur'];
 		}

		$encoded = base64_encode(json_encode($richiesta));
        return secure_url( $this->awsapi . "/" . $encoded );
    }

    /**
     * @param string $id
     * @param array $crop_params
     * @param array $params
     * @return string
     */
    public function getUrlWithCrop($id, array $crop_params, array $params = [])
    {
		return $this->getUrl($id,$crop_params + $params);
    }

    /**
     * @param string $id
     * @param array $cropParams
     * @param int $width
     * @param int $height
     * @param array $params
     * @return string
     */
    public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = [])
    {
		return $this->getUrl($id, $crop_params + $params);
    }

    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getLQIPUrl($id, array $params = [])
    {
        $defaultParams = config('awsimage.lqip_default_params');
		$par = array_replace($defaultParams, $params);
		return $this->getUrl($id, $par);
    }

    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getSocialUrl($id, array $params = [])
    {
        $defaultParams = config('awsimage.social_default_params');
		$par = array_replace($defaultParams, $params);
		return $this->getUrl($id, $par);
    }

    /**
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getCmsUrl($id, array $params = [])
    {
        $defaultParams = config('awsimage.cms_default_params');
		$par = array_replace($defaultParams, $params);
		return $this->getUrl($id, $par);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getRawUrl($id)
    {
        return secure_url( $this->awsapi . "/" . $id );
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getDimensions($id)
    {
        $url = $this->getRawUrl($id);

        try {
            list($w, $h) = getimagesize($url);

            return [
                'width' => $w,
                'height' => $h,
            ];
        } catch (\Exception $e) {
            return [
                'width' => 0,
                'height' => 0,
            ];
        }
    }

}