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

/*
$q = sprintf("
	SELECT FROM_UNIXTIME(m.timestamp, '%%Y.%%e.%%m') AS day, SUM(m.totalTime) AS totalTime, m.idCause, c.name AS cName, c.code AS cCode
	FROM meditations m
	INNER JOIN causes c ON c.idCause = m.idCause
	WHERE timestamp > 90
	GROUP BY idCause, day
	ORDER BY day
	", time());

$r = dbQuery($q);

$labels = array();
$times  = array();
$details = array();

while($row = $r->fetch_object()){
	$stuff[$row->cCode][] = array("name" => $row->cName, "day" => $row->day, "totalTime" => $row->totalTime);
	$details[$row->cName]['details'][$row->day] = $row->totalTime;
	//$details[$row->cCode]['name'] = $row->cName;
	//$codes[] = $row->cCode;
	$dates[] = $row->day;
}

# Remove duplicates
//$codes = array_unique($codes);
$dates = array_unique($dates);

# Lets fix the dates
$nDates = array();
foreach($dates as $d){
	$nDates[] = $d;
}


print_r($details);
# Fix dates to see if there are any missing ones?
foreach($details as $topic => $d){
	echo "Working on topic: $topic <br />";
		foreach($dates as $date){
			echo "Working on date: $date <br />";
			if(!array_key_exists($date, $details[$topic]['details'])){
				echo "Missing that date <br/ >";
				$details[$topic]['details'][$date] = 0;
				ksort($details[$topic]['details']);
				break;
			}
		}
}

# Now, lets get rid of al that is not needed
#
$justTheNumbers = array();
foreach($details as $topic => $d){
	foreach($dates as $date){
		$justTheNumbers[$topic][] = $details[$topic]['details'][$date];
	}
}

print_r($details);
print_r($justTheNumbers);

printJson(array('details' => $justTheNumbers, 'dates' => $nDates));
/*
foreach($dates as $date){
	echo "Working on date $date <br />";
	# Now, lets see if each code has this date
	foreach($stuff as $s => $ss){
		echo "Working on topic $s <br/>";
		//print_r($ss);
		foreach($ss as $t => $tt){
			echo "Working on this $t<br/>";
			print_r($stuff[$s][$t]['name']); echo "<br />";
			if($tt['day'] == $date){
				//echo ";bion";
				echo "I have that date <br/>";
				//continue;
				break;
			}
			else{
				//$stuff[$s][] = array("name" => $stuff[$s][$t]['name'], "day" => $date, "totalTime" => 0);
				echo "I am missing that date: $date on $s and $t <br />";
				break;
			}

		}
	}
}
//print_r($dates);
//print_r($codes);
exit;
 */

#
## What do you want?
#

# I want to get a list of causes
if(@$_GET['what'] == 'getCauses'){

	if(!isset($_GET['ini'])){
		$_GET['ini'] = 0;
	}

	$q = sprintf("SELECT * FROM causes LIMIT %s, %s", $_GET['ini'], $_GET['ini'] + 10);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$allCauses[] = $row;
	} 
	//print_r($allCauses);
	print json_encode($allCauses);

}

