<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Functions

function cleanTags($myTags) {
 $myTags = preg_replace( '/[^[:print:]]/', '', $myTags);
 $myTags = trim($myTags);
 $myTags = preg_replace('!\s+!', ' ', $myTags);
 $myTagArray = explode(" ", $myTags);
 $tagsDelimitedByCommas = implode(',', $myTagArray);
 return $tagsDelimitedByCommas;
}

function translateDateFormat($myOSQADateStyle) {
 //    E.g: 2011-04-13T12:05:45.393
 // Output: 2013-06-01 10:44:47
 
 $myDateParseArray = date_parse($myOSQADateStyle);
 
 $myYear       = $myDateParseArray['year'];
 $myYearPadded = sprintf('%02d', $myYear);
 
 $myMonth       = $myDateParseArray['month'];
 $myMonthPadded = sprintf('%02d', $myMonth);
 
 $myDay        = $myDateParseArray['day'];
 $myDayPadded  = sprintf('%02d', $myDay);
 
 $myHour       = $myDateParseArray['hour'];
 $myHourPadded = sprintf('%02d', $myHour);
 
 $myMinute       = $myDateParseArray['minute'];
 $myMinutePadded = sprintf('%02d', $myMinute);
 
 $mySecond       = $myDateParseArray['second']; 
 $mySecondPadded = sprintf('%02d', $mySecond); 
 
 $myQ2AStyleDateString = "$myYearPadded-$myMonthPadded-$myDayPadded $myHourPadded:$myMinutePadded:$mySecondPadded";
 return $myQ2AStyleDateString;
}

/*
 * import-osqa.php
 *
 * script to import OSQA xml database into Q2A
 *
 * Reads in .xml files, parses them, formats them and then inserts
 * the appropriate SQL scripts into the Q2A MySQL database.
 * 
 * @author James Stephen Spittal
 */

define('Q2A_MYSQL_HOST', '');
define('Q2A_MYSQL_DATABASE', '');
define('Q2A_MYSQL_USERNAME', '');
define('Q2A_MYSQL_PASSWORD', '');

define('POST_TYPE_ID_QUESTION', 1);
define('POST_TYPE_ID_ANSWER', 2);

// Defaults for qa_users
define('DEFAULT_IMPORT_IP_ADDRESS', '2091405205');
define('DEFAULT_PASSWORD', 'password');
define('DEFAULT_PASSWORD_HASH_PASSSALT', 'dasd3j7tsu0botim');
define('DEFAULT_PASSWORD_HASH_PASSCHECK', 'cecab612f5a24f8ad66b84a1abe841355dec43dc');
define('DEFAULT_USER_LEVEL', '0');
define('DEFAULT_LOGGEDIN_TIME', '2013-06-01 10:44:47');
define('DEFAULT_LOGINIP', '2091405205');
define('DEFAULT_WRITTEN', '2013-06-01 10:30:12');
define('DEFAULT_WRITEIP', '2091405205');
define('DEFAULT_EMAILCODE', 'vegigo9c');
define('DEFAULT_SESSIONCODE', 'n7apyod5');
define('DEFAULT_SESSIONSOURCE', NULL);
define('DEFAULT_FLAGS', 8);

// Defaults for qa_...

// Load the xml files
$xmlPosts = simplexml_load_file('Posts.xml');
$xmlUsers = simplexml_load_file('Users.xml');

/**
 ** Create all the users. 
 */

$number = 0;
$import_n_users = 1270;

