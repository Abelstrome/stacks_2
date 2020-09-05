<?php
echo 'hello 1';

ini_set("log_errors", TRUE);
ini_set("error_log", $_SERVER['DOCUMENT_ROOT'].'/stacks2/Logs2/api_error_log.txt');

ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('html_errors',FALSE);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

if ($_SERVER['SERVER_NAME'] == 'localhost') require __DIR__ . '/stacks2/dummy/vendor/autoload.php';
else require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

if ($_SERVER['SERVER_NAME'] == 'localhost') $app->setBasePath('/dummy');
else if (strpos(strtoupper($_SERVER['DOCUMENT_ROOT']), 'AMAZON') !== False) $app->setBasePath('/stacks2/dummy');
else $app->setBasePath('/');

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

//-----------//
//dummy for Retrofit
//-----------//
$app->get('/retro', function (Request $request, Response $response, $args) {

	$fnResp =  json_encode(array("type" => "test", 'ref' => 'retrofit test does not call stacks programs', 
			'items' => array(array('count' => '1', 'dataString' => 'data string 1'), array('count' => 'two', 'dataString' => 'data string 2'))));;
	//echo $fnResp;
	$response->getBody()->write($fnResp);
	//return $response;
	return $response->withHeader('Content-Type', 'application/json');
	
});

//-----------//
//game status
//-----------//
$app->get('/games/{game}', function (Request $request, Response $response, $args) {

	$fnResp = getGameStatus($args['game']);
	$response->getBody()->write($fnResp);
	return $response;
});

function getGameStatus ($gameRef){
	require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
	$callString = json_encode(array('type' => 'game_status', 'gameRef' => $gameRef));
	fnDbConnect();
	$fnResp = fnProcessInput($callString);
	return $fnResp;
}

//-----------//
//player_info
//-----------//
$app->get('/players/{name}', function (Request $request, Response $response, $args) {
	
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/stacks2/Logs2/api_error_log.txt', 'player name ' . $args['name'] . "\n", FILE_APPEND);
	$fnResp = getPlayerInfo($args['name']);
	$response->getBody()->write($fnResp);
	//echo '>' . $fnResp . '<';
	//return $response;
	return $response->withHeader('Content-Type', 'application/json');
});

function getPlayerInfo ($player_name){
	
	require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
	$callString = json_encode(array('type' => 'player_info', 'playername' => $player_name));
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/stacks2/Logs2/api_error_log.txt', $callString . "\n", FILE_APPEND);
	fnDbConnect();
	$fnResp = fnProcessInput($callString);
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/stacks2/Logs2/api_error_log.txt', $fnResp . "\n", FILE_APPEND);
	return trim($fnResp);
}

//----------//
//open_games
//----------//
$app->get('/games', function (Request $request, Response $response, $args) {
	$fnResp = getOpenGames();
	$response->getBody()->write($fnResp);
	return $response;
});

function getOpenGames (){
	require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
	$callString = json_encode(array('type' => 'open_games'));
	fnDbConnect();
	$fnResp = fnProcessInput($callString);
	return $fnResp;
}

//--------//
//register
//--------//
$app->post('/players', function (Request $request, Response $response, $args) {

	$requestContent  = $request->getBody();
	if (strlen($requestContent) == 0) {
		//no content in message body
		$fnResp = 'invalid api content - message body empty'; }
	else {
		$requestContent = json_decode($requestContent,True);
		if (is_null($requestContent)) {
			//error in json function
			$fnResp = 'json error in message body: ' . jsonErrors(json_last_error()); }
		else {
			$fnResp = putRegisterPlayer($requestContent); }
	}
	
	$response->getBody()->write($fnResp);
	return $response;
});

function putRegisterPlayer ($playerData){
	if (array_key_exists('playername', $playerData)){
		if (array_key_exists('pin', $playerData)) {
			require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
			$callString = json_encode(array('type' => 'register', 'playername' => $playerData['playername'], 'pin' => $playerData['pin']));
			fnDbConnect();
			$fnResp = fnProcessInput($callString); }
		else {
			$fnResp = 'invalid api content - pin missing'; }
	}
	else {
		$fnResp = 'invalid api content - playername missing';
	}
	return $fnResp;
}

//------------------//
//P1 create new game
//------------------//
$app->post('/games', function (Request $request, Response $response, $args) {
	
	$requestContent  = $request->getBody();
	if (strlen($requestContent) == 0) {
		//no content in message body
		$fnResp = 'invalid api content - message body empty'; }
	else {
		$requestContent = json_decode($requestContent,True);
		if (is_null($requestContent)) {
			//error in json function
			$fnResp = 'json error in message body: ' . jsonErrors(json_last_error()); }
		else {
			$fnResp = postP1NewGame($requestContent); }
	}
	
	$response->getBody()->write($fnResp);
	return $response;
});

function postP1NewGame ($requestData){
	
	if (array_key_exists('playername', $requestData)){
		if (array_key_exists('pin', $requestData)) {
			if (array_key_exists('withGC', $requestData)) {
				$callString = json_encode(array('type' => 'p1_new_game', 'playername' => $requestData['playername']. '/' . $requestData['pin'], 'gamecentral' => $requestData['withGC']));
			}
			else {
				//withGC is optional so if its not supplied then default to 'N'
				$callString = json_encode(array('type' => 'p1_new_game', 'playername' => $requestData['playername']. '/' . $requestData['pin'], 'gamecentral' => 'N'));
			}
			require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
			fnDbConnect();
			$fnResp = fnProcessInput($callString); 
		}
		else {
			$fnResp = 'invalid api content - pin missing'; }
	}
	else {
		$fnResp = 'invalid api content - playername missing'; }
	
	return $fnResp;
}

