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
    
//    protected function url(){
//        $url = self::PAYMENT_URL_TEST;
//        if($this->_params->get('mode') == self::PAYMENT_MODE_LIVE){
//            $url = self::PAYMENT_URL_LIVE;
//        }
//        return $url;
//    }
    
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
    
//    protected function buildInputsGeneral(){
//        $businessEmail = htmlentities($this->_params->get('email'), ENT_COMPAT, 'UTF-8');
//        if($this->_params->get('message_type') == self::MESSAGE_TYPE_STANDARD){
//            $item_name = htmlentities($membership->name, ENT_COMPAT, 'UTF-8');
//        }else if($this->_params->get('message_type') == self::MESSAGE_TYPE_MEMBERSHIPNAME){
//            $item_name = htmlentities(JText::sprintf('RSM_MEMBERSHIP_PURCHASE_ON', date(RSMembershipHelper::getConfig('date_format'), RSMembershipHelper::getCurrentDate($transaction->date))), ENT_COMPAT, 'UTF-8');
//        }
//        $currencyCode = htmlentities(JText::sprintf('RSM_MEMBERSHIP_PURCHASE_ON', date(RSMembershipHelper::getConfig('date_format'), RSMembershipHelper::getCurrentDate($transaction->date))), ENT_COMPAT, 'UTF-8');
//        $inputs = array(
//            'business' => $businessEmail,
//            'charset' => 'utf-8',
//            'item_name' => $item_name,
//            'currency_code' => $currencyCode
//        );
//        array_merge($this->inputs, $inputs);
//    }
    
//    protected function buildInputsNewRecurring(){
//        $inputs = array(
//            'cmd' => '_xclick-subscriptions',
//            'no_shipping' => '1',
//            'no_note' => '1',
//            'src' => '1',
//            'sra' => '1'
//        );
//        array_merge($this->inputs, $inputs);
//    }
    
//    protected function buildInputsTrialPeriod($transaction, $db_membership, $extra_total){
//        // initial price
//        $price = $this->_convertNumber($transaction->price);
//        list($p, $t) = $this->_convertPeriod($db_membership->trial_period, $db_membership->trial_period_type);
//        // renewal price (+tax)				
//        $price = $this->_convertNumber($membership->use_renewal_price ? $db_membership->renewal_price + $this->_getTax($db_membership->renewal_price) : $db_membership->price + $this->_getTax($db_membership->price));
//        $price += $extra_total;
//        list($p, $t) = $this->_convertPeriod($db_membership->period, $db_membership->period_type);
//        $html .= '<input type="hidden" name="p3" value="'.$p.'" />';
//        $html .= '<input type="hidden" name="t3" value="'.$t.'" />';
//        $inputs = array(
//            'a1' => $price,
//            'p1' => $p,
//            't1' => $t,
//            'a3' => $price,
//            'p3' => $p,
//            't3' => $t
//        );
//        array_merge($this->inputs, $inputs);
//    }
    
//    protected function inputs(){
//        $merchant = $this->_params->get('merchant');
//        $inputs = array(
//            'acquirerlang' => 'sv',
//            'merchant' => $merchant,
//            'orderid' => '',
//            'lang' => 'sv',
//            'amount' => '',
//            
//        );
//    }
    