for ($number = 0; $number < $import_n_users; $number++) {

 if (! isset($xmlUsers->row[$number]->Id))
  continue;

 $userId                      = $xmlUsers->row[$number]->Id;
 $userDisplayName             = $xmlUsers->row[$number]->DisplayName;
 $userLocation                = $xmlUsers->row[$number]->Location;
 $userWebsiteUrl              = $xmlUsers->row[$number]->WebsiteUrl;
 $userAboutMeWithHTML         = $xmlUsers->row[$number]->AboutMe;
 $userAboutMeWithoutHTML      = mysql_escape_string(strip_tags($userAboutMeWithHTML));
 $userEmail                   = $xmlUsers->row[$number]->Email;
 $userCreationDateOSQAStyle   = $xmlUsers->row[$number]->CreationDate;
 $userCreationDateQ2AStyle    = translateDateFormat("$userCreationDateOSQAStyle"); 
 $userLastAccessDateOSQAStyle = $xmlUsers->row[$number]->LastAccessDate;
 $userLastAccessDateQ2AStyle  = translateDateFormat("$userLastAccessDateOSQAStyle");
 $userLastLoginDateOSQAStyle  = $xmlUsers->row[$number]->LastLoginDate;
 $userLastLoginDateQ2AStyle   = translateDateFormat("$userLastLoginDateOSQAStyle");
 $userBirthday                = $xmlUsers->row[$number]->Birthday;
 $userReputation              = $xmlUsers->row[$number]->Reputation;
 $userBadgeSummary            = $xmlUsers->row[$number]->BadgeSummary;
 $userLastLoginIP             = $xmlUsers->row[$number]->LastLoginIP;
 $userLastLoginIPAsInteger    = ip2long($userLastLoginIP);
 
 if ($userId == -1)
  continue;
 
 // Notable ommissions: UserTypeId, OpenId, Views, HasReplies, HasMessage, OptInEmail, LastLoginIP
 
 //print("User ($userId,$userEmail,$userDisplayName,$userLocation,$userWebsiteUrl) \n");
 
 // SQL format for qa_users:
 //  userid       (maps to $userId)
 //  created      (maps to $userCreationDateQ2AStyle)
 //  createip     (maps to $userLastLoginIPAsInteger)
 //  email        (maps to $userEmail)
 //  handle       (maps to $userDisplayName)
 //  avatarblobid (maps to NULL)
 //  avatarwidth  (maps to NULL)
 //  avatarheight (maps to NULL)
 //  passsalt     (maps to DEFAULT_PASSWORD_HASH_PASSSALT)
 //  passcheck    (maps to DEFAULT_PASSWORD_HASH_PASSCHECK)
 //  level        - let's make it 0 for now and investigate later
 //  loggedin     (maps to $userLastLoginDateQ2AStyle)
 //  loginip      (maps to $userLastLoginIPAsInteger)
 //  written      - let's make it $userLastAccessDateQ2AStyle
 //  writeip      - let's make it $userLastLoginIPAsInteger
 //  emailcode    - vegigo9c
 //  sessioncode  - n7apyod5
 //  sessionsource- NULL
 //  flags        - 8
 
 $sql = "INSERT INTO qa_users VALUES ('$userId','$userCreationDateQ2AStyle','$userLastLoginIPAsInteger','$userEmail','$userDisplayName',NULL,NULL,NULL,'".DEFAULT_PASSWORD_HASH_PASSSALT."','" . DEFAULT_PASSWORD_HASH_PASSCHECK."','0','$userLastLoginDateQ2AStyle','$userLastLoginIPAsInteger','$userLastAccessDateQ2AStyle','$userLastLoginIPAsInteger','vegigo9c','n7apyod5',NULL,'8');";
 
 ////print $sql;
 
 // SQL format for qa_userprofile
 // Each user has 4 entries in qa_userprofile

 // Entry #1 (about)
 //  userid  (maps to $userId)
 //  title   (maps to 'about') 
 //  content (maps to $userAboutMeWithoutHTML)
 
 // Entry #2 (location)
 //  userid  (maps to $userId)
 //  title   (maps to 'location')
 //  content (maps to $userLocation)
 
 // Entry #3 (name)
 //  name    (maps to $userId)
 //  title   (maps to 'name')
 //  content (maps to $userDisplayName) 
 
 // Entry #4 (website)
 //  name    (maps to $userId)
 //  title   (maps to 'website')
 //  content (maps to $userWebsiteUrl)  
 
 $sql = "INSERT INTO qa_userprofile VALUES ('$userId', 'about', '$userAboutMeWithoutHTML');";
 ////print $sql;

 $sql = "INSERT INTO qa_userprofile VALUES ('$userId', 'location', '$userLocation');";
 ////print $sql;
 
 $sql = "INSERT INTO qa_userprofile VALUES ('$userId', 'name', '$userDisplayName');";
 ////print $sql;
 
 $sql = "INSERT INTO qa_userprofile VALUES ('$userId', 'website', '$userWebsiteUrl');";
 ////print $sql;
 
 
 // SQL format for qa_userpoints
 //  userid       (maps to $userId)
 //  points       (maps to $userReputation)
 //  qposts        0
 //  aposts        0
 //  cposts        0
 //  aselects      0
 //  aselecteds    0
 //  qupvotes      0
 //  qdownvotes    0
 //  aupvotes      0
 //  adownvotes    0
 //  qvoteds       0
 //  avoteds       0
 //  upvoteds      0
 //  downvoteds    0
 //  bonus         0
 
 $sql = "INSERT INTO qa_userpoints VALUES ('$userId', '$userReputation', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');";
 ////print $sql;
} 





// Create some variables for counting.
$number = 0; 
$n_questions = 0; $n_answers = 0;

$n_posts = 3998;