//-----------//
//player move
//-----------//
$app->put('/games/{game}/players/{name}', function (Request $request, Response $response, $args) {
	
	$requestContent  = $request->getBody();
	if (strlen($requestContent) == 0) {
		//no content in message body
		$fnResp = 'invalid api content - message body empty'; }
	else {
		$requestContent = json_decode($requestContent, True);
		if (is_null($requestContent)) {
			//error in json function
			$fnResp = 'json error in message body:' . jsonErrors(json_last_error()); }
		else {
			$fnResp = putPlayerMove($args['game'], $args['name'], $requestContent);	}
	}

	$response->getBody()->write($fnResp);
	return $response;
});

function putPlayerMove ($gameRef, $playername, $requestData) {
	
	if (array_key_exists('pin', $requestData)){
		if (array_key_exists('movepos', $requestData) and array_key_exists('movedir', $requestData)) {
			require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
			$callString = json_encode(array('type' => 'pmove', 'playername' => $playername.'/'.$requestData['pin'], 'gameRef' => $gameRef, 
							'movepos' => $requestData['movepos'], 'movedir' => $requestData['movedir'])) ;
			fnDbConnect();
			$fnResp = fnProcessInput($callString); 
		}
		else {
			$fnResp = 'invalid api content - move position and/or direction missing'; }
	}
	else {
		$fnResp = 'invalid api content - pin missing'; }
	
	return $fnResp;
}

//----------------//
//P2 join new game
//----------------//
$app->put('/games/{game}', function (Request $request, Response $response, $args) {
	
	$requestContent  = $request->getBody();
	if (strlen($requestContent) == 0) {
		//no content in message body
		$fnResp = 'invalid api content - message body empty'; }
	else {
		$requestContent = json_decode($requestContent, True);
		if (is_null($requestContent)) {
			//error in json function
			$fnResp = 'json error in message body:' . jsonErrors(json_last_error()); }
		else {
			$fnResp = getP2NewGame($args['game'], $requestContent);	}
	}

	$response->getBody()->write($fnResp);
	return $response;
});

function getP2NewGame ($gameRef, $playerData){

	if (array_key_exists('playername', $playerData)){
		if (array_key_exists('pin', $playerData)) {
			require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
			$callString = json_encode(array('type' => 'p2_new_game', 'playername' => $playerData['playername'].'/'.$playerData['pin'], 'gameRef' => $gameRef));
			fnDbConnect();
			$fnResp = fnProcessInput($callString); }
		else {
			$fnResp = 'invalid api content - pin missing'; }
	}
	else {
		$fnResp = 'invalid api content - playername missing';
	}
	
	return $fnResp;
}

//------//
//resign
//------//
$app->delete('/games/{ref}/players/{name}', function (Request $request, Response $response, $args) {

	$requestContent  = $request->getBody();
	if (strlen($requestContent) == 0) {
		//no content in message body
		$fnResp = 'invalid api content - message body empty'; }
	else {
		$requestContent = json_decode($requestContent, True);
		if (is_null($requestContent)) {
			//error in json function
			$fnResp = 'json error in message body:' . jsonErrors(json_last_error()); }
		else {
			//assume that the data in the mesage body is the player pin
			$fnResp = deleteResign($args['ref'], $args['name'], $requestContent); }
	}
	
	$response->getBody()->write($fnResp);
	return $response;

});

function deleteResign ($gameRef, $player_name, $pin){
	if (array_key_exists('pin', $pin)) {
		require $_SERVER['DOCUMENT_ROOT']."/stacks2/Brian2/Functions21-8.inc";
		$callString = json_encode(array('type' => 'resign', 'playername' => $player_name.'/'.$pin['pin'], 'gameRef' => $gameRef));
		fnDbConnect();
		$fnResp = fnProcessInput($callString); }
	else {
		$fnResp = 'invalid api content - pin missing'; }

	return $fnResp;
}

//-----------------------//
//json error descriptions
//-----------------------//
function jsonErrors($json_error_code) {
	
	switch ($json_error_code) {
		case JSON_ERROR_DEPTH:
			$fnResp = 'The maximum stack depth has been exceeded';
			break;
		case JSON_ERROR_STATE_MISMATCH:
			$fnResp = 'Invalid or malformed JSON';
			break;
		case JSON_ERROR_CTRL_CHAR:
			$fnResp = 'Control character error, possibly incorrectly encoded';
			break;
		case JSON_ERROR_SYNTAX:
			$fnResp = 'Syntax error';
			break;
		case JSON_ERROR_RECURSION:
			$fnResp = 'One or more recursive references in the value to be encoded';
			break;
		case JSON_ERROR_INF_OR_NAN:
			$fnResp = 'One or more NAN or INF values in the value to be encoded';
			break;
		case JSON_ERROR_UNSUPPORTED_TYPE:
			$fnResp = 'A value of a type that cannot be encoded was given';
			break;
		case JSON_ERROR_INVALID_PROPERTY_NAME:
			$fnResp = 'A property name that cannot be encoded was given';
			break;
		case JSON_ERROR_UTF16:
			$fnResp = 'Malformed UTF-16 characters, possibly incorrectly encoded';
			break;
		default:
			$fnResp = 'unknown error';
			break;
	}
	
	return $fnResp;

}


$app->run();