# Total meditation times
if(@$_GET['what'] == 'getAllMeditationTimesPerDay'){

	# Where to start?
	if(!isset($_GET['ini'])){
		$_GET['ini'] = 0;
	}

	$q = sprintf("
		SELECT FROM_UNIXTIME(m.timestamp, '%%Y.%%e.%%m') AS day, SUM(m.totalTime) AS totalTime, m.idCause, c.name AS cName, c.code AS cCode
		FROM meditations m
		INNER JOIN causes c ON c.idCause = m.idCause
		WHERE timestamp > 90
		GROUP BY idCause, day
		ORDER BY day
		", (time()-($_GET['ini']*86400)));

	$r = dbQuery($q);

	$stuff = array();
	$details = array();
	$dates  = array();

	while($row = $r->fetch_object()){
		$stuff[$row->cCode][] = array("name" => $row->cName, "day" => $row->day, "totalTime" => $row->totalTime);
		$details[$row->cName]['details'][$row->day] = $row->totalTime;
		$dates[] = $row->day;
	}

	# Remove duplicates
	$dates = array_unique($dates);

	# Lets fix the dates (remove keys)
	$nDates = array();
	foreach($dates as $d){
		$nDates[] = $d;
	}

	# Fix dates to see if there are any missing ones?
	foreach($details as $topic => $d){
		//echo "Working on topic: $topic <br />";
		foreach($dates as $date){
			//echo "Working on date: $date <br />";
			if(!array_key_exists($date, $details[$topic]['details'])){
				//echo "Missing that date <br/ >";
				$details[$topic]['details'][$date] = 0;
				# Sort them out
				ksort($details[$topic]['details']);
				break;
			}
		}
	}

	# Now, lets get rid of al that is not needed
	$justTheNumbers = array();
	foreach($details as $topic => $d){
		foreach($dates as $date){
			$justTheNumbers[$topic][] = $details[$topic]['details'][$date];
		}
	}

	//print_r($details);
	//print_r($justTheNumbers);

	printJson(array('details' => $justTheNumbers, 'dates' => $nDates));

	/*
	//SELECT FROM_UNIXTIME(m.timestamp, '%Y.%e.%m') AS day, SUM(m.totalTime) AS totalTime, m.idCause, c.name FROM meditations m INNER JOIN causes c ON c.idCause = m.idCause WHERE timestamp > 90 GROUP BY day, idCause ORDER BY day

	$q = sprintf("
		SELECT FROM_UNIXTIME(m.timestamp, '%%Y.%%e.%%m') AS day, SUM(m.totalTime) AS totalTime
		FROM meditations m
		WHERE timestamp > %s
		GROUP BY day
		ORDER BY day
		", (time()-($_GET['ini']*86400)));

	$r = dbQuery($q);

	$labels = array();
	$times  = array();

	while($row = $r->fetch_object()){
		$labels[] = $row->day;
		$times[] = $row->totalTime;
	} 

	print json_encode(array("labels" => $labels, "times" => $times));
	 */
}

# A list of meditation times per person
if(@$_GET['what'] == 'getMyMeditationTimes'){

	# Where to start?
	if(!isset($_GET['ini'])){
		$_GET['ini'] = 0;
	}

	# Get the details about the user
	$user = loadUserByEmail($_GET['email']);

	$q = sprintf("
		SELECT FROM_UNIXTIME(timestamp, '%%Y.%%e.%%m') AS day, SUM(totalTime) AS totalTime
		FROM meditations
		WHERE idUser = '%s'
		AND timestamp > %s
		GROUP BY day
		ORDER BY day
		", $user->idUser, (time()-($_GET['ini']*86400)));

	$r = dbQuery($q);

	$labels = array();
	$times  = array();

	while($row = $r->fetch_object()){
		$labels[] = $row->day;
		$times[] = $row->totalTime;
	}

	printJson(array("labels" => $labels, "times" => $times));

}

# Generate tmp pswd
if(@$_GET['what'] == 'recoverPwd'){

/*
	# Get the details about the user
	$user = loadUserByEmail($_GET['email']);

	# Generate new tmp pwd
	$tmpPwd = md5(rand() . time() . $user->email);

	# Insert the tmp pwd

	updateUser($user->id, array("pwd" => $tmpPwd));

	$q = sprintf("
		SELECT FROM_UNIXTIME(timestamp, '%%Y.%%e.%%m') AS day, SUM(totalTime) AS totalTime
		FROM meditations
		WHERE idUser = '%s'
		AND timestamp > %s
		GROUP BY day
		ORDER BY day
		", $user->idUser, (time()-($_GET['ini']*86400)));

	$r = dbQuery($q);

	$labels = array();
	$times  = array();

	while($row = $r->fetch_object()){
		$labels[] = $row->day;
		$times[] = $row->totalTime;
	} 

	print json_encode(array("labels" => $labels, "times" => $times));
 */
}



/**
 * Add time to a cause
 * call = what=addToCause&causeCode=paz&totalTime=888&where=here&who=me
 */
elseif(@$_POST['what'] == 'addToCause'){

	$idCause = 0;

	# Get the correct id of the cause
	$q = sprintf("SELECT idCause FROM causes WHERE code = '%s'", $_POST['causeCode']);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$idCause = $row->idCause;
	}

	# Get the details about the user
	$user = loadUserByEmail($_POST['email']);	

	if($idCause > 0){
		// Register the new time
		$q = sprintf("INSERT INTO `meditations` (`timestamp`, `totalTime`, `idCause`, `where`, `idUser`)
			VALUES ('%s', '%s', '%s', '%s', '%s')",
				time(),
				$_POST['totalTime'],
				$idCause,
				$user->country,
				$user->idUser
			);
		$r = dbQuery($q);

		// Add up all times
		$q = sprintf("UPDATE causes SET totalTime = totalTime + %s WHERE idCause = '%s'", $_POST['totalTime'], $idCause);
		$r = dbQuery($q);
	}

	print 1;

}

/**
 * Insert users
 */
elseif(@$_POST['what'] == 'addUser'){

	if($_POST['pwd'] == "" | !isset($_POST['pwd'])){
		# I need a password
		printJson(0);
	}

	# Does the user exist?
	$user = loadUserByEmail($_POST['email']);

	if($user->idUser > 0){
		printJson(0);
	}
	else{
		// Register the new user
		$q = sprintf("INSERT INTO `users` (`name`, `email`, `timestamp`, `country`, `pwd`)
			VALUES ('%s', '%s', '%s', '%s', '%s')",
				$_POST['name'],
				$_POST['email'],
				time(),
				$_POST['country'],
				md5($_POST['pwd'])
			);

		$r = dbQuery($q);

		sendWelcomeMail($_POST['name'], $_POST['email']);

		printJson(1);
	}
}

/**
 * Insert users
 */
elseif(@$_POST['what'] == 'logUserIn'){

	$user = loadUserByEmail($_POST['email']);

	# User does not exist
	if($user->idUser == 0){
		printJson(2);
	}
	# All good
	elseif($user->pwd == md5($_POST['pwd'])){
		# Remove the pwd, just in case
		$user->pwd = "";
		printJson($user);
	}

	# Wrong pwd
	else{
		printJson(0);
	}

}


# Get total meditation times per cause
if(@$_GET['what'] == 'getCausesTimes'){

	if(!isset($_GET['ini'])){
		$_GET['ini'] = 0;
	}

	$where = "";
	if(!isset($_GET['cause'])){
		$where = sprintf(" WHERE idCause = '%s'", $_GET['cause']);
	}


	$q = sprintf("SELECT idCause, name, totalTime FROM causes %s LIMIT %s, %s", $where, $_GET['ini'], $_GET['ini'] + 10);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$allCauses[] = $row;
	}
	printJson($allCauses);

}

