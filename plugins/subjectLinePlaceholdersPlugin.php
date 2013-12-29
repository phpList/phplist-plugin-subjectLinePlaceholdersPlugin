<?php

/**
 * subjectLinePlaceholders plugin version 1.0a1
 * 
 * This plugin allows placeholders to be used in the subject line of a message which
 * will be replaced by the value of the corresponding subscriber attribute in the 
 * message being sent to that subscriber.
 *
 * The placeholder is the attribute name in uppercase and enclosed in square brackets. If
 * the replacement is to be in uppercase, there must be an '!' immediately before the
 * closing bracket.
 *
 * Thus if subscribers have attributes named 'City' and 'State' and the state is to be
 * in upper case, a subject line might read:
 *
 *            A message for subscribers in [CITY], [STATE!]
 *
 * For a subscriber in Dallas, Texas, this would result in the subject line:
 *
 *			  A message for subscribers in Dallas, TEXAS
 *
 */

/**
 * Registers the plugin with phplist
 * 
 * @category  phplist
 * @package   subjectLinePlaceholdersPlugin
 */

class subjectLinePlaceholdersPlugin extends phplistPlugin
{
    /*
     *  Inherited variables
     */
    public $name = 'Subject Line Placeholders Plugin';
    public $version = '1.0a1';
    public $enabled = true;
    public $authors = 'Arnold Lesikar';
    public $description = 'Allows the use of placeholders for user attributes in the subject line of messages';
    private $attnames = array ();  // All the names of user attributes
    private $capnames = array ();  // All the user attributes in upper case
    private $holders = array ();  // An array keyed on placeholders (without the brackets)
    							  // giving the replacement value and other info for each placeholder
    private $havesome = FALSE; 	// Flag that we have some placeholders in the subject line
  private $fn = '/home/suncityd/public_html/phplist/admin/aquery.txt';
  private $fnflag = FILE_APPEND;
        	
	public function __construct()
    {

        $this->coderoot = dirname(__FILE__) . '/subjectLinePlaceholdersPlugin/';
           		
    	parent::__construct();
    }
    
/*
   * campaignStarted
   * called when sending of a campaign starts
   * @param array messagedata - associative array with all data for campaign
   * @return null
   * 
   * We find out here what placeholders we have in the subject line and 
   * which will require upper case
   *
   */
   public function campaignStarted($messagedata = array()) 
   {
   
   		// Collect the placeholders in the subject line; quit if none there.
   		// It is only at the start of the campaign that we have access to the subject line.
   		$this->holders = array();
  		$subject = $messagedata['subject'];
  		
  		// Find the placeholders with regex. Some may end with '!' to indicate
  		// the replacement string is capitalized.
  		if (!preg_match_all('/\[(!?)([^\[\]]*)\]/', $subject, $matches))
  			$this->havesome = FALSE; // Flag no placeholders 
  		
  		$this->havesome = TRUE;
  		$raw = $matches[0];  // Placeholders with brackets
  		$caps = $matches[1]; // Exclamation marks or nothing
  		$hldr = $matches[2]; // Placeholders without brackets
  		$len = count($raw); 		 
  		
  		// Get all the user-attribute names for comparison with placeholders
   		$this->attnames = array();
    	$att_table = $GLOBALS['tables']["attribute"];
    	$res = Sql_Query(sprintf('SELECT Name FROM %s', $att_table));
    	while ($row = Sql_Fetch_Row($res))
    		$this->attnames[] = $row[0];
    		
    	// Need array of upper case attribute names to compare with upper case
    	// placeholders.
    	foreach ($this->attnames as $aname) 
    		$this->capnames[] = strtoupper($aname);
    
		// Now which placeholders really correspond to attributes?
  		for ($i=0; $i < $len; $i++) 
  		{
  			// Does this supposed placeholder really correspond to a user attribute?
  			$key = array_search($hldr[$i], $this->capnames); 
  			if ($key === FALSE)
  				continue;
  				
  			//Save the corresponding attribute name
  			$this->holders[$raw[$i]]['attname'] = $this->attnames[$key];
  			
  			// Check for '!', meaning the replacement should be put in upper case
 			if (isset($caps[$i]) && ($caps[$i] == '!'))
  				$this->holders[$raw[$i]]['capflag'] = TRUE;
  			else
  				$this->holders[$raw[$i]]['capflag'] = FALSE;	
  		}
    		
  		if (!count($this->holders))
  			$this->havesome = FALSE; // Flag no placeholders
   }	
   
    /* canSend -- The original purpose of this function is:
   *
   * can this message be sent to this subscriber
   * if false is returned, the message will be identified as sent to the subscriber
   * and never tried again
   * 
   * @param $messagedata array of all message data
   * @param $userdata array of all user data
   * returns bool: true, send it, false don't send it
   *
   * Instead of this we are using this function to find the placeholder replacement values
   * for the particular user receiving the message
   *
 */

  function canSend ($messagedata, $subscriberdata) 
  {  
  	if (!$this->havesome)
  		return true;
  		
  	// Get the replacement values for this subscriber	
  	$id = $subscriberdata['id'];
  	$ouratts = getUserAttributeValues('', $id);	
  	foreach ($this->holders as $key => &$val)
  		$val['replace'] = $ouratts[$val['attname']];
  	
    return true; //@@@
  }

    
  /* messageHeaders  -- The original purpose of this function is:
   *
   * return headers for the message to be added, as "key => val"
   *
   * @param object $mail
   * @return array (headeritem => headervalue)
   *
   *
   * This is the last point at which we can reach into the queue processing and
   * modify the subject line.
   *
 */
  
  public function messageHeaders($mail)
  {
  	if (!$this->havesome)
  		return array();
  		
  	// $mail contains the message to go out. Do the replacements.
  	foreach ($this->holders as $key => $val) 
  	{
  		if ($val['capflag'])
  			$mail->Subject = str_replace ($key, strtoupper($val['replace']), $mail->Subject);
  		else
  			$mail->Subject = str_replace ($key, $val['replace'], $mail->Subject);
    }
    return array(); //@@@
  }

}
  