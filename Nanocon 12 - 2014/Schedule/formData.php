<?php

// This will keep track of the number of events that are read in from the file.
$numberEvents = 0;



/**@fun singularPlural($number, $singularForm, $pluralForm)
 *	^	This function will return the singular form or the plural form of a 
 *		word based on the quantity of the number.
 *
 *	@param $number | integer
 *		^	This is the number that will determine the singular or plural form
 *			of the word
 *
 *	@param $singularForm | string
 *		^	This is what is returned if there is only one instance
 *	
 *	@param $pluralForm | string
 *		^	This is what will be returned if there are multiple instances
 *	
 *	@return | string
 *		^	This will return either the singularForm variable or the pluralForm
 *			variable.
 *
 *	@auth NoremacSkich | 2014/09/13
 *
 */
function singularPlural($number, $singularForm, $pluralForm){
	
	if($number > 1){
		return $pluralForm;
	}else{
		return $singularForm;
	}
	
	
}


$row = [
	"eventTitle" => "",
	"eventStartDate" => "",
	"eventEndDate" => "",
	"eventDescription" => "",
	"eventVenue" => "",
	"eventID" => "",
	"eventVisibility" => "",
	"eventCapacity" => "",
	
	"primaryTag" => "",
	"secondaryTag" => "",
	
	"numTables" => "",
	"numSeats" => "",
	
	"sessionManager" => "",
	"sessionPhone" => "",
	"sessionEmail" => "",
	
	"moderatorList" => "",
	
	"hostOrg" => "",
	"editURL" => "",
];


	
	
/**@fun dayToDate(date)
 *	^	This function will take the text from the event date and convert it to
 *		the date.
 *
 *	@var date | string
 *		^	This is the date that looks like this: Friday, November 7th
 *
 *	@auth NoremacSkich | 2014/09/09
 *
 *	@return Boolean
 *		^	This will return the date for the day.
 *		N	This will return FALSE if it isn't a valid date.
 *
 *	@note NoremacSkich | 2014/09/09
 *		^	This isn't actually calculating anything, it's just doing string 
 *			compares and returning a string back.
 */
function dayToDate($date){
	if($date=="Friday, November 6th")
		return "11/6/2014";
	else if($date=="Saturday, November 7th")
		return "11/7/2014";
	else if($date=="Sunday, November 8th")
		return "11/8/2014";
	else
		return false;
}

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
	while (!feof($file_handle) ) {
		$line_of_text[] = fgetcsv($file_handle);
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
		fputcsv($handle, $line, $delimiter, $enclosure);
	}
	rewind($handle);
	while (!feof($handle)) {
		$contents .= fread($handle, 8192);
	}
	fclose($handle);
	return $contents;
}

/**@fun formalizeData()
 *	^	This will read in the form data and convert it into a formal data 
 *		structure
 *
 * @auth  NoremacSkich | 2014/09/09
 * @mod	NoremacSkich | 2014/09/13
 *
 * @return | Array
 *	^	This will return a 2 dimensional array that has all the event info on
 *		it.
 *
 */
