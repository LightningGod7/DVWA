<?php

// A repeating-key XOR with a hardcoded key is not encryption. Messages are
// now protected with authenticated AES-256-GCM under a per-session key that
// is generated from a CSPRNG and never shipped to the client.
function crypto_key() {
	if( empty( $_SESSION['crypto_key'] ) ) {
		$_SESSION['crypto_key'] = random_bytes( 32 );
	}
	return $_SESSION['crypto_key'];
}

function encrypt_message( $cleartext ) {
	$iv  = random_bytes( 12 );
	$tag = '';
	$ct  = openssl_encrypt( $cleartext, 'aes-256-gcm', crypto_key(), OPENSSL_RAW_DATA, $iv, $tag );
	if( $ct === false ) {
		throw new Exception( "Encryption failed" );
	}
	return base64_encode( $iv . $tag . $ct );
}

function decrypt_message( $encoded ) {
	$raw = base64_decode( $encoded, true );
	if( $raw === false || strlen( $raw ) < 28 ) {
		throw new Exception( "Message could not be decrypted" );
	}
	$iv  = substr( $raw, 0, 12 );
	$tag = substr( $raw, 12, 16 );
	$ct  = substr( $raw, 28 );
	$pt  = openssl_decrypt( $ct, 'aes-256-gcm', crypto_key(), OPENSSL_RAW_DATA, $iv, $tag );
	if( $pt === false ) {
		throw new Exception( "Message could not be decrypted" );
	}
	return $pt;
}

// The account password is stored as a salted bcrypt hash and compared with a
// constant time verifier, never as cleartext.
$password_hash = '$2y$10$8Kx1Zt0h1yq2Yl5s5oO0/uYlq3f6ZQ3Qh0m0YrJ2yF1Ck0oQ8oZbi';

$errors = "";
$success = "";
$messages = "";
$encoded = null;
$encode_radio_selected = " checked='checked' ";
$decode_radio_selected = " ";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	try {
		if (array_key_exists ('message', $_POST)) {
			$message = $_POST['message'];
			if (array_key_exists ('direction', $_POST) && $_POST['direction'] == "decode") {
				$encoded = decrypt_message ($message);
				$encode_radio_selected = " ";
				$decode_radio_selected = " checked='checked' ";
			} else {
				$encoded = encrypt_message ($message);
			}
		}
		if (array_key_exists ('password', $_POST)) {
			$password = $_POST['password'];
			if (password_verify ($password, $password_hash)) {
				$success = "Welcome back user";
			} else {
				$errors = "Login Failed";
			}
		}
	} catch(Exception $e) {
		$errors = $e->getMessage();
	}
}

$html = "
		<p>
		This system lets you exchange messages using authenticated AES-256-GCM. Use the box below to encode and decode messages.
		</p>
		<form name=\"xor\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
			<p>
				<label for='message'>Message:</lable><br />
				<textarea style='width: 600px; height: 56px' id='message' name='message'>" . htmlentities ($message) . "</textarea>
			</p>
			<p>
				<input type='radio' value='encode' name='direction' id='direction_encode' " . $encode_radio_selected . "><label for='direction_encode'>Encode</label> or 
				<input type='radio' value='decode' name='direction' id='direction_decode' " . $decode_radio_selected . "><label for='direction_decode'>Decode</label>
			</p>
			<p>
				<input type=\"submit\" value=\"Submit\">
			</p>
		</form>
";

if (!is_null ($encoded)) {
	$html .= "
			<p>
				<label for='encoded'>Message:</lable><br />
				<textarea readonly='readonly' style='width: 600px; height: 56px' id='encoded' name='encoded'>" . htmlentities ($encoded) . "</textarea>
			</p>";
}

if ($errors != "") {
	$html .= '<div class="warning">' . htmlspecialchars ($errors, ENT_QUOTES, 'UTF-8') . '</div>';
}

if ($messages != "") {
	$html .= '<div class="nearly">' . htmlspecialchars ($messages, ENT_QUOTES, 'UTF-8') . '</div>';
}

if ($success != "") {
	$html .= '<div class="success">' . htmlspecialchars ($success, ENT_QUOTES, 'UTF-8') . '</div>';
}

$html .= "
		<form name=\"ecb\" method='post' action=\"" . htmlspecialchars ($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\">
			<p>
				<label for='password'>Password:</lable><br />
<input type='password' id='password' name='password'>
			</p>
			<p>
				<input type=\"submit\" value=\"Login\">
			</p>
		</form>
";
?>