# Cause with maximum meditation
if(@$_GET['what'] == 'getCausesTimesMax'){

	$q = "SELECT idCause, name, totalTime FROM causes ORDER BY totalTime DESC LIMIT 1";

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$maxCause = $row;
	}

	printJson($maxCause);

}

# Some function to fix the utf-8 problems
function _fixUTF($value, $key){

	global $newWhat;

	$newWhat[utf8_encode($key)] = utf8_encode($value);

}

# Some function to fix the utf-8 problems
function fixUTF($a){

	global $newWhat;
	foreach($a as $value => $v){
		echo $value;
		echo mb_detect_encoding($value);
		/*if(is_array($v)){
			echo $value . " is an array";
			$newWhat[utf8_encode($value)] = fixUTF($value);
		}
		else{
			echo "bien: " . $value;
			//return utf8_encode($value);
		}*/
	}
	//$newWhat[utf8_encode($key)] = utf8_encode($value);

}

# I will print stuff in json
function printJson($what){

	global $newWhat;

	header('Content-Type: text/html; charset=utf-8');
	header('Content-Type: application/json');
	print json_encode($what);
	//echo json_last_error_msg();
	exit();
}

# Load a user by id
function loadUserById($id){

	# Get the correct id of the cause
	$q = sprintf("SELECT * FROM users WHERE id = '%s' LIMIT 1", $id);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$user = $row;
	}

	return $user;

}

# Load a user by email
function loadUserByEmail($email){

	# By default the user does not exist
	$user = (object) array('idUser' => 0);

	# Get the correct id of the cause
	$q = sprintf("SELECT * FROM users WHERE email = '%s' LIMIT 1", $email);

	$r = dbQuery($q);

	while($row = $r->fetch_object()){
		$user = $row;
	}

	return $user;

}

# I update user's details
function updateUser($userId, $dets = array()){

	$user = loadUserById($userId);

	# Which fields should I modify
	if(array_key_exists('name', $dets)){
		$user->name = $dets['name'];
	}

	if(array_key_exists('pwd', $dets)){
		$user->pwd = $dets['pwd'];
	}

	if(array_key_exists('email', $dets)){
		$user->email = $dets['email'];
	}

	if(array_key_exists('country', $dets)){
		$user->country = $dets['country'];
	}

	$q = sprintf("UPDATE users set");

}

# send welcome email
function sendWelcomeMail($name, $email){
	$theMail = "
		Hola {name} y bienvenido a Bhavana, su 'Retiro Personal De Meditación'

		Su cuenta ya ha sido creada y está lista para ser utilizada.

		Muchas gracias por tomar este tiempo para mejor su vida y la de todos los seres.

		Recuerde que siempre estamos al alcance si tiene preguntas o requiere ayuda.

		--  La Sangha, Organización Para El Desarrollo y Crecimiento Humano
		";

	$theMail = str_replace("{name}", $name, $theMail);

	sendMail($email, "Bienvenido a Bhavana, Su retiro personal", $theMail);
	sendMail("felipeannica@yahoo.com", "Bienvenido a Bhavana, Su retiro personal", $theMail);

}

function sendMail($to, $subject, $msg, $from = "no-reply@lasangha.org", $replyTo = "no-reply@lasangha.org"){

	$headers = 
		'Content-type: text/plain; charset=utf-8' . "\r\n" .
		'From: ' . $from . "\r\n" .
		'Reply-To: ' . $replyTo . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $msg, $headers);

}