for ($number = 0; $number < $n_posts; $number++) {
	 if (isset($xmlPosts->row[$number]->PostTypeId)) {
		  $postTypeId = $xmlPosts->row[$number]->PostTypeId;

		  if ($postTypeId == POST_TYPE_ID_QUESTION) {
		   $postId                          = $xmlPosts->row[$number]->Id;
		   $postCreationDateOSQAStyle       = $xmlPosts->row[$number]->CreationDate;
		   $postCreationDateQ2AStyle        = translateDateFormat("$postCreationDateOSQAStyle");
		   $postScore                       = $xmlPosts->row[$number]->Score;
		   $postViewCount                   = $xmlPosts->row[$number]->ViewCount;
		   $postBodyWithHTMLTags            = $xmlPosts->row[$number]->Body;
		   $postBodyWithoutHTMLTags         = mysql_escape_string(strip_tags($postBodyWithHTMLTags));
		   $postOwnerUserId                 = $xmlPosts->row[$number]->OwnerUserId;
		   $postLastEditorUserId            = $xmlPosts->row[$number]->LastEditorUserId;
		   $postLastEditDateOSQAStyle       = $xmlPosts->row[$number]->LastEditDate;
		   $postLastEditDateQ2AStyle        = translateDateFormat("$postLastEditDateOSQAStyle");
		   $postLastActivityDateOSQAStyle   = $xmlPosts->row[$number]->LastActivityDate;
		   $postLastActivityDateQ2AStyle    = translateDateFormat("$postLastActivityDateOSQAStyle");
		   $postLastActivityUserId          = $xmlPosts->row[$number]->LastActivityUserId;
		   $postAcceptedAnswerId            = $xmlPosts->row[$number]->AcceptedAnswerId;
		   $postTitle                       = mysql_escape_string($xmlPosts->row[$number]->Title);
		   $postTagsMessyFormatting         = $xmlPosts->row[$number]->Tags;
		   $postTagsSeperatedByCommas       = cleanTags($postTagsMessyFormatting);
		   $postAnswerCount                 = $xmlPosts->row[$number]->AnswerCount;
		   $postCommentCount                = $xmlPosts->row[$number]->CommentCount;
		   $postFavoriteCount               = $xmlPosts->row[$number]->FavoriteCount;
		   $postLastOwnerEmailDateOSQAStyle = $xmlPosts->row[$number]->LastOwnerEmailDate;
		   $postLastOwnerEmailDateQ2AStyle  = translateDateFormat("$postLastOwnerEmailDateOSQAStyle");
		   
		   $sql = "INSERT INTO qa_posts VALUES ('$postId', 'Q', NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '$postOwnerUserId', NULL, '2091405205', '$postOwnerUserId', '2091405205', '0', '0', '$postViewCount', NULL, '0', NULL, '0', '', '$postCreationDateQ2AStyle', NULL, NULL, '$postTitle', '$postBodyWithoutHTMLTags', '$postTagsSeperatedByCommas', NULL);";
		   
		   print $sql;
		   		  
		   //print("INSERT INTO qa_posts VALUES (X, Q, NULL, NULL, ..., ..., $title, $content, );");
		  
		   // Other elements of POST_TYPE_ID_QUESTION are:
		   // <OwnerUserId>n</OwnerUserId>
		   
		   $n_questions++;
		  }
		  elseif ($postTypeId == POST_TYPE_ID_ANSWER) {
		   $postId                          = $xmlPosts->row[$number]->Id;
		   $postCreationDateOSQAStyle       = $xmlPosts->row[$number]->CreationDate;
		   $postCreationDateQ2AStyle        = translateDateFormat("$postCreationDateOSQAStyle");		  
		   $postScore                       = $xmlPosts->row[$number]->Score;
		   $postViewCount                   = $xmlPosts->row[$number]->ViewCount;
		   $postBodyWithHTMLTags            = $xmlPosts->row[$number]->Body;
		   $postBodyWithoutHTMLTags         = mysql_escape_string(strip_tags($postBodyWithHTMLTags));		   
		   $postOwnerUserId                 = $xmlPosts->row[$number]->OwnerUserId;
		   $postLastEditorUserId            = $xmlPosts->row[$number]->LastEditorUserId;
		   $postLastEditDateOSQAStyle       = $xmlPosts->row[$number]->LastEditDate;
		   $postLastEditDateQ2AStyle        = translateDateFormat("$postLastEditDateOSQAStyle");
		   $postLastActivityDateOSQAStyle   = $xmlPosts->row[$number]->LastActivityDate;
		   $postLastActivityDateQ2AStyle    = translateDateFormat("$postLastActivityDateOSQAStyle");
		   $postLastActivityUserId          = $xmlPosts->row[$number]->LastActivityUserId;
		   $postParentId                    = $xmlPosts->row[$number]->ParentId;
		   $postCommentCount			    = $xmlPosts->row[$number]->CommentCount;
		   
		   // Todo: fiddle with this so that updates work.
		   
		   $sql = "INSERT INTO qa_posts VALUES ('$postId', 'A', '$postParentId', NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '$postOwnerUserId', NULL, '2091405205', NULL, NULL, '0', '0', '0', NULL, '$postViewCount', NULL, '0', '', '$postCreationDateQ2AStyle', NULL, NULL, NULL, '$postBodyWithoutHTMLTags', NULL, NULL);";
		   
		   print $sql;
		  
		   $n_answers++;
		  }
	}}

//print("Total number of questions: $n_questions\n<br/>");
//print("Total number of answers: $n_answers\n<br/>");

$total_posts = $n_questions + $n_answers;

//print("Total number of posts: $total_posts\n<br/>");

$n_deleted_posts = ($number + 1) - $total_posts;

//print("Total number of deleted posts: $n_deleted_posts\n<br/>");

?>