function formalizeData(){

	// Read in and store the events
	$formData = readCSV('events.csv');
	
	
	// Get rid of last blank line array item.
	array_pop($formData);
	
	// See what imported
	//print_r($formData);
	
	// Check to see if array is set
	//echo "isset()\n";	
	//echo isset($formData);
	
	// Get the number of elements imported
	$numberEvents = count($formData);
	
	// Print out the number of events.
	//echo "Number Events : \n";
	//echo $numberEvents;
	
	// Sched.org uses 26 columns to store the event information.
	$m = 26;
	
	// The initial value for the arrays should be 0
	$value = '';
	
	// Now create multidimensional array to store the sched.org information
	$tmpEvents = array_fill(0, $numberEvents, "");
	
	// These are the relevent fields of data that we need.  For brevity purposes,
	// I will only note the needed fields here.
	// 0 = ID - i will be the session ID
	// 1 = Session Name
	// 3 = Start Date & Time MM/DD/YY HH:MM
	// 4 = End Date & Time MM/DD/YY HH:MM
	// 5 = Session Type
	// 6 = Session Sub-type
	// 8 = Session Description 
	// 10 = Moderators 
	// 15 = Venue 
	// 24 = Host Organization

	
	// This is the layout of response sheet:
	//
	// Layout of data[row][see below]
	// 0 = Timestamp
	// 1 = Name of Event
	// 2 = What day did you want the event?
	// 3 = When did you want to do this event?
	// 4 = How long is the event?
	// 5 = How many chairs do you need?
	// 6 = How many tables do you need?
	// 7 = Description
	// 8 = Event Tags
	// 9 = Name
	// 10 = Email
	// 11 = Phone Number
	// 12 = Moderators
	// 13 = Primary Event Tag
	// 14 = Host Organization
	// 15 = Edit URL
	
	// Set the default time zone
	date_default_timezone_set('UTC');
	// Don't have this set in my php file
	//date.timezone = "America/New_York";
	// In order to do this, we need to loop through all the rows in the spreadsheet
	for ($i = 0; $i < $numberEvents; $i++) {
		
		// First lets store the ID
		$tmpEvents[$i]['eventID'] = $i;
		
		// Then the name of the event
		$tmpEvents[$i]['eventTitle'] = $formData[$i][1];
		
		//==========
		// convert the date from the form into a timestamp
		$date = dayToDate($formData[$i][2]);
		
		// See what it looks like.
		//echo "Date: " .  $date  . "\n";
		
		// Check to make sure it is valid
		if(!$date){
              // If it wasn't, then continue to the next element
			  continue;
		}
		
		// Store the date
		$startDate = date("m/d/y", strtotime($date));
		
		// Next we need to get the starting time for the event.
		$startTime = strtotime($formData[$i][3], date("m/d/y"));
		
		// This is the number of hours into the day.
		$startTime = gmdate("H:i", $startTime);
		
		// Print out the start time and date.
		//echo "Start Time: '" . date('m/d/y H:i', strtotime("$startDate $startTime")) . "'\n";
		
		// Store the start time and date as unix time
		$startDateTime = date('U', strtotime("$startDate $startTime"));
		
		// Print out the number of seconds
		//echo 'Start Unix Time: \'' . $startDateTime . "'\n";
		
		// Next we need to combine the two
		// This is storing the date as 09/10/2014 19:48
		$tmpEvents[$i]['eventStartDate'] = date('m/d/Y H:i', strtotime("$startDate $startTime"));
		
		// Get the length of the event.
		$length = date('U', strtotime($formData[$i][4], date("m/d/y")));
		
		// Print out the length of the event
		//echo "Unix Event Length: '" . $length . "'\n";
		
		// Add the two times together
		$endTime = $length + $startDateTime;
		
		// Format and store the End Time date
		$endTimeFinal = date('m/d/y H:i', $endTime);
		
		// This line is for debugging the end time output
		//echo 'End Time: \'' . $endTimeFinal . "'\n\n";
		
		// Now store the end time into the tmp array
		$tmpEvents[$i]['eventEndDate'] = $endTimeFinal;
		
		// The venue is to be decided by the Schedule Officer.
		$tmpEvents[$i]['eventVenue'] = '';
		
		// Next we need the primary  event tag
		$tmpEvents[$i]['primaryTag'] = $formData[$i][13];
		
		// Then any other event tags
		$tmpEvents[$i]['secondaryTag'] = $formData[$i][8];
		
		// Store the number of tables needed
		$tmpEvents[$i]['numTables'] = $formData[$i][6];
		
		// Store the number of seats needed
		$tmpEvents[$i]['numSeats'] = $formData[$i][5];
		
		// With that, we want the list of moderators		
		$tmpEvents[$i]['moderatorList'] = $formData[$i][12];
		
		// Then the event organizer
		$tmpEvents[$i]['managerName'] = $formData[$i][9];
		
		// Store the session manager phone number
		$tmpEvents[$i]['managerPhone'] = $formData[$i][11];
		
		// Store the session manager email
		$tmpEvents[$i]['managerEmail'] = $formData[$i][10];	

		// Finally the Host organization
		$tmpEvents[$i]['hostOrg'] = $formData[$i][14];
		
		// We also would like to store the editing url
		$tmpEvents[$i]['editURL'] = $formData[$i][15];
		
		// Store the date that form was submitted.
		$tmpEvents[$i]['dateSubmitted'] = $formData[$i][0];
				
		// Now we want the description of the event
		$tmpEvents[$i]['eventDescription'] = $formData[$i][7];
		
		
	}
	
	// Dump the array out to command line.
	//print_r($tmpEvents);
	
	// Drop the first element off
	array_shift($tmpEvents);
	
	//print_r($tmpEvents);
	//echo "tmpEvents Done \n\n\n";
	
	return $tmpEvents;
}

/**@fun printReport($eventList, $eventID)
 *	^	This will return the email that will be sent to the Session Manager.
 *	
 *	@param $eventList | Array
 *		^	This is the list of all the events and their attributes
 *
 *	@param $eventID | Integer
 *		^	This is the ID of the event that you want printed.
 *
 *	@auth 2014/09/13
 */
