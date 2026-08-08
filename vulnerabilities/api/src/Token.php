<?php

namespace Src;

use OpenApi\Attributes as OAT;

#[OAT\Schema(required: ['token'])]
class Token {
	private const ENCRYPTION_CIPHER = "aes-256-gcm";

	// A hardcoded key means every install shares the same secret and anyone with
	// the source can mint or read tokens. The key is now generated per install
	// from a CSPRNG and kept outside the document root.
	private static function key() {
		static $key = null;
		if ($key !== null) { return $key; }
		$path = sys_get_temp_dir() . "/dvwa_api_token.key";
		if (is_readable($path)) {
			$raw = file_get_contents($path);
			if ($raw !== false && strlen($raw) === 32) { $key = $raw; return $key; }
		}
		$key = random_bytes(32);
		@file_put_contents($path, $key);
		@chmod($path, 0600);
		return $key;
	}

    # Not sure if this is needed
    #[OAT\Property(example: "11111")]
	public string $token;

	private string $secret;
	private int $expires;

	public function __construct () {
	}

	private static function encrypt($cleartext) {
		$ivlen = openssl_cipher_iv_length(self::ENCRYPTION_CIPHER);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext = openssl_encrypt($cleartext, self::ENCRYPTION_CIPHER, self::key(), $options=0, $iv, $tag);
		$ret = base64_encode ($tag . ":::::" . $iv . ":::::" . $ciphertext);
		return $ret;
	}

	private static function decrypt($ciphertext) {
		$str = base64_decode ($ciphertext);
		$bits = explode (":::::", $str, 3);
		if (count ($bits) != 3) {
			return false;
		}
		$value = $bits[2];
		$iv = $bits[1];
		$tag = $bits[0];
		$cleartext = openssl_decrypt($value, self::ENCRYPTION_CIPHER, self::key(), $options=0, $iv, $tag);
		return $cleartext;
	}
	public function create_token($secret, $expires) {
		$token = self::encrypt (json_encode (array (
						"secret" => $secret,
						"expires" => $expires,
					)));
		return $token;
	}

	public function decrypt_token($token) {
		$decrypted = self::decrypt($token);

		if ($decrypted === false) {
			return false;
		}

		$token = json_decode ($decrypted, true);
		return $token;
	}
}

?>
