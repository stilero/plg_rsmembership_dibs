<?php
/**
 * Plugin_RSMembership_DIBS
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Plugin_RSMembership_DIBS
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-feb-23 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 * 
 * Test Param:
 localhost/joomla_svn/index.php?orderid=1361725491&paytype=VISA%28SE%29&accepturl=http%3A%2F%2Fwww%2Ephp%2Dprogrammering%2Ese%2Findex%2Ephp%3Foption%3Dcom%5Frsmembership%26task%3Dthankyou%26dibspayment%3D1&acquirerlang=sv&agreement=5396496&amount=10000&callbackurl=http%3A%2F%2Fwww%2Ephp%2Dprogrammering%2Ese%2Findex%2Ephp%3Foption%3Dcom%5Frsmembership%26dibspayment%3D1&cancelurl=https%3A%2F%2Fpayment%2Earchitrade%2Ecom%2Fpaymentweb%2Freply%2Eaction&currency=752&declineurl=https%3A%2F%2Fpayment%2Earchitrade%2Ecom%2Fpaymentweb%2Freply%2Eaction&delivery1.Email=entimme%40stilero%2Ecom&delivery2.Name=Daniel%20Eliasson&delivery3.Address=Tranb%E4rsv%E4gen%2052%20%2C44837%20Floda&dibsmd5=e6f5678883161a19c8bac84bfa3a7b4d&flexwin_cardlogosize=1&fullreply=1&ip=81%2E235%2E197%2E91&lang=sv&merchant=90150391&newDIBSTransactionID=695336505&newDIBSTransactionIDVerification=78fe18ba43e72385259e88ba80974f918f3aa5b5f4dc8382dd91292423dfaf3a&ordline0-1=description&ordline0-2=price&ordline1-1=%C5rsmedlemskap&ordline1-2=100&posty! pe=ssl&rscurrency=SEK&test=yes&textreply=1&uniqueoid=7f05b0e0a6998fa0739c671b612164db&option=com%5Frsmembership&dibspayment=1&approvalcode=123456&statuscode=2&transact=695336505&authkey=6fca43e4bdf9c80b0530b7e3aa726f9f
 */

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php'))
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php');

class plgSystemRSMembershipDibs extends JPlugin{
	
    var $_db;
    const TAXTYPE_PERCENT = 0;
    const TAXTYPE_AMOUNT = 1;
    const PAYMENT_MODE_TEST = 0;
    const PAYMENT_MODE_LIVE = 1;
    const MESSAGE_TYPE_STANDARD = 0;
    const MESSAGE_TYPE_MEMBERSHIPNAME = 1;
    const PAYMENT_URL_LIVE = 'https://payment.architrade.com/paymentweb/start.action';
    const IS_DEBUGGING_RSDIBS = TRUE;
    protected $inputs = array();
    
    /**
     * The instantiation method of the class for DIBS payment
     * @param type $subject
     * @param type $config
     * @return none
     */
    function plgSystemRSMembershipDibs(&$subject, $config){
        parent::__construct($subject, $config);
        $this->_plugin =& JPluginHelper::getPlugin('system', 'rsmembershipdibs');
        jimport('joomla.html.parameter');
        $this->_params = new JParameter($this->_plugin->params);
        if (!$this->canRun()){
            return;
        }
        RSMembership::addPlugin('Dibs', 'rsmembershipdibs');
        $this->_db = JFactory::getDBO();
    }
    /**
     * Checks if nececary classes exists so that the payment can be run.
     * @return bool true if it exists.
     */
    public function canRun(){
        return file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php');
    }
    
    /**
     * Returns a RS membership object based on its ID
     * @param int $id The membership id
     * @return object A RS Membership object
     */
    protected function getMembershipWithId($id){
        $db =& JFactory::getDBO();
        $query =& $db->getQuery(TRUE);
        $query->select('*');
        $query->from($db->nameQuote('#__rsmembership_memberships'));
        $query->where('id = '.(int)$id);
        $db->setQuery($query);
        $membership = $db->loadObject();
        return $membership;
    }
    
