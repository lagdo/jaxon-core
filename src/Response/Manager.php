<?php

/**
 * Manager.php - Jaxon Response Manager
 *
 * This class stores and tracks the response that will be returned after processing a request.
 * The Response Manager represents a single point of contact for working with <Response> objects.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response;

class Manager
{
    use \Jaxon\Utils\ContainerTrait;

    /**
     * The current response object that will be sent back to the browser
     * once the request processing phase is complete
     *
     * @var \Jaxon\Response\Response
     */
    private $xResponse;
    
    /**
     * The debug messages
     *
     * @var array
     */
    private $aDebugMessages;
    
    private function __construct()
    {
        $this->xResponse = null;
        $this->aDebugMessages = array();
    }
    
    /**
     * Return the one and only instance of the jaxon response manager
     *
     * @return Manager
     */
    public static function getInstance()
    {
        static $xInstance = null;
        if(!$xInstance)
        {
            $xInstance = new Manager();
        }
        return $xInstance;
    }
    
    /**
     * Clear the current response
     *
     * A new response will need to be appended before the request processing is complete.
     *
     * @return void
     */
    public function clear()
    {
        $this->xResponse = null;
    }

    /**
     * Append one response object onto the end of another
     *
     * You cannot append a given response onto the end of a response of different type.
     * If no prior response has been appended, this response becomes the main response
     * object to which other response objects will be appended.
     *
     * @param Response        $xResponse            The response object to be appended
     *
     * @return void
     */
    public function append(Response $xResponse)
    {
        if(!$this->xResponse)
        {
            $this->xResponse = $xResponse;
        }
        else if(get_class($this->xResponse) == get_class($xResponse))
        {
            if($this->xResponse != $xResponse)
                $this->xResponse->appendResponse($xResponse);
        }
        else
        {
            $this->debug($this->trans('errors.mismatch.types', array('class' => get_class($xResponse))));
        }
    }
    
    /**
     * Appends a debug message on the end of the debug message queue
     *
     * Debug messages will be sent to the client with the normal response
     * (if the response object supports the sending of debug messages, see: <Response>)
     *
     * @param string        $sMessage            The debug message
     *
     * @return void
     */
    public function debug($sMessage)
    {
        $this->aDebugMessages[] = $sMessage;
    }
    
    /**
     * Prints the response object to the output stream, thus sending the response to the client
     *
     * @return void
     */
    public function send()
    {
        if(($this->xResponse))
        {
            foreach($this->aDebugMessages as $sMessage)
            {
                $this->xResponse->debug($sMessage);
            }
            $this->aDebugMessages = array();
            $this->xResponse->sendHeaders();
            $this->xResponse->printOutput();
        }
    }
}
