<?php 
/*
//experimenting with the use of binary numbers to define stacks
$decnum = 64534; $binum = decbin($decnum); $binumrev = strrev($binum);
echo $binum.' len='.strlen($binum).' '.$binumrev .' char 4='.substr($binumrev,4-1,1) .'<br>'.'<br>';
$logLevel=3;
*/

//date_default_timezone_set("Europe/London"); 

ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('log_errors',TRUE);
ini_set('html_errors',TRUE);
ini_set('error_log',$_SERVER["DOCUMENT_ROOT"].'/stacks2/Logs2/error_log.txt');
//ini_set('display_errors',FALSE);

require $_SERVER["DOCUMENT_ROOT"]."/stacks2/Brian2/Functions21-8.inc";

//set_error_handler("fnErrorHandler",E_ALL);

$callString = filter_input(INPUT_GET, 'go');
if ($callString == null) {
   $callString = filter_input(INPUT_GET, 'GO');}

if ($callString == null) {
	echo 'go message not found!!! (stacks_2 for byethost.com)';
	fnCommentLog("go parameters not found");
	//fnLogMessageToDb("go parameters not found");
	fnErrorHandler(0,"go parameters not found"); }
	//error_log("go parameters not found",3,"../../Brian/error_log.txt");}   
else {
	fnDbConnect();
	$logLevel=3;
	$fnResp = fnProcessInput($callString);
	echo print_r($fnResp, TRUE);
	//fnDisplayTable($fnResp);
		
	//include $_SERVER["DOCUMENT_ROOT"]."/stacks2/Brian2/GC1-1.inc";
		
	fnDbDisconnect(); }

	
function fnDisplayTable($inputString) {
	fnLogMessageToDb("start display table");
	//only do this if the input is a game status string
	//if (substr($inputString,4,4) != "game") {return;}
	echo '<br>'.'<br>'.'<br>';
	print_r($inputString);
	//find game reference
	$inputString = strtolower($inputString);
	$inx = strpos($inputString,"areasize",0);
	$inx2 = strpos($inputString,":",$inx);
	$inx3 = strpos($inputString,",",$inx2);
	$areaSize = substr($inputString,$inx2+2,$inx3-$inx2-3);
	//$gameRow = fnQrySelectGame($gameRef);
	//echo 'area size=' . '< game ref=' . $gameRef . '< input string'  . '<';
	echo "<p>";
	echo "<table border = 1>";
	echo "<tr>";
	echo "<td>" . "P1 Reserve" . "</td>";
	for ($i=1; $i <= $areaSize; $i++) {
		echo "<td>" . "Stack" . $i . "</td>";
	}
	echo "<td>" . "P2 Reserve" . "</td>";
	echo "</tr>";

	//$stackRows = fnQrySelectStacks($gameRef,0);
	//find the number of tracks
	$inx = strpos($inputString, "areawidth", 0);
	$inx2 = strpos($inputString, ":",$inx);
	$inx3 = strpos($inputString, ",",$inx2);
	$areaWidth = substr($inputString, $inx2+2, $inx3-$inx2-3);	
	echo '<br>' . 'area width >' . $areaWidth . '<' . '<br>';
	//loop through the tracks 
	for ($hSub = $areaWidth-1; $hSub >= 0; $hSub--) {
		//set new row in the output table
		echo "<tr>"; 
		//loop through the steps from left to right
		for ($sSub = 0; $sSub <= $areaSize + 1; $sSub++) {
			$stepNum = '8' . $hSub . $sSub;
			//find the height
			$inx = strpos($inputString, $stepNum, 0);
			$inx1 = strpos($inputString, "height", $inx);
			$inx2 = strpos($inputString, ":", $inx1);
			$inx3 = strpos($inputString, ",", $inx2);
			$height = substr($inputString, $inx2+2, $inx3-$inx2-3);	
			if ($height == 0) $height = "";
			//find the owner (Top)
			$inx1 = strpos($inputString, "top", $inx);
			$inx2 = strpos($inputString, ":", $inx1);
			$inx3 = strpos($inputString, "}", $inx2);
			$owner = strtoupper(substr($inputString, $inx2+2, $inx3-$inx2-3));	
			//if ($owner == "") $owner = "E";
			echo "<td>" . $owner . '/' . $height . "</td>"; 
		} 
		//set end of row in the output table
		echo "</tr>";
	}
	 
}
	
?>