//    protected function html(){
//        $url = $this->url();
//        $html = '';
//        $html .= '<p>'.JText::_('RSM_PLEASE_WAIT_REDIRECT').'</p>';
//        $html .= '<form method="post" action="'.$url.'" id="dibsForm">';
//        return $html;
//    }
    
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
        $currency = $transaction->currency;
        $membershipName = $membership->name;
        $membershipSku = $membership->sku == '' ? 'medl' : $membership->sku;
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
        //---$html = $this->htmlGeneral();
        //---$this->buildInputsGeneral();
        $html = '';
        $html .= '<p>'.JText::_('RSM_PLEASE_WAIT_REDIRECT').'</p>';
        $html .= '<form method="post" action="'.$url.'" id="dibsForm">';
        $html .= '<input type="hidden" name="acquirerlang" value="sv" />';
        $html .= '<input type="hidden" name="merchant" value="'.htmlentities($this->_params->get('merchant'), ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="orderid" value="'.$orderId.'" />';
        $html .= '<input type="hidden" name="uniqueoid" value="'.htmlentities($transaction->custom).'" />';
        $html .= '<input type="hidden" name="lang" value="sv" />';
        //$html .= '<input type="hidden" name="paytype" value="VISA,MC,AMEX,MTRO,ELEC" />';
        if ($this->_params->get('mode') == self::PAYMENT_MODE_TEST){
            $html .= '<input type="hidden" name="test" value="yes" />';
        }

        //$html .= '<input type="hidden" name="business" value="'.htmlentities($this->_params->get('email'), ENT_COMPAT, 'UTF-8').'" />';
        //$html .= '<input type="hidden" name="charset" value="utf-8" />';
//        if ($this->_params->get('message_type')){
//            $html .= '<input type="hidden" name="ordertext" value="'.htmlentities($membership->name, ENT_COMPAT, 'UTF-8').'" />';
//        }else{
//            $html .= '<input type="hidden" name="ordertext" value="'.htmlentities(JText::sprintf('RSM_MEMBERSHIP_PURCHASE_ON', date(RSMembershipHelper::getConfig('date_format'), RSMembershipHelper::getCurrentDate($transaction->date))), ENT_COMPAT, 'UTF-8').'" />';
//        }
        $html .= $this->orderDetails($transaction, $membership);
        $html .= '<input type="hidden" name="rscurrency" value="'.htmlentities(RSMembershipHelper::getConfig('currency'), ENT_COMPAT, 'UTF-8').'" />';
        $html .= '<input type="hidden" name="currency" value="752" />';

//        if ($membership->recurring && $membership->period > 0 && $transaction->type == 'new'){
//            //---$this->buildInputsNewRecurring();
//            $html .= '<input type="hidden" name="cmd" value="_xclick-subscriptions" />';
//            $html .= '<input type="hidden" name="no_shipping" value="1" />';
//            $html .= '<input type="hidden" name="no_note" value="1" />';
//            $html .= '<input type="hidden" name="src" value="1" />';
//            $html .= '<input type="hidden" name="sra" value="1" />';
//            // trial period
//            if ($membership->use_trial_period){
//                //---$this->buildInputsTrialPeriod($transaction, $db_membership, $extra_total);
//                // initial price
//                $price = $this->_convertNumber($transaction->price);
//                $html .= '<input type="hidden" name="a1" value="'.$price.'" />';
//                list($p, $t) = $this->_convertPeriod($db_membership->trial_period, $db_membership->trial_period_type);
//                $html .= '<input type="hidden" name="p1" value="'.$p.'" />';
//                $html .= '<input type="hidden" name="t1" value="'.$t.'" />';
//
//                // renewal price (+tax)				
//                $price = $this->_convertNumber($membership->use_renewal_price ? $db_membership->renewal_price + $this->_getTax($db_membership->renewal_price) : $db_membership->price + $this->_getTax($db_membership->price));
//                // add extras
//                $price += $extra_total;
//                $html .= '<input type="hidden" name="a3" value="'.$price.'" />';
//                list($p, $t) = $this->_convertPeriod($db_membership->period, $db_membership->period_type);
//                $html .= '<input type="hidden" name="p3" value="'.$p.'" />';
//                $html .= '<input type="hidden" name="t3" value="'.$t.'" />';
//            }else{
//                if ($membership->use_renewal_price){
//                    // initial price
//                    $price = $this->_convertNumber($transaction->price);
//                    $html .= '<input type="hidden" name="a1" value="'.$price.'" />';
//                    list($p, $t) = $this->_convertPeriod($db_membership->period, $db_membership->period_type);
//                    $html .= '<input type="hidden" name="p1" value="'.$p.'" />';
//                    $html .= '<input type="hidden" name="t1" value="'.$t.'" />';
//
//                    // renewal price (+tax)
//                    $price = $this->_convertNumber($membership->renewal_price + $this->_getTax($membership->renewal_price));
//                    // add extras
//                    $price += $extra_total;
//                    $html .= '<input type="hidden" name="a3" value="'.$price.'" />';
//                    list($p, $t) = $this->_convertPeriod($db_membership->period, $db_membership->period_type);
//                    $html .= '<input type="hidden" name="p3" value="'.$p.'" />';
//                    $html .= '<input type="hidden" name="t3" value="'.$t.'" />';
//                }else{
//                    // renewal price
//                    $price = $this->_convertNumber($transaction->price);
//                    $html .= '<input type="hidden" name="a3" value="'.$price.'" />';
//                    list($p, $t) = $this->_convertPeriod($membership->period, $membership->period_type);
//                    $html .= '<input type="hidden" name="p3" value="'.$p.'" />';
//                    $html .= '<input type="hidden" name="t3" value="'.$t.'" />';
//                }
//            }
//        }else{
            //$html .= '<input type="hidden" name="cmd" value="_xclick" />';
            $html .= '<input type="hidden" name="amount" value="'.$this->_convertNumber($transaction->price).'" />';
 //       }
        if ($db_membership->activation == 1){
            $html .= '<input type="hidden" name="callbackurl" value="'.JRoute::_(JURI::root().'index.php?option=com_rsmembership&dibspayment=1').'" />';
        }elseif ($db_membership->activation == 2){
            $transaction->status = 'completed';
        }
        //$html .= '<input type="hidden" name="custom" value="'.htmlentities($transaction->custom).'" />';
        $html .= '<input type="hidden" name="accepturl" value="'.JRoute::_(JURI::root().'index.php?option=com_rsmembership&task=thankyou').'" />';
        //$html .= '<input type="hidden" name="rm" value="1" />';
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
    public function onAfterRender(){
        global $mainframe;
        $app =& JFactory::getApplication();		
        if($app->getName() != 'site') return;
        $paypalpayment = JRequest::getVar('dibspayment', '', 'get');
        if (!empty($paypalpayment))
                $this->onPaymentNotification();
    }
    
    /**
     * Returns the plugin limitations for display in the admin settings
     * @return string The limitations
     */
    public function getLimitations(){
        $this->loadLanguage('plg_system_rsmembershipdibs');
        return JText::_('RSM_DIBS_LIMITATIONS');
    }
	
//    protected function _buildPostData() {
//        // read the post from PayPal system and add 'cmd'
//        $req = 'cmd=_notify-validate';
//
//            //reading raw POST data from input stream. reading pot data from $_POST may cause serialization issues since POST data may contain arrays
//            $raw_post_data = file_get_contents('php://input');
//            if ($raw_post_data) {
//                    $raw_post_array = explode('&', $raw_post_data);
//                    $myPost = array();
//                    foreach ($raw_post_array as $keyval) {
//                            $keyval = explode ('=', $keyval);
//                            if (count($keyval) == 2) {
//                                    $myPost[$keyval[0]] = urldecode($keyval[1]);
//                            }
//                    }
//
//                    $get_magic_quotes_exists 	= function_exists('get_magic_quotes_gpc');
//                    $get_magic_quotes_gpc 		= get_magic_quotes_gpc();
//
//                    foreach ($myPost as $key => $value) {
//                            if ($key == 'limit' || $key == 'limitstart' || $key == 'option') continue;
//
//                            if ($get_magic_quotes_exists && $get_magic_quotes_gpc) {
//                                    $value = urlencode(stripslashes($value)); 
//                            } else {
//                                    $value = urlencode($value);
//                            }
//                            $req .= "&$key=$value";
//                    }
//            } else {
//                    // read the post from PayPal system
//                    $post = JRequest::get('post', JREQUEST_ALLOWRAW);
//                    foreach ($post as $key => $value)
//                    {
//                            if ($key == 'limit' || $key == 'limitstart' || $key == 'option') continue;
//
//                            $value = urlencode($value);
//                            $req .= "&$key=$value";
//                    }
//            }
//
//            return $req;
//    }
    
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
        $key1 = $this->_params->get('md5key1');
        $key2 = $this->_params->get('md5key2');
        $md5key = md5($key2.md5($key1.'transact='.$transId.'&amount='.$amount.'&currency='.$currency));
        if($authkey == $md5key){
            return TRUE;
        }else{
            return FALSE;
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
        $query->where('from_transaction_id = '.(int)$this->_db->Quote($transId));
        $this->_db->setQuery($query);
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
        $transaction->price = $amount;
        $transaction->currency = RSMembershipHelper::getConfig('currency');
        $transaction->hash = '';
        $transaction->gateway = 'Dibs';
        $transaction->status = 'pending';
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
        $price = $this->_convertNumber($transactionPrice);
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
        $query->where('id = '.(int)$this->_db->Quote($transId));
        $this->_db->setQuery($query);
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
        $authkey = JRequest::getVar('authkey');
        $dibsTransId = JRequest::getVar('transact');
        $amount = JRequest::getVar('amount');
        $currency = JRequest::getVar('currency');
        $rscurrency = JRequest::getVar('rscurrency');
        $orderId = JRequest::getVar('orderid');
        $custom = JRequest::getVar('uniqueoid',0,'post');
        $email = JRequest::getVar('delivery1.Email');
        $status = JRequest::getVar('statuscode');
        $isSuspectedFraud = JRequest::getBool('suspect');
        $isTransactionValid = $this->isMd5Valid($dibsTransId, $amount, $currency, $authkey);
        $rsTransId = $this->getTransactionFromCustom($custom)->id;
        $log[] = "DIBS reported a valid transaction.";
        $log[] = "Payment status is ".(!empty($status) ? $status : 'empty').".";
        $log[] = "Adding new payment...";
        if(!$this->isTransactionWithHashAlreadyDone($dibsTransId)){
            $transaction = $this->getTransactionFromCustom($custom);
            if(!empty($transaction)){
                if ($transaction->status == 'completed'){
                    $log[] = "Identified this payment as recurring.";
                    $membership = $this->getMembershipFromTransId($transaction->id);
                    if (!empty($membership)){
                        $this->addRecurring($membership, $transaction, $amount);
                        $log[] = "Successfully added the recurring transaction to the database.";
                    }else{
                        $log[] = "Could not identify the original transaction for this recurring payment.";
                    }
                }else{
                    if($this->isPaymentAdequate($transaction->price, $amount)){
                        if($this->isCurrencyCorrect($currency)){
                            $this->updateHashOnTransaction($dibsTransId, $transaction->id);
                            RSMembership::approve($transaction->id);
                            $log[] = "Successfully added the payment to the database.";
                        }else{
                            $log[] = "Expected a currency of 752. PayPal reports this payment is made in $currency. Stopping.";
                            $deny  = true;
                        }
                    }else{
                        $log[] = "Expected an amount of $transaction->price. PayPal reports this payment is $amount. Stopping.";
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
                RSMembership::deny($transaction_id);
            }
        }

        
        
        //$req = $this->_buildPostData();
        // post back to PayPal system to validate
//        $url = $this->_params->get('mode') ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
//
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
//            $res = curl_exec($ch);
//            $errstr = curl_error($ch);
//            curl_close($ch);
//
//            // assign posted variables to local variables
//            $item_name 			= JRequest::getVar('item_name', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $item_number 		= JRequest::getVar('item_number', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $payment_status 	= JRequest::getVar('payment_status', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $payment_amount 	= JRequest::getVar('mc_gross', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $payment_currency 	= JRequest::getVar('mc_currency', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $txn_id 			= JRequest::getVar('txn_id', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $txn_type 			= JRequest::getVar('txn_type', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $receiver_email 	= JRequest::getVar('receiver_email', '', 'post', 'none', JREQUEST_ALLOWRAW);
//            $payer_email 		= JRequest::getVar('payer_email', '', 'post', 'none', JREQUEST_ALLOWRAW);
//
//            $custom = JRequest::getVar('custom',0,'post');
//
//            // try to get the transaction id based on the custom hash
//            $this->_db->setQuery("SELECT id FROM #__rsmembership_transactions WHERE `custom`='".$this->_db->getEscaped($custom)."' AND `gateway`='PayPal'");
//            $transaction_id = $this->_db->loadResult();
//
//            $deny = false;

//            if ($res)
//            {
//                    if (strcmp ($res, "VERIFIED") == 0)
//                    {
//                            $log[] = "DIBS reported a valid transaction.";
//                            $log[] = "Payment status is ".(!empty($payment_status) ? $payment_status : 'empty').".";
//                            // check the payment_status is Completed
//                            //if ($this->_params->get('mode') == 0 || $payment_status == 'Completed')
//                            //{
//                                    // sign up - do nothing, we use our "custom" parameter to identify the transaction
//                                    if ($txn_type == 'subscr_signup')
//                                    {
//                                            return;
//                                    }
//                                    elseif ($txn_type == 'subscr_payment')
//                                    {
//                                            $log[] = "Adding new payment...";
//                                            // check that txn_id has not been previously processed
//                                            // check custom_hash from db -> if custom_hash == txn_id
//                                            $this->_db->setQuery("SELECT `id` FROM #__rsmembership_transactions WHERE `hash`='".$this->_db->getEscaped($txn_id)."' AND `gateway`='PayPal' LIMIT 1");
//                                            if (!$this->_db->loadResult())
//                                            {
//                                                    $this->_db->setQuery("SELECT * FROM #__rsmembership_transactions WHERE `custom`='".$this->_db->getEscaped($custom)."' AND `gateway`='Dibs'");
//                                                    $transaction = $this->_db->loadObject();
//
//                                                    // check if transaction exists
//                                                    if (!empty($transaction))
//                                                    {
//                                                            // this transaction has already been processed
//                                                            // we need to create a new "renewal" transaction
//                                                            if ($transaction->status == 'completed')
//                                                            {
//                                                                    $log[] = "Identified this payment as recurring.";
//
//                                                                    $this->_db->setQuery("SELECT id, user_id, membership_id FROM #__rsmembership_membership_users WHERE `from_transaction_id`='".$transaction->id."' LIMIT 1");
//                                                                    $membership = $this->_db->loadObject();
//
//                                                                    if (!empty($membership))
//                                                                    {
//                                                                            $user = JFactory::getUser($membership->user_id);
//
//                                                                            JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'tables');
//                                                                            $transaction =& JTable::getInstance('RSMembership_Transactions','Table');
//                                                                            $transaction->user_id = $user->get('id');
//                                                                            $transaction->user_email = $user->get('email');
//                                                                            $transaction->type = 'renew';
//                                                                            $params = array();
//                                                                            $params[] = 'id='.$membership->id;
//                                                                            $params[] = 'membership_id='.$membership->membership_id;
//
//                                                                            $transaction->params = implode(';', $params); // params, membership, extras etc
//                                                                            $date = JFactory::getDate();
//                                                                            $transaction->date = $date->toUnix();
//                                                                            $transaction->ip = $_SERVER['REMOTE_ADDR'];
//                                                                            $transaction->price = $payment_amount;
//                                                                            $transaction->currency = RSMembershipHelper::getConfig('currency');
//                                                                            $transaction->hash = '';
//                                                                            $transaction->gateway = 'PayPal';
//                                                                            $transaction->status = 'pending';
//
//                                                                            // store the transaction
//                                                                            $transaction->store();
//
//                                                                            RSMembership::finalize($transaction->id);
//
//                                                                            $log[] = "Successfully added the recurring transaction to the database.";
//                                                                    }
//                                                                    else
//                                                                            $log[] = "Could not identify the original transaction for this recurring payment.";
//                                                            }
//                                                    }
//                                                    else
//                                                            $log[] = "Could not identify transaction with custom hash $custom. Stopping.";
//                                            }
//                                            else
//                                                    $log[] = "The transaction $txn_id has already been processed. Stopping.";
//                                    }
//                                    else
//                                    {
//                                            // check that txn_id has not been previously processed
//                                            // check custom_hash from db -> if custom_hash == txn_id
//                                            $this->_db->setQuery("SELECT `id` FROM #__rsmembership_transactions WHERE `hash`='".$this->_db->getEscaped($txn_id)."' AND `gateway`='PayPal' LIMIT 1");
//                                            if (!$this->_db->loadResult())
//                                            {
//                                                    $this->_db->setQuery("SELECT * FROM #__rsmembership_transactions WHERE `custom`='".$this->_db->getEscaped($custom)."' AND `status`!='completed' AND `gateway`='PayPal'");
//                                                    $transaction = $this->_db->loadObject();
//
//                                                    // check if transaction exists
//                                                    if (empty($transaction))
//                                                            $log[] = "Could not identify transaction with custom hash $custom. Stopping.";
//                                            }
//                                            else
//                                                    $log[] = "The transaction $txn_id has already been processed. Stopping.";
//                                    }
//
//                                    if (!empty($transaction))
//                                    {
//                                            $plugin_email   = strtolower(trim($this->_params->get('email')));
//                                            $receiver_email = strtolower(trim($receiver_email));
//
//                                            // check that receiver_email is your Primary PayPal email
//                                            if ($plugin_email == $receiver_email)
//                                            {								
//                                                    // check that payment_amount/payment_currency are correct
//                                                    // check $payment_amount == $price from $subscription_id && $payment_currency == $price from $subscription_id
//                                                    $price = $this->_convertNumber($transaction->price);
//                                                    $currency = strtolower(trim(RSMembershipHelper::getConfig('currency')));
//                                                    $payment_currency = strtolower(trim($payment_currency));
//                                                    if ($payment_amount >= $price)
//                                                    {
//                                                            if ($currency == $payment_currency)
//                                                            {
//                                                                    // set the hash
//                                                                    $this->_db->setQuery("UPDATE #__rsmembership_transactions SET `hash`='".$this->_db->getEscaped($txn_id)."' WHERE `id`='".$transaction->id."' LIMIT 1");
//                                                                    $this->_db->query();
//
//                                                                    // process payment
//                                                                    RSMembership::approve($transaction->id);
//
//                                                                    $log[] = "Successfully added the payment to the database.";
//                                                            }
//                                                            else
//                                                            {
//                                                                    $log[] = "Expected a currency of $currency. PayPal reports this payment is made in $payment_currency. Stopping.";
//                                                                    $deny  = true;
//                                                            }
//                                                    }
//                                                    else
//                                                    {
//                                                            $log[] = "Expected an amount of $price $currency. PayPal reports this payment is $payment_amount $payment_currency. Stopping.";
//                                                            $deny  = true;
//                                                    }
//                                            }
//                                            else
//                                            {
//                                                    $log[] = "Expected payment to be made to $plugin_email. PayPal reports this payment is made for $receiver_email. Stopping.";
//                                                    $deny  = true;
//                                            }
//                                    }
//                            //}
//                            //else
//                            //{
//                            //	$log[] = "Payment status is $payment_status. Stopping.";
//                            //	$deny  = true;
//                            //}
//                    }
//                    elseif (strcmp($res, "INVALID") == 0)
//                    {
//                            $log[] = "Could not verify transaction authencity. PayPal said it's invalid.";
//                            $log[] = "String sent to PayPal is $req";
//                            $deny  = true;
//                            // log for manual investigation
//                    }
//            }
//            else
//                    $log[] = "Could not open $url in order to verify this transaction. Error reported is: $errstr";
//
//            if ($transaction_id)
//            {
//                    RSMembership::saveTransactionLog($log, $transaction_id);
//                    if ($deny)
//                            RSMembership::deny($transaction_id);
//            }
    }
    
    /**
     * Converts a float number to a number that DIBS understands
     * @param float $number
     * @return int A converted int number.
     */
    protected function _convertNumber($number){
        $convertedNumber = number_format(((float)($number)), 2, '', '');
        return $convertedNumber;
    }
	
//	protected function _convertPeriod($period, $type)
//	{
//		$return = array();
//		
//		$return[0] = $period;
//		$return[1] = strtoupper($type);
//		
//		return $return;
//	}
}