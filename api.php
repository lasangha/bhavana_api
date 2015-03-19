<?php
/**
 * The api :)
 */

iconv_set_encoding("internal_encoding", "UTF-8");
iconv_set_encoding("input_encoding", "UTF-8");
iconv_set_encoding("output_encoding", "UTF-8");
//var_dump(iconv_get_encoding('all'));

include_once("db.php");

# Connecting to the db
#
dbConnect();

#
## What do you want?
#

# I want to get a list of causes
if(@$_GET['what'] == 'getCauses'){

	if(!isset($_GET['ini'])){
		$_GET['ini'] = 0;
	}

	echo $q = sprintf("SELECT * FROM causes LIMIT %s, %s", $_GET['ini'], $_GET['ini'] + 10);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$allCauses[] = $row;
	} 
	//print_r($allCauses);
	print json_encode($allCauses);

}

/**
 * Add time to a cause
 * call = what=addToCause&causeCode=paz&totalTime=888&where=here&who=me
 */
elseif(@$_POST['what'] == 'addToCause'){

	$idCause = 0;

	//Get the correct id of the cause
	$q = sprintf("SELECT idCause FROM causes WHERE code = '%s'", $_POST['causeCode']);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$idCause = $row->idCause;
	}

	if($idCause > 0){
		// Register the new time
		$q = sprintf("INSERT INTO `meditations` (`timestamp`, `totalTime`, `idCause`, `where`, `who`)
						VALUES ('%s', '%s', '%s', '%s', '%s')",
						time(),
						$_POST['totalTime'],
						$idCause,
						$_POST['where'],
						$_POST['who']
						);
			$r = dbQuery($q);
		
		// Add up all times
		$q = sprintf("UPDATE causes SET totalTime = totalTime + %s WHERE idCause = '%s'", $_POST['totalTime'], $idCause);
		$r = dbQuery($q);
	}

	print 1;

}

# I want to get a list of causes
if(@$_GET['what'] == 'getCausesTimes'){

	if(!isset($_GET['ini'])){
		$_GET['ini'] = 0;
	}

	$q = sprintf("SELECT idCause, name, totalTime FROM causes LIMIT %s, %s", $_GET['ini'], $_GET['ini'] + 10);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$allCauses[] = $row;
	}

	printJson($allCauses);

}

# I will print stuff in json
function printJson($what){
	header('Content-Type: text/html; charset=utf-8');
	header('Content-Type: application/json');
	print drupal_json_encode($what);
	//print json_last_error_msg();
}
# This two functions where borrowed from Drupal, I am having problems with the database encoding, it must be utf-8
# it is supposed to be, but who knows, the point is that json_encode keeps giving me errors.
function drupal_json_encode_helper($var) {
	switch (gettype($var)) {
	case 'boolean':
		return $var ? 'true' : 'false'; // Lowercase necessary!

	case 'integer':
	case 'double':
		return $var;

	case 'resource':
	case 'string':
		// Always use Unicode escape sequences (\u0022) over JSON escape
		// sequences (\") to prevent browsers interpreting these as
		// special characters.
		$replace_pairs = array(

			// ", \ and U+0000 - U+001F must be escaped according to RFC 4627.
			'\\' => '\u005C',
			'"' => '\u0022',
			"\x00" => '\u0000',
			"\x01" => '\u0001',
			"\x02" => '\u0002',
			"\x03" => '\u0003',
			"\x04" => '\u0004',
			"\x05" => '\u0005',
			"\x06" => '\u0006',
			"\x07" => '\u0007',
			"\x08" => '\u0008',
			"\x09" => '\u0009',
			"\x0a" => '\u000A',
			"\x0b" => '\u000B',
			"\x0c" => '\u000C',
			"\x0d" => '\u000D',
			"\x0e" => '\u000E',
			"\x0f" => '\u000F',
			"\x10" => '\u0010',
			"\x11" => '\u0011',
			"\x12" => '\u0012',
			"\x13" => '\u0013',
			"\x14" => '\u0014',
			"\x15" => '\u0015',
			"\x16" => '\u0016',
			"\x17" => '\u0017',
			"\x18" => '\u0018',
			"\x19" => '\u0019',
			"\x1a" => '\u001A',
			"\x1b" => '\u001B',
			"\x1c" => '\u001C',
			"\x1d" => '\u001D',
			"\x1e" => '\u001E',
			"\x1f" => '\u001F',

			// Prevent browsers from interpreting these as as special.
			"'" => '\u0027',
			'<' => '\u003C',
			'>' => '\u003E',
			'&' => '\u0026',

			// Prevent browsers from interpreting the solidus as special and
			// non-compliant JSON parsers from interpreting // as a comment.
			'/' => '\u002F',

			// While these are allowed unescaped according to ECMA-262, section
			// 15.12.2, they cause problems in some JSON parsers.
			"\xe2\x80\xa8" => '\u2028', // U+2028, Line Separator.
			"\xe2\x80\xa9" => '\u2029', // U+2029, Paragraph Separator.
		);

		return '"' . strtr($var, $replace_pairs) . '"';

	case 'array':
		// Arrays in JSON can't be associative. If the array is empty or if it
		// has sequential whole number keys starting with 0, it's not associative
		// so we can go ahead and convert it as an array.
		if (empty($var) || array_keys($var) === range(0, sizeof($var) - 1)) {
			$output = array();
			foreach ($var as $v) {
				$output[] = drupal_json_encode_helper($v);
			}
			return '[ ' . implode(', ', $output) . ' ]';
		}
		// Otherwise, fall through to convert the array as an object.

	case 'object':
		$output = array();
		foreach ($var as $k => $v) {
			$output[] = drupal_json_encode_helper(strval($k)) . ':' . drupal_json_encode_helper($v);
		}
		return '{' . implode(', ', $output) . '}';

	default:
		return 'null';
	}
}

function drupal_json_encode($var) {
	// The PHP version cannot change within a request.
	static $php530;

	if (!isset($php530)) {
		$php530 = version_compare(PHP_VERSION, '5.3.0', '>=');
	}

	if ($php530) {
		echo 11;
		// Encode <, >, ', &, and " using the json_encode() options parameter.
		//<F8return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
	}

	// json_encode() escapes <, >, ', &, and " using its options parameter, but
	// does not support this parameter prior to PHP 5.3.0.  Use a helper instead.
	//include_once DRUPAL_ROOT . '/includes/json-encode.inc';
	return drupal_json_encode_helper($var);
}