function printReport($eventList, $eventID){
	// Title
	// Date
	// Start time
	// End time
	// Session manager
	// # tables
	// # seats
	// Host Organization
	// Primary Tags
	// Secondary Tags
	// List of moderators
	// Edit event URL
	// Event Description
	// Manager phone number
	// Manager email
	
	$data = $eventList[$eventID];
	
	
	// This is the string that will keep track of the report.
	$report = "";
	
	// State the title of the event.
	$report .= $data['eventTitle'] . "\n";
	
	// State the date of event and the start and end times
	$report .= date('D M d',strtotime($data['eventStartDate'])) . " from " . date('h:i a',strtotime($data['eventStartDate'])) . " to " . date('h:i a',strtotime($data['eventEndDate'])) . ".\n\n";
	
	// Start of the message body
	$report .= "I, " . $data['managerName'] . ", have requested to host the above stated event";
	
	// If they are sponsering an organization, state it here.
	if($hostOrg != ""){
		$report .= " on behalf of " . $data['hostOrg'] . ".  ";
	}else{
		$report .= ".  ";
	}
	
	$report .= "I would like to request " . $data['numTables'] . " " . singularPlural($data['numTables'], "table", "tables");
	
	$report .= " with " . $data['numSeats'] . " " . singularPlural($data['numSeats'], "seat", "seats") . ".  The primary focus of my event is " . $data['primaryTag'] . ".  ";
	
	// If there are secondary tags, then state them here.
	if($data['secondaryTag'] != ""){
		$report .= "The following are additional tags that I wish to associate with my event: " . $data['secondaryTag'] . ". ";
	}
	
	// If they have a moderator list, state so here.
	if($moderatorList != ""){
		$report .= "\n\n I assume the responsibility for making sure that the following people are hosting this event:  " . $data['moderatorList'] . ".  ";
	}
	
	// New paragraph
	$report .= "\n\n";
	
	
	// State the editing link
	$report .= "I acknowledge that we are able to change the event information at " . $data['editURL'] . ", and that I will not receive";
	$report .= " any notice of change in the event details beyond this statement.";
	
	// New Paragraph
	$report .= "\n\n";
	
	// State the description that they gave
	$report .= "This is the description that I wish to have for my event:" . "\n";
	$report .= $data['eventDescription'];
	
	// New Paragraph
	$report .= "\n\n";
	
	// State the contact information
	$report .= "You can contact me by phone (" . $data['managerPhone'] . ") or email me at <" . $data['managerEmail'] . ">.";  
	
	// New Paragraph
	$report .= "\n\n";
	
	// State how they can prove that they have read and approve the details
	$report .= "As proof of agreeing to these details, I will reply to this email with a picture of a cat.\n";
	
	echo $report;

	return $report;
}

/**@fun schedEventImport($eventList)
 *	^	This will convert the events into a format acceptable for an sched event 
 *		import.
 *
 *	@param $eventList | Array
 *		^	This is the 2D array that contains the event information
 *
 *	@auth NoremacSkich | 2014/09/15
 *
 *	@return | 2D Array
 *		^	Will return a 2D array of the events.  Each row represents 1 event.
 *
 */
function schedEventImport($eventList){
	
	// Get the number of elements imported
	$numberEvents = count($eventList);
	
	
	// Create a tempory event list
	$tmpEvents = array_fill(0, $numberEvents, "");
	
	
	for($i=0; $i<$numberEvents; $i++){
	
		// The event ID
		$tmpEvents[$i][0] = $eventList[$i]["id"];
		
		// The event Title
		$tmpEvents[$i][1] = $eventList[$i]["eventTitle"];
		
		// Is event listed on site?
		$tmpEvents[$i][2] = "N";

		// Event Start date and time
		$tmpEvents[$i][3] = $eventList[$i]["eventStartDate"];
		
		// Event End date and time
		$tmpEvents[$i][4] = $eventList[$i]["eventEndDate"];
		
		// Session Type
		$tmpEvents[$i][5] = $eventList[$i]["primaryTag"];
		
		// Session Sub Type
		$tmpEvents[$i][6] = $eventList[$i]["secondaryTag"];
		
		// Seat Reservation - None this year
		$tmpEvents[$i][7] = "";
		
		// Event Description
		$tmpEvents[$i][8] = $eventList[$i]["eventDescription"];

		$tmpEvents[$i][9] = "";
		$tmpEvents[$i][10] = "";

		// For now, the list of moderators will allways include the manager.
		$tmpEvents[$i][11] = $eventList[$i]["managerName"];
		
		// Now, if there is a list List of Moderators
		
		$tmpEvents[$i][12] = "";
		$tmpEvents[$i][13] = "";
		$tmpEvents[$i][14] = "";

		// Position event is taking place at
		$tmpEvents[$i][15] = "General Gaming Area";
		
		$tmpEvents[$i][17] = "";
		$tmpEvents[$i][18] = "";
		$tmpEvents[$i][19] = "";
		$tmpEvents[$i][20] = "";
		$tmpEvents[$i][21] = "";
		$tmpEvents[$i][22] = "";
		$tmpEvents[$i][23] = "";

		// Host Organization
		$tmpEvents[$i][24] = $eventList[$i]['hostOrg'];
		
		// Custom Button Link
		$tmpEvents[$i][25] = "";
		
	}
	
	//print_r($tmpEvents);
	
	return $tmpEvents;
	
}
 
