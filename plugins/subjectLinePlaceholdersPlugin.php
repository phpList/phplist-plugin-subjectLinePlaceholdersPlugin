<?php

/**
 * subjectLinePlaceholders plugin version 1.0a1
 * 
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
   * We create the list name prefix here.
   *
   */
	public function campaignStarted(&$messagedata = NULL) 
  {
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
  	$mail->Subject = $this->curpfx . $mail->Subject;  // Add the prefix
  	
    return array(); //@@@
  }
}
  