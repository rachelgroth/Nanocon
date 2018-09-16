<?php

// This will keep track of the number of events that are read in from the file.
$numberEvents = 0;

/**@fun readCSV($csvFile)
 *	^	Read in CSV file and store in array.
 *
 * @param $csvFile | File Path
 *	^	This is the path to the csv file
 *
 * @auth jasondavis | 2012/05/09
 * @mod NoremacSkich | 2014/09/09
 * 
 * @src http://goo.gl/oi6vjX | 2014/09/09
 *
 */
function readCSV($csvFile){
	$file_handle = fopen($csvFile, 'r');
	
	if(!$file_handle){
		echo "Unable to open \"" . $csvFile . "\"\n";
		echo "Exiting script\n";
		exit(1);
	}
	
	while (!feof($file_handle) ) {
		$line_of_text[] = fgetcsv($file_handle, 0);
		$numberEvents++;
	}
	fclose($file_handle);
	return $line_of_text;
}

/**@fun generateCsv($data, $delimiter = ',', $enclosure = '"')
 *	^	This will convert an 2D array into a csv string.
 *
 * @param $data | 2D Array
 *	^	This is the data to convert
 *
 * @param $delimiter | Character
 *	^	This is the delimiter that you want to use
 *	D	,
 *
 * @param $enclosure | Character
 *	^	This character is used to enclose strings.
 *	D	"
 *
 * @retrn String
 *	^	This will return a string of the array contents.
 *
 * @auth welancers | 2012/08/22
 *
 * @src http://goo.gl/Fc1Yye | 2014/09/10
 *
 */
function generateCsv($data, $delimiter = ',', $enclosure = '"') {
	$handle = fopen('php://temp', 'r+');
	foreach ($data as $line) {
		$line = cleanApost($line);
		fputcsv($handle, $line, $delimiter, $enclosure);
	}
	rewind($handle);
	while (!feof($handle)) {
		$contents .= fread($handle, 8192);
	}
	fclose($handle);
	return $contents;
}

// http://stackoverflow.com/a/26380297/3271665 
// Take csv, and create array with column names as array properties
function processCsv($absolutePath)
{
    $csv = array_map('str_getcsv', file($absolutePath));
    $headers = $csv[0];
    unset($csv[0]);
    $rowsWithKeys = [];
    foreach ($csv as $row) {
        $newRow = [];
        foreach ($headers as $k => $key) {
            $newRow[$key] = $row[$k];
        }
        $rowsWithKeys[] = $newRow;
    }
    return $rowsWithKeys;
}

// http://stackoverflow.com/a/2910637/3271665
function date_compare($a, $b)
{
    $t1 = strtotime($a['event_start']);
    $t2 = strtotime($b['event_start']);
    return $t1 - $t2;
}    

function schedToBooklet(){
	
	// Read in and store the events
	$formData = processCsv('sched final.csv');

	// See what is there
	//print_r($formData);
	
	// Get the number read in
	$numEvents = count($formData);
	
	// Sort the events based on start time
	usort($formData, 'date_compare');
	
	
	// Give the csv a header row
	echo "\"eventName\",\"eventVenu\", \"eventStartEndTimes\",\"moderator\",\"day\"\n";
	
	// Loop through each event, print out a description
	for($i=0; $i<$numEvents; $i++){
		
		// Skip events without names
		if($formData[$i]["name"] != "")		
			echo printEvent($formData, $i);
		
	}
	
}


$isFridayStart = false;
$isSaturdayStart = false;
$isSundayStart = false;

/**@fun printEvent($eventList, $i)
 *	^	This will print out the event details for the Nanocon Booklet
 *
 *	@param $eventList | 2D Array
 *		^	This is the list of events
 *
 *	@param $i | Integer
 *		^	This is the event that needs to be printed.
 *
 *	@return | String
 *		^	This is the formated event info
 *
 *	@auth NoremacSkich | 2014/09/18
 *
 */

 
function printEvent($eventList, $i){
	
	// Skip any disabled events
	if($eventList[$i]["active"] == "N")
		return "";
	
	$eventPrint = "";
	
	$eventName = $eventList[$i]["name"];
	$venue = $eventList[$i]["venue"];
	$startendtime = date("g:i a", strtotime($eventList[$i]["event_start"])) . " - " . date("g:i a", strtotime($eventList[$i]["event_end"]));
	$moderator = $eventList[$i]["moderators"];
	$day = date("l", strtotime($eventList[$i]["event_start"]));
	
	$eventPrint .= "\"" . $eventName . "\",\"" . $venue . "\",\"" . $startendtime . "\",\"" . $moderator . "\",\"" . $day . "\"";
	
	$eventPrint .= "\n";
	return $eventPrint;

}



//=====================
// MAIN STARTING POINT
//=====================

schedToBooklet();




?>
