<?php 

namespace RespacioHouzezImport;

class common{
	/**
	 * To avoid json_decode limitation
	 */
    public static function jsonToArray($json = false){
        if($json == false) return false;
		//preserving new line chars
        $json = str_replace('\\n', 'newLinePlaceHolder', $json);
		//removing '\' 
		$json = str_replace('\\', '', $json);
		//adding new line chars
		$json = str_replace('newLinePlaceHolder', '\\n', $json);
		//finally decoding json to array
		$array = (array)json_decode($json, true);
		return $array;
    }

	/**
	 * to sanitize array
	 */
	public static function sanitize_text_field_array($array) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = self::sanitize_text_field_array($value);
			} else {
				$array[$key] = sanitize_text_field($value);
			}
		}
		return $array;
	}
}