    /**
     * Summarizes all the extra fees
     * @param array $extra
     * @return int The summary
     */
    protected function extraTotal($extra){
        $extra_total = 0;
        foreach ($extra as $row){
            $extra_total += $row->price;
        }
        return $extra_total;
    }
    
    /**
     * Creates and returns a HTML string with the order details
     * @param object $transaction A RS transaction object
     * @param object $membership A RS membership object
     * @return string HTML containing the inputs for the order details.
     */
    protected function orderDetails($transaction, $membership){
        $userData = unserialize($transaction->user_data);
        $email = $transaction->user_email;
        $name = $userData->name;
        $adress = $userData->fields['address'];
        $city = $userData->fields['city'];
        $zip = $userData->fields['zip'];
        $fullAdress = $adress.' ,'.$zip.' '.$city;
        $price = $transaction->price;
        //$currency = $transaction->currency;
        $membershipName = $membership->name;
        //$membershipSku = $membership->sku == '' ? 'medl' : $membership->sku;
        $html = '<input type="hidden" name="delivery1.Email" value="'.htmlentities($email, ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="delivery2.Name" value="'.htmlentities($name, ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="delivery3.Address" value="'.htmlentities($fullAdress, ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="ordline0-1" value="description" />';
        $html .= '<input type="hidden" name="ordline0-2" value="price" />';
        $html .= '<input type="hidden" name="ordline1-1" value="'.htmlentities($membershipName, ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="ordline1-2" value="'.htmlentities($price, ENT_COMPAT, 'UTF-8').'" />';
        return $html;
    }
    
    /**
     * Method is run when a user starts a membership on the site.
     * Creates the payment variables and redirects to DIBS
     * @param object $plugin the plugin object
     * @param object $data A data object
     * @param object $extra Contains all the extras
     * @param object $membership RS membership object
     * @param object $transaction RS transaction object
     * @return boolean|string
     */
    public function onMembershipPayment($plugin, $data, $extra, $membership, $transaction){
        if (!$this->canRun()){
            print 'cannotrun';
            return;
        }
        if ($plugin != $this->_plugin->name){
            return false;
        }
        $this->loadLanguage('plg_system_rsmembership', JPATH_ADMINISTRATOR);
        $this->loadLanguage('plg_system_rsmembershipdibs', JPATH_ADMINISTRATOR);
        $transaction->price += $this->_getTax($transaction->price);
        $extra_total = $this->extraTotal($extra);
        $db_membership = $this->getMembershipWithId($membership->id);
        $transaction->custom = md5($transaction->params.' '.time());
        $url = self::PAYMENT_URL_LIVE;
        $orderId = time();
        $html = '';
        $html .= '<p>'.JText::_('RSM_PLEASE_WAIT_REDIRECT').'</p>';
        $html .= '<form method="post" action="'.$url.'" id="dibsForm">';
        $html .= '<input type="hidden" name="acquirerlang" value="sv" />';
        $html .= '<input type="hidden" name="merchant" value="'.htmlentities($this->_params->get('merchant'), ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="orderid" value="'.$orderId.'" />';
        $html .= '<input type="hidden" name="uniqueoid" value="'.htmlentities($transaction->custom).'" />';
        $html .= '<input type="hidden" name="lang" value="sv" />';
        if ($this->_params->get('mode') == self::PAYMENT_MODE_TEST){
            $html .= '<input type="hidden" name="test" value="yes" />';
        }
        $html .= $this->orderDetails($transaction, $membership);
        $html .= '<input type="hidden" name="rscurrency" value="'.htmlentities(RSMembershipHelper::getConfig('currency'), ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="currency" value="752" />';
        $html .= '<input type="hidden" name="amount" value="'.$this->_convertNumberForDIBS($transaction->price).'" />';
        if ($db_membership->activation == 1){
            $html .= '<input type="hidden" name="callbackurl" value="'.JRoute::_(JURI::root().'index.php?option=com_rsmembership&dibspayment=1').'" />';
        }elseif ($db_membership->activation == 2){
            $transaction->status = 'completed';
        }
        $html .= '<input type="hidden" name="accepturl" value="'.JRoute::_(JURI::root().'index.php?option=com_rsmembership&task=thankyou&dibspayment=1').'" />';
        $cancel = $this->_params->get('cancel_return');
        if ($cancel != '' ){
            $replace = array('{live_site}', '{membership_id}');
            $with = array(JURI::root(), $membership->id);
            $cancel = str_replace($replace, $with, $cancel);
            $html .= '<input type="hidden" name="cancelurl" value="'.$cancel.'" />';
        }
        $html .= '</form>';
        $html .= '<script type="text/javascript">';
        $html .= 'function dibsFormSubmit() { window.setTimeout(function() { document.getElementById(\'dibsForm\').submit() }, 5500); }';
        $html .= 'try { window.addEventListener ? window.addEventListener("load",dibsFormSubmit,false) : window.attachEvent("onload",dibsFormSubmit); }';
        $html .= 'catch (err) { dibsFormSubmit(); }';
        $html .= '</script>';
        return $html;
    }
    /**
     * Calculates and returns tax value
     * @param float $price the price
     * @return float The tax amount
     */
    protected function _getTax($price){
        $tax_value = $this->_params->get('tax_value');
        if (!empty($tax_value)){
            $tax_type = $this->_params->get('tax_type');
            // percent ?
            if ($tax_type == self::TAXTYPE_PERCENT){
                $tax_value = $price * ($tax_value / 100);
            }
        }
        return $tax_value;
    }
    
    /**
     * Responsible for capturing the callback when it's detected
     * @global type $mainframe
     * @return none
     */
    //public function onAfterRender(){
    public function onAfterInitialise(){
        //global $mainframe;
        $app =& JFactory::getApplication();
        if($app->getName() != 'site') return;
        $dibsPayment = JRequest::getVar('dibspayment');
        if ($dibsPayment == '1'){
            $this->onPaymentNotification();
        }
    }
    
    /**
     * Returns the plugin limitations for display in the admin settings
     * @return string The limitations
     */
    public function getLimitations(){
        $this->loadLanguage('plg_system_rsmembershipdibs');
        return JText::_('RSM_DIBS_LIMITATIONS');
    }
	
    /**
     * Check if the provided MD5 matches the calculated MD5 to catch forgery
     * 
     * @param int $transId Transaction ID from DIBS
     * @param int $amount The amount of the transaction
     * @param int $currency The currency code for the transaction
     * @param string $authkey The MD5 received from DIBS
     * @return bool True on success, false on fail
     */
    private function isMd5Valid($transId, $amount, $currency, $authkey){
        $md5key = $this->calcMd5($transId, $amount, $currency);
        if($authkey == $md5key){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Calculate an MD5 key
     * 
     * @param int $transId Transaction ID from DIBS
     * @param int $amount The amount of the transaction
     * @param int $currency The currency code for the transaction
     * @param string $authkey The MD5 received from DIBS
     * @return bool True on success, false on fail
     */
    private function calcMd5($transId, $amount, $currency){
        $key1 = $this->_params->get('md5key1');
        $key2 = $this->_params->get('md5key2');
        $md5key = md5($key2.md5($key1.'transact='.$transId.'&amount='.$amount.'&currency='.$currency));
        return $md5key;
    }
    
    /**
     * Method outputs debug info when activated
     * @param string $debugInfo
     */
    private function debug($debugInfo, $method = 'NO METHOD SPECIFIED'){
        if(self::IS_DEBUGGING_RSDIBS){
            print 'METHOD: '.$method.'</ br>';
            if(is_string($debugInfo)){
                print '<pre>';
                print $debugInfo;
                print '</pre>';
            }else{
                print '<pre>';
                var_dump($debugInfo);
                print '</pre>';
            }
            print '</ br>';
        }
    }
    
    /**
     * Retrieves the transaction object from the database based on custom value
     * @param string $custom The custom value created by RS
     * @return object RS Transaction Object
     */
    private function getTransactionFromCustom($custom){
        $query =& $this->_db->getQuery(TRUE);
        $query->select('*');
        $query->from('#__rsmembership_transactions');
        $query->where('custom = '.$this->_db->Quote($custom));
        $query->where('gateway ='.$this->_db->Quote('Dibs'));
        $this->_db->setQuery($query);
        $this->debug($query->dump(), __FUNCTION__);
        $transaction = $this->_db->loadObject();
        return $transaction;
    }
    
    /**
     * Checks if a transaction with a certain hash already exists
     * @param string $hash A hash string. The transaction ID from DIBS.
     * @return bool True if it exists, otherwise false
     */
    private function isTransactionWithHashAlreadyDone($hash){
        $query =& $this->_db->getQuery(TRUE);
        $query->select('*');
        $query->from('#__rsmembership_transactions');
        $query->where('hash = '.$this->_db->Quote($hash));
        $query->where('gateway ='.$this->_db->Quote('Dibs'));
        $this->_db->setQuery($query);
        $this->debug($query->dump(), __FUNCTION__);
        $transaction = $this->_db->loadObject();
        if(!$transaction){
            return FALSE;
        }else{
            return TRUE;
        }
    }
    /**
     * Gets the RS membership object based on RS transaction ID
     * @param int $transId The RS transaction ID
     */
    private function getMembershipFromTransId($transId){
        $query =& $this->_db->getQuery(TRUE);
        $query->select('id, user_id, membership_id');
        $query->from('#__rsmembership_membership_users');
        $query->where('from_transaction_id = '.(int)$transId);
        $this->_db->setQuery($query);
        $this->debug($query->dump(), __FUNCTION__);
        $membership = $this->_db->loadObject();
        return $membership;
    }
    
    /**
     * Adds a recurring payment to RS
     * @param object $membership RS membership Object
     * @param object $transaction RS transaction Object
     * @param int $amount The amount of the transaction
     */
    private function addRecurring($membership, $transaction, $amount){
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'tables');
        $transaction =& JTable::getInstance('RSMembership_Transactions','Table');
        $user = JFactory::getUser($membership->user_id);
        $transaction->user_id = $user->get('id');
        $transaction->user_email = $user->get('email');
        $transaction->type = 'renew';
        $params = array();
        $params[] = 'id='.$membership->id;
        $params[] = 'membership_id='.$membership->membership_id;
        $transaction->params = implode(';', $params); // params, membership, extras etc
        $date = JFactory::getDate();
        $transaction->date = $date->toUnix();
        $transaction->ip = $_SERVER['REMOTE_ADDR'];
        $transaction->price = $this->_convertNumberFromDIBS($amount);
        $transaction->currency = RSMembershipHelper::getConfig('currency');
        $transaction->hash = '';
        $transaction->gateway = 'Dibs';
        $transaction->status = 'completed';
        $transaction->store();
        RSMembership::finalize($transaction->id);
    }
    
    /**
     * Checks if a payment is equal or higher than the membership price
     * @param int $transactionPrice The price for the transaction
     * @param int $paymentAmount The actual amount paid
     * @return bool True on success, false on fail.
     */
    private function isPaymentAdequate($transactionPrice, $paymentAmount){
        $price = $this->_convertNumberForDIBS($transactionPrice);
        if ($paymentAmount >= $price){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    /**
     * Checks if the currency of the payment equals the config
     * @param int $paymentCurrency Currency code from the payment
     * @return bool True on success, false on fail.
     */
    private function isCurrencyCorrect($paymentCurrency){
        //$currency = strtolower(trim(RSMembershipHelper::getConfig('currency')));
        if($paymentCurrency == '752'){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Updates the hash for a certain transaction
     * @param string $hash The DIBS transaction ID
     * @param int $transId The RS transaction ID
     */
    private function updateHashOnTransaction($hash, $transId){
        $query =& $this->_db->getQuery(TRUE);
        $query->update('#__rsmembership_transactions');
        $query->set('hash = '.$this->_db->Quote($hash));
        $query->where('id = '.(int)$transId);
        $this->_db->setQuery($query);
        $this->debug($query->dump(), __FUNCTION__);
        $this->_db->query();
    }
    
    /**
     * Method runs when DIBS performs a callback to the site.
     * Finalizes and adds logs to the database.
     */
    public function onPaymentNotification(){
        if (!$this->canRun()){
            return;
        }
        $log = array();
        $deny = FALSE;
        $authkey = JRequest::getVar('authkey');
        $dibsTransId = JRequest::getVar('transact');
        $amount = JRequest::getVar('amount');
        $currency = JRequest::getVar('currency');
        //$rscurrency = JRequest::getVar('rscurrency');
        //$orderId = JRequest::getVar('orderid');
        $custom = JRequest::getVar('uniqueoid');
        //$email = JRequest::getVar('delivery1.Email');
        $status = JRequest::getVar('statuscode');
        $isSuspectedFraud = JRequest::getBool('suspect');
        if(self::IS_DEBUGGING_RSDIBS){
            $isTransactionValid = TRUE;
        }  else {
            $isTransactionValid = $this->isMd5Valid($dibsTransId, $amount, $currency, $authkey);
        }
        
        $transFromCust = $this->getTransactionFromCustom($custom);
        $rsTransId = $transFromCust->id;
        $log[] = "DIBS reported a valid transaction.";
        $log[] = "Payment status is ".(!empty($status) ? $status : 'empty').".";
        $log[] = "Adding new payment...";
        $log[] = "Checking MD5 encryption";
        if(!$isTransactionValid){
            $calcMD5 = $this->calcMd5($dibsTransId, $amount, $currency, $authkey);
            $log[] = "Expected MD5 string $authkey does not match calculated $calcMD5. Stopping.";
            $deny  = true;
        }
        if($isSuspectedFraud){
            $log[] = "Suspected fraud according to DIBS. Stopping.";
            $deny  = true;
        }
        if(!$this->isTransactionWithHashAlreadyDone($dibsTransId)){
            $transaction = $this->getTransactionFromCustom($custom);
            if(!empty($transaction)){
                if ($transaction->status == 'completed'){
                    $log[] = "Identified this payment as recurring.";
                    $membership = $this->getMembershipFromTransId($transaction->id);
                    if (!empty($membership)){
                        $this->addRecurring($membership, $transaction, $amount);
                        RSMembership::approve($transaction->id);
                        $log[] = "Successfully added the recurring transaction to the database.";
                    }else{
                        $log[] = "Could not identify the original transaction for this recurring payment.";
                    }
                }else{
                    if($this->isPaymentAdequate($transaction->price, $amount)){
                        if($this->isCurrencyCorrect($currency)){
                            $this->updateHashOnTransaction($dibsTransId, $rsTransId);
                            RSMembership::approve($rsTransId);
                            $log[] = "Successfully added the payment to the database.";
                        }else{
                            $log[] = "Expected a currency of 752. DIBS reports this payment is made in $currency. Stopping.";
                            $deny  = true;
                        }
                    }else{
                        $log[] = "Expected an amount of $transaction->price. DIBS reports this payment is $amount. Stopping.";
                        $deny  = true;
                    }
                }
            }else{
                $log[] = "Could not identify transaction with custom hash $custom. Stopping.";
            }
        }else{
            $log[] = "The transaction $dibsTransId has already been processed. Stopping.";
        }
        if ($rsTransId){
            RSMembership::saveTransactionLog($log, $rsTransId);
            if ($deny){
                RSMembership::deny($rsTransId);
            }
        }
        if(self::IS_DEBUGGING_RSDIBS){
            exit;
        }
    }
    
    /**
     * Converts a float number to a number that DIBS understands
     * @param float $number
     * @return int A converted int number.
     */
    protected function _convertNumberForDIBS($number){
        $convertedNumber = number_format(((float)($number)), 2, '', '');
        return $convertedNumber;
    }
    
    /**
     * Converts a DIBS number to a regular float number
     * @param string $number
     * @return float A converted float number.
     */
    protected function _convertNumberFromDIBS($number){
        $oeren = substr($number, strlen($number)-3, 2);
        $kronor = substr($number, 0, strlen($number)-2);
        $convertedNumber = (float)$kronor.'.'.$oeren;
        return $convertedNumber;
    }

}