/**@fun schedPeopleImport($eventList)
 *	^	This will generate the list of people that need to have profiles on 
 *		sched.org
 *
 *	@param $eventList | 2D Array
 *		^	This is the array list.  Each row represents 1 event.
 *
 *	@auth NoremacSkich | 2014/09/15
 *
 *	@return | 2D Array
 *		^	This will return a 2D array that has list of people for event.
 */
function schedPeopleImport($eventList){

	// 0 NAME (Required)
	// 1 EMAIL ADDRESS (Optional)
	// 2 PASSWORD (Optional)
	// 3 COMPANY (Optional)
	// 4 POSITION (Optional)
	// 5 LOCATION (Optional)
	// 6 BIO/DESCRIPTION (Optional)
	// 7 RELATED WEBSITE (Optional)
	// 8 IMAGE (Optional)


}

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
	
	// <strong>Camp Wicakini – Part 1 (Æther)</strong>
	$eventPrint = $eventList[$i]["eventTitle"] . "\n";
	
	//	Host: Allen Thiele			Time: 6pm-10pm
	$eventPrint .= "\tHost: " . $eventList[$i]["managerName"] . "\tTime: " . date("g:i a", strtotime($eventList[$i]["eventStartDate"])) . " " . date("g:i a", strtotime($eventList[$i]["eventEndDate"])) . "\n";
	
	//	Number of Players: 3-6 (- if none)
	$eventPrint .= "\tNumber of Players: " . $eventList[$i]["numSeats"] . "\n";
	
	//Description: Camp Wicakini, a beautiful summer camp, is nestled next to Lake Wicakini, or Lake of the Resurrection. The teenage camp counselors are helping run things and keeping the maggots... er... campers in line when something goes wrong... terribly, horribly wrong.  
	$eventPrint .= "Description:\n" . $eventList[$i]["eventDescription"];
	
	return $eventPrint;

}


// Generate and formailize the data structure
$events = formalizeData();

//print_r($events);


// Export the data that we have generated to the data.csv
file_put_contents ( 'data.csv', generateCsv($events));

//$eventSend = printReport($events, 5);

// Convert the data into a sched event form.
$sched = schedEventImport($events);

// and export the data into a csv
file_put_contents ( 'sched.csv', generateCsv($sched));

$eventDescription = printEvent($events, 3);

echo $eventDescription . "\n";


	// Session Import Excel Sheet
	//
	// 0 = ID
	// X 1 = Session Name
	// 2 = Visible On Site
	// X 3 = Start Date & Time
	// X 4 = End Date & Time
	// X 5 = Session Type
	// X 6 = Session Sub-type
	// 7 = Capacity
	// X 8 = Session Description
	// 9 = Speakers
	// X 10 = Moderators
	// 11 = Artists
	// 12 = Sponsors
	// 13 = Vendors
	// 14 = Volunteers
	// X 15 = Venue
	// 16 = Physical Address
	// 17 = Link To Image File
	// 18 = Link To MP3 File
	// 19 = Custom Filter 1
	// 20 = Custom Filter 2
	// 21 = Custom Filter 3
	// 22 = Custom Filter 4
	// 23 = Tags
	// X 24 = Host Organization
	// 25 = Custom Button Link

	// Attendee Import Excel Sheet
	//
	// 0 NAME (Required)
	// 1 EMAIL ADDRESS (Optional)
	// 2 PASSWORD (Optional)
	// 3 COMPANY (Optional)
	// 4 POSITION (Optional)
	// 5 LOCATION (Optional)
	// 6 BIO/DESCRIPTION (Optional)
	// 7 RELATED WEBSITE (Optional)
	// 8 IMAGE (Optional)




?>
