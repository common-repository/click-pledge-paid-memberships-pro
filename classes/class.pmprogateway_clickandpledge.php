<?php	
error_reporting(0);
require_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'paid-memberships-pro' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'gateways' . DIRECTORY_SEPARATOR . 'class.pmprogateway.php');
//load classes init method
add_action('init', array('PMProGateway_clickandpledge', 'init'));
global $wpdb; 
global $payinc;
$table_name         = $wpdb->prefix . 'cnp_wp_pmpcnpsettingsinfo';
$tokentable_name    = $wpdb->prefix . 'cnp_wp_pmpcnptokeninfo';
$accountstable_name = $wpdb->prefix . 'cnp_wp_pmpcnpaccountsinfo';
$charset_collate    = $wpdb->get_charset_collate();
 $payinc =1;
			$settingssql = "CREATE TABLE $table_name (
			  `cnpsettingsinfo_id` int(11) NOT NULL AUTO_INCREMENT,
			  `cnpsettingsinfo_clientid` varchar(255) NOT NULL,
			  `cnpsettingsinfo_clentsecret` varchar(255) NOT NULL,
			  `cnpsettingsinfo_granttype` varchar(255) NOT NULL,
			  `cnpsettingsinfo_scope` varchar(255) NOT NULL,
			   PRIMARY KEY (`cnpsettingsinfo_id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $settingssql );
			
			$tokensql = "CREATE TABLE $tokentable_name (
 			`cnptokeninfo_id` int(11) NOT NULL AUTO_INCREMENT,
			`cnptokeninfo_username` varchar(255) NOT NULL,
			`cnptokeninfo_code` varchar(255) NOT NULL,
			`cnptokeninfo_accesstoken` text NOT NULL,
			`cnptokeninfo_refreshtoken` text NOT NULL,
			`cnptokeninfo_date_added` datetime NOT NULL,
			`cnptokeninfo_date_modified` datetime NOT NULL,
			 PRIMARY KEY (`cnptokeninfo_id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $tokensql );
			
			$accountssql = "CREATE TABLE $accountstable_name (
 			  `cnpaccountsinfo_id` int(11) NOT NULL AUTO_INCREMENT,
			  `cnpaccountsinfo_orgid` varchar(100) NOT NULL,
  			  `cnpaccountsinfo_orgname` varchar(250) NOT NULL,
  	          `cnpaccountsinfo_accountguid` varchar(250) NOT NULL,
			  `cnpaccountsinfo_userfirstname` varchar(250) NOT NULL,
			  `cnpaccountsinfo_userlastname` varchar(250) NOT NULL,
			  `cnpaccountsinfo_userid` varchar(250) NOT NULL,
			  `cnpaccountsinfo_crtdon` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `cnpaccountsinfo_crtdby` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			   PRIMARY KEY (`cnpaccountsinfo_id`)) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $accountssql );
			 $cnpsql= "SELECT count(*) as cnt FROM ". $table_name;
			 $rowcount = $wpdb->get_var( $cnpsql );
			if($rowcount == 0)
			{ 
					$cnpfldname = 'connectwordpressplugin';
					$cnpfldtext = 'zh6zoyYXzsyK9fjVQGd8m+ap4o1qP2rs5w/CO2fZngqYjidqZ0Fhbhi1zc/SJ5zl';
					$cnpfldpwd = 'password';
					$cnpfldaccsid = 'openid profile offline_access';


					$wpdb->insert( 
						$table_name, 
						array( 
							'cnpsettingsinfo_clientid' => $cnpfldname, 
							'cnpsettingsinfo_clentsecret' => $cnpfldtext, 
							'cnpsettingsinfo_granttype' => $cnpfldpwd,
							'cnpsettingsinfo_scope' => $cnpfldaccsid,
						) 
					);
			}	/**
	 * PMProGateway_gatewayname Class
	 *
	 * Handles example integration.
	 *
	 */
	class PMProGateway_clickandpledge extends PMProGateway
	{
		public $responsecodes;
		public $VaultGUID;
		public $TransactionNumber;
		public $country_code;
		 
		function PMProGateway($gateway = NULL)
		{
			$this->gateway = $gateway;
			
			$this->responsecodes = array(2054=>'Total amount is wrong',2055=>'AccountGuid is not valid',2056=>'AccountId is not valid',2057=>'Username is not valid',2058=>'Password is not valid',2059=>'Invalid recurring parameters',2060=>'Account is disabled',2101=>'Cardholder information is null',2102=>'Cardholder information is null',2103=>'Cardholder information is null',2104=>'Invalid billing country',2105=>'Credit Card number is not valid',2106=>'Cvv2 is blank',2107=>'Cvv2 length error',2108=>'Invalid currency code',2109=>'CreditCard object is null',2110=>'Invalid card type ',2111=>'Card type not currently accepted',2112=>'Card type not currently accepted',2210=>'Order item list is empty',2212=>'CurrentTotals is null',2213=>'CurrentTotals is invalid',2214=>'TicketList lenght is not equal to quantity',2215=>'NameBadge lenght is not equal to quantity',2216=>'Invalid textonticketbody',2217=>'Invalid textonticketsidebar',2218=>'Invalid NameBadgeFooter',2304=>'Shipping CountryCode is invalid',2305=>'Shipping address missed',2401=>'IP address is null',2402=>'Invalid operation',2501=>'WID is invalid',2502=>'Production transaction is not allowed. Contact support for activation.',2601=>'Invalid character in a Base-64 string',2701=>'ReferenceTransaction Information Cannot be NULL',2702=>'Invalid Refrence Transaction Information',2703=>'Expired credit card',2805=>'eCheck Account number is invalid',2807=>'Invalid payment method',2809=>'Invalid payment method',2811=>'eCheck payment type is currently not accepted',2812=>'Invalid check number',1001=>'Internal error. Retry transaction',1002=>'Error occurred on external gateway please try again',2001=>'Invalid account information',2002=>'Transaction total is not correct',2003=>'Invalid parameters',2004=>'Document is not a valid xml file',2005=>'OrderList can not be empty',3001=>'Invalid RefrenceTransactionID',3002=>'Invalid operation for this transaction',4001=>'Fraud transaction',4002=>'Duplicate transaction',5001=>'Declined (general)',5002=>'Declined (lost or stolen card)',5003=>'Declined (fraud)',5004=>'Declined (Card expired)',5005=>'Declined (Cvv2 is not valid)',5006=>'Declined (Insufficient fund)',5007=>'Declined (Invalid credit card number)');
			$this->VaultGUID = '';
			$this->TransactionNumber = '';
			$this->country_code = array( 'DE' => '276','AT' => '040','BE' => '056','CA' => '124','CN' => '156','ES' => '724',	'FI' => '246','FR' => '250','GR' => '300', 'IT' => '380','JP' => '392','LU' => '442', 'NL' => '528','PL' => '616','PT' => '620','CZ' => '203','GB' => '826','SE' => '752', 'CH' => '756','DK' => '208','US' => '840','HK' => '344','NO' => '578','AU' => '036',	'SG' => '702','IE' => '372','NZ' => '554','KR' => '410','IL' => '376','ZA' => '710','NG' => '566','CI' => '384','TG' => '768','BO' => '068','MU' => '480','RO' => '642',	'SK' => '703','DZ' => '012','AS' => '016','AD' => '020','AO' => '024','AI' => '660',	'AG' => '028','AR' => '032','AM' => '051','AW' => '533','AZ' => '031','BS' => '044',	'BH' => '048','BD' => '050','BB' => '052','BY' => '112','BZ' => '084','BJ' => '204',	'BT' => '060','56' => '064','BW' => '072','BR' => '076','BN' => '096','BF' => '854',	'MM' => '104','BI' => '108','KH' => '116','CM' => '120','CV' => '132','CF' => '140',	'TD' => '148','CL' => '152','CO' => '170','KM' => '174','CD' => '180','CG' => '178',	'CR' => '188','HR' => '191','CU' => '192','CY' => '196','DJ' => '262','DM' => '212',	'DO' => '214','TL' => '626','EC' => '218','EG' => '818','SV' => '222','GQ' => '226',	'ER' => '232','EE' => '233','ET' => '231','FK' => '238','FO' => '234','FJ' => '242', 'GA' => '266','GM' => '270','GE' => '268','GH' => '288','GD' => '308','GL' => '304', 'GI' => '292','GP' => '312','GU' => '316','GT' => '320','GG' => '831','GN' => '324', 'GW' => '624','GY' => '328','HT' => '332','HM' => '334','VA' => '336','HN' => '340', 'IS' => '352','IN' => '356','ID' => '360','IR' => '364','IQ' => '368','IM' => '833', 'JM' => '388','JE' => '832','JO' => '400','KZ' => '398','KE' => '404','KI' => '296', 'KP' => '408','KW' => '414','KG' => '417','LA' => '418','LV' => '428','LB' => '422','LS' => '426','LR' => '430','LY' => '434','LI' => '438','LT' => '440','MO' => '446','MK' => '807','MG' => '450','MW' => '454','MY' => '458','MV' => '462','ML' => '466','MT' => '470','MH' => '584','MQ' => '474','MR' => '478','HU' => '348','YT' => '175','MX' => '484','FM' => '583','MD' => '498','MC' => '492','MN' => '496','ME' => '499','MS' => '500','MA' => '504','MZ' => '508','NA' => '516','NR' => '520','NP' => '524','BQ' => '535','NC' => '540','NI' => '558','NE' => '562','NU' => '570','NF' => '574','MP' => '580','OM' => '512','PK' => '586','PW' => '585','PS' => '275','PA' => '591','PG' => '598','PY' => '600','PE' => '604','PH' => '608','PN' => '612','PR' => '630','QA' => '634','RE' => '638','RU' => '643','RW' => '646','BL' => '652','KN' => '659', 'LC' => '662','MF' => '663','PM' => '666','VC' => '670','WS' => '882','SM' => '674',	'ST' => '678','SA' => '682','SN' => '686','RS' => '688','SC' => '690','SL' => '694','SI' => '705','SB' => '090','SO' => '706','GS' => '239','LK' => '144','SD' => '729','SR' => '740','SJ' => '744','SZ' => '748','SY' => '760','TW' => '158','TJ' => '762','TZ' => '834','TH' => '764','TK' => '772','TO' => '776','TT' => '780','TN' => '788','TR' => '792','TM' => '795','TC' => '796','TV' => '798','UG' => '800','UA' => '804','AE' => '784','UY' => '858','UZ' => '860','VU' => '548','VE' => '862','VN' => '704','VG' => '092','VI' => '850','WF' => '876','EH' => '732','YE' => '887','ZM' => '894','ZW' => '716','AL' => '008','AF' => '004','AQ' => '010','BA' => '070','BV' => '074','IO' => '086','BG' => '100','KY' => '136','CX' => '162','CC' => '166','CK' => '184','GF' => '254','PF' => '258','TF' => '260','AX' => '248','CW' => '531','SH' => '654','SX' => '534','SS' => '728','UM' => '581'		
          );
			return $this->gateway;
		}	
		

		/**
		 * Run on WP init
		 *
		 * @since 1.8
		 */
		static function init()
		{ 
			//make sure example is a gateway option
			add_filter('pmpro_gateways', array('PMProGateway_clickandpledge', 'pmpro_gateways'));

			//add fields to payment settings
			add_filter('pmpro_payment_options', array('PMProGateway_clickandpledge', 'pmpro_payment_options'));
			add_filter('pmpro_payment_option_fields', array('PMProGateway_clickandpledge', 'pmpro_payment_option_fields'), 10, 2);

			//add some fields to edit user page (Updates)
			add_action('pmpro_after_membership_level_profile_fields', array('PMProGateway_clickandpledge', 'user_profile_fields'));
			add_action('profile_update', array('PMProGateway_clickandpledge', 'user_profile_fields_save'));

			//custom sku & connect Campaign URL Alias
			add_action('pmpro_membership_level_after_other_settings',array('PMProGateway_clickandpledge','cnp_custom_function') );
			add_action('pmpro_save_membership_level',array('PMProGateway_clickandpledge','cnp_custom_function_save') );
			
					
			//updates cron
			add_action('pmpro_activation', array('PMProGateway_clickandpledge', 'pmpro_activation'));
			add_action('pmpro_deactivation', array('PMProGateway_clickandpledge', 'pmpro_deactivation'));
			add_action('pmpro_cron_example_subscription_updates', array('PMProGateway_clickandpledge', 'pmpro_cron_example_subscription_updates'));

			add_action( 'wp_ajax_cnp_getcode', array('PMProGateway_clickandpledge','cnp_pmpgetconnectcode') );
			add_action( 'wp_ajax_nopriv_cnp_getcode', array('PMProGateway_clickandpledge','cnp_pmpgetconnectcode') );
			add_action( 'wp_ajax_cnp_pmpgetaccounts', array('PMProGateway_clickandpledge','cnp_pmpgetcnpaccounts') );
			add_action( 'wp_ajax_nopriv_cnp_pmpgetaccounts', array('PMProGateway_clickandpledge','cnp_pmpgetcnpaccounts') );
			add_action( 'wp_ajax_cnp_pmprefreshAccounts', array('PMProGateway_clickandpledge','cnp_pmprefreshAccounts') );
			add_action( 'wp_ajax_nopriv_cnp_pmprefreshAccounts', array('PMProGateway_clickandpledge','cnp_pmprefreshAccounts') );
			
			add_action( 'wp_ajax_getCnPPMPUserEmailAccountList', array('PMProGateway_clickandpledge','cnp_getCnPPMPUserEmailAccountList'));
			add_action( 'wp_ajax_nopriv_getCnPPMPUserEmailAccountList', array('PMProGateway_clickandpledge','cnp_getCnPPMPUserEmailAccountList'));
			//code to add at checkout if example is the current gateway
			$gateway = pmpro_getOption("gateway");
        
			if($gateway == "clickandpledge")
			{
				add_action('pmpro_checkout_preheader', array('PMProGateway_clickandpledge', 'pmpro_checkout_preheader'));
				add_filter('pmpro_checkout_order', array('PMProGateway_clickandpledge', 'pmpro_checkout_order'));
				//add_filter('pmpro_include_billing_address_fields', array('PMProGateway_clickandpledge', 'pmpro_include_billing_address_fields'));				add_filter('pmpro_include_billing_address_fields', '__return_false');
				add_filter('pmpro_include_cardtype_field', array('PMProGateway_clickandpledge', 'pmpro_include_billing_address_fields'));
				
				//add_filter('pmpro_include_payment_information_fields',     array('PMProGateway_clickandpledge', 'pmpro_include_payment_information_fields'));				add_filter('pmpro_include_payment_information_fields', '__return_true');
			}
		}
	public static function getCnPPMPrefreshtoken() {
		global $wpdb;
	    $table_name         = self::get_cnp_tokeninfo();
		$cnprefreshtkn      = $wpdb->get_var("SELECT cnptokeninfo_refreshtoken  FROM $table_name");
		$settingstable_name = self::get_cnp_settingsinfo();
        $sql                = "SELECT * FROM ". $settingstable_name;
        $results            = $wpdb->get_results($sql, ARRAY_A);
        $count              = sizeof($results);
        for($i=0; $i<$count; $i++){
			 $password="password";
			$cnpsecret = openssl_decrypt($results[$i]['cnpsettingsinfo_clentsecret'],"AES-128-ECB",$password);
			
			 $rtncnpdata = "client_id=".$results[$i]['cnpsettingsinfo_clientid']."&client_secret=". $cnpsecret."&grant_type=refresh_token&scope=".$results[$i]['cnpsettingsinfo_scope']."&refresh_token=".$cnprefreshtkn;
        }
	 return $rtncnpdata;
	}
	public static function cnp_pmprefreshAccounts()
	{
		
	$rtnrefreshtokencnpdata =  self::getCnPPMPrefreshtoken();
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://aaas.cloud.clickandpledge.com/IdServer/connect/token",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $rtnrefreshtokencnpdata,
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/x-www-form-urlencoded"

		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
			$cnptokendata = json_decode($response);
			$cnptoken = $cnptokendata->access_token;
			$cnprtokentyp = $cnptokendata->token_type;
			if($cnptoken != "")
			{
				$curl = curl_init();

			  curl_setopt_array($curl, array(
  			  CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/accountlist",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: ".$cnprtokentyp." ".$cnptoken,
				"content-type: application/json"),
			  	));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  
					$cnpAccountsdata = json_decode($response); $camrtrnval = "";
					$rtncnpdataf = self::delete_cnppmpaccountslist();
					
						$confaccno =  $_REQUEST['Accountid'];

					foreach($cnpAccountsdata as $cnpkey =>$cnpvalue)
					{
					 $selectacnt ="";
					 $cnporgid = $cnpvalue->OrganizationId;
					 $cnporgname = addslashes($cnpvalue->OrganizationName);
					 $cnpaccountid = $cnpvalue->AccountGUID;
					 $cnpufname = addslashes($cnpvalue->UserFirstName);
					 $cnplname = addslashes($cnpvalue->UserLastName);
				     $cnpuid = $cnpvalue->UserId;
					
					$rtncnpdata = self::insert_cnppmpaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid);
						if($confaccno == $cnporgid){$selectacnt ="selected='selected'";}
						
		 $camrtrnval .= "<option value='".$cnporgid."'". $selectacnt .">".$cnporgid." [".$cnpvalue->OrganizationName."]</option>";
		

	 }
					echo $camrtrnval;
					}
					//print_r($cnpAccountsdata);
				   
				}
			}
			
	}
		
	public static function cnp_pmpgetconnectcode(){
		$curl = curl_init();
		$cnpemailaddress = $_REQUEST['cnpemailid'];
		curl_setopt_array($curl, array(
  		CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/requestcode",
  		CURLOPT_RETURNTRANSFER => true,
  	    CURLOPT_ENCODING => "",
  		CURLOPT_MAXREDIRS => 10,
  	    CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded",
        "email: ".$cnpemailaddress),));

		$response = curl_exec($curl);
	
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
		wp_die(); 
	 }
		public static function get_cnptransactions($cnpemailid,$cnpcode){
        global $wpdb;
		
        $table_name = self::get_cnp_settingsinfo();
       
        $sql = "SELECT * FROM ". $table_name;

        $results = $wpdb->get_results($sql, ARRAY_A);

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
			$password="password";
			$cnpsecret = openssl_decrypt($results[$i]['cnpsettingsinfo_clentsecret'],"AES-128-ECB",$password);
			
			 $rtncnpdata = "client_id=".$results[$i]['cnpsettingsinfo_clientid']."&client_secret=". $cnpsecret."&grant_type=".$results[$i]['cnpsettingsinfo_granttype']."&scope=".$results[$i]['cnpsettingsinfo_scope']."&username=".$cnpemailid."&password=".$cnpcode;
        }

        return $rtncnpdata;
    }
	public static function delete_cnptransactions(){
        global $wpdb;
        $table_name = self::get_cnp_tokeninfo();
        $wpdb->query("DELETE FROM ". $table_name);
    }
	public static function cnp_getCnPPMPUserEmailAccountList() { 
		$cnpwcpnpaccountid = $_REQUEST['cnppmpacid']; $cnppmprtrntxt = "";
		$cnppmprtrntxt = self::getwcCnPPMPConnectCampaigns($cnpwcpnpaccountid,'');
        $cnppmprtrnpaymentstxt = self::getCnPPMPactivePaymentList($cnpwcpnpaccountid); 
		echo $cnppmprtrntxt."||".$cnppmprtrnpaymentstxt;
	 die();
	}	
	public static function cnp_pmpgetcnpaccounts(){
		
		$cnpemailid = $_REQUEST['cnpemailid'];
		$cnpcode = $_REQUEST['cnpcode'];
		$cnptransactios = self::get_cnptransactions($cnpemailid,$cnpcode);
		  $curl = curl_init();
		  curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://aaas.cloud.clickandpledge.com/idserver/connect/token",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $cnptransactios,
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/x-www-form-urlencoded"

		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		$cnptokendata = json_decode($response);
		
		 if(!isset($cnptokendata->error)){
			$cnptoken = $cnptokendata->access_token;
			$cnprtoken = $cnptokendata->refresh_token;
			$cnptransactios = self::delete_cnptransactions();
			$rtncnpdata =	self::insrt_cnptokeninfo($cnpemailid,$cnpcode,$cnptoken,$cnprtoken);	
			
			if($rtncnpdata != "")
			{
				$curl = curl_init();

			  curl_setopt_array($curl, array(
  			  CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/accountlist",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Bearer ".$cnptoken,
				"content-type: application/json"),
			  	));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				 
					$cnpAccountsdata = json_decode($response);
					$cnptransactios = self::delete_cnppmpaccountslist();
					foreach($cnpAccountsdata as $cnpkey =>$cnpvalue)
					{
					 $cnporgid = $cnpvalue->OrganizationId;
					 $cnporgname = addslashes($cnpvalue->OrganizationName);
					 $cnpaccountid = $cnpvalue->AccountGUID;
					 $cnpufname = addslashes($cnpvalue->UserFirstName);
					 $cnplname = addslashes($cnpvalue->UserLastName);
				     $cnpuid = $cnpvalue->UserId;
					 $cnptransactios = self::insert_cnppmpaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid);	
						
					}
					//print_r($cnpAccountsdata);
				   echo "success";
				}
			}
		}else{
				echo "error";
			}
			
	    }
		die();
	}	
		public static function insert_cnppmpaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid){
        global $wpdb;
        $table_name = self::get_cnp_accountsinfo();
      
            $wpdb->insert($table_name, array('cnpaccountsinfo_orgid' => $cnporgid, 
					'cnpaccountsinfo_orgname' => $cnporgname, 
					'cnpaccountsinfo_accountguid' => $cnpaccountid,
					'cnpaccountsinfo_userfirstname' => $cnpufname,
					'cnpaccountsinfo_userlastname'=> $cnplname,
					'cnpaccountsinfo_userid'=> $cnpuid));
            $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
			
        return $id;
    }
		public static function delete_cnppmpaccountslist(){
        global $wpdb;
        $table_name = self::get_cnp_accountsinfo();
        $wpdb->query("DELETE FROM ". $table_name);
    }
		 public static function insrt_cnptokeninfo($cnpemailid, $cnpcode, $cnptoken, $cnprtoken){
        global $wpdb;
        $table_name = self::get_cnp_tokeninfo();
         $wpdb->insert($table_name, array('cnptokeninfo_username' => $cnpemailid, 
					'cnptokeninfo_code' => $cnpcode, 
					'cnptokeninfo_accesstoken' => $cnptoken,
					'cnptokeninfo_refreshtoken' => $cnprtoken));
		
            $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
			
        return $id;
		
    }
	 public static function getwcCnPPMPConnectCampaigns($cnpaccid,$selectcampaign)
	{

		$cnpacountid = $cnpaccid;
	    $cnpaccountGUID = self::getCnPPMPAccountGUID($cnpacountid);
		$connect_name = $selectcampaign;
        $cnpcampaignalias ="";
		$cnpUID = "14059359-D8E8-41C3-B628-E7E030537905";
		$cnpKey = "5DC1B75A-7EFA-4C01-BDCD-E02C536313A3";
		$connect  = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
	    $client   = new SoapClient('https://resources.connect.clickandpledge.com/wordpress/Auth2.wsdl', $connect);
    
		if( isset($cnpacountid) && $cnpacountid !="" && isset($cnpaccountGUID) &&  $cnpaccountGUID !="")
		{ 
			$xmlr  = new SimpleXMLElement("<GetActiveCampaignList2></GetActiveCampaignList2>");
			$cnpsel ="";
			$xmlr->addChild('accountId', $cnpacountid);
			$xmlr->addChild('AccountGUID', $cnpaccountGUID);
			$xmlr->addChild('username', $cnpUID);
			$xmlr->addChild('password', $cnpKey);
			$response = $client->GetActiveCampaignList2($xmlr); 
			$responsearr =  $response->GetActiveCampaignList2Result->connectCampaign;
	
			 $cnporderRes = [];
    if( is_object($responsearr) && $responsearr->alias != ""){

      $cnporderRes[$responsearr->alias] = $responsearr->name;
    }
    else {
      foreach ($responsearr as $obj) {
        $cnporderRes[$obj->alias] = $obj->name;
      }
    }
    natcasesort($cnporderRes);
      
			if( $selectcampaign != '')
			{
				 $cnpcampaignalias 	 =  $selectcampaign;
			}
			
			 $camrtrnval = "<option value=''>Select Campaign Name</option>";
        
		foreach ($cnporderRes as $cnpkey => $cnpvalue) {
        if($cnpkey == $cnpcampaignalias){ $cnpsel ="selected='selected'";}else{$cnpsel ="";}
      $camrtrnval .= "<option value='" . $cnpkey . "' $cnpsel>" . $cnpvalue . " (" . $cnpkey . ")</option>";
      }
			
			}
		
		return $camrtrnval;
			
		}
		/**
		 * Make sure example is in the gateways list
		 *
		 * @since 1.8
		 */
		static function pmpro_gateways($gateways)
		{
			if(empty($gateways['clickandpledge']))
				$gateways['clickandpledge'] = __('Click & Pledge', 'pmpro');

			return $gateways;
		}
		static function pmpro_checkout_preheader()		{			global $gateway, $pmpro_level;		}	
		
		/**		 * Check settings if billing address should be shown.		 * @since 1.8		 */		
		
		static function pmpro_include_billing_address_fields($include)		{			$include = true;	
																			 return $include;		}			
		/**		 * Use our own payment fields at checkout. (Remove the name attributes.)				 * @since 1.8		 */		/*		static function pmpro_include_payment_information_fields($include)		{			//global vars			//global $pmpro_requirebilling, $pmpro_show_discount_code, $discount_code, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear;			//return true;		}*/
		/**
		 * Get a list of payment options that the example gateway needs/supports.
		 *
		 * @since 1.8
		 */
		static function getGatewayOptions()
		{
			$options = array(
				'sslseal',
				'nuclear_HTTPS',
				'gateway_environment',				
				'clickandpledge_AccountID',
				'clickandpledge_AccountGuid',
				'clickandpledge_email_customer',
				'clickandpledge_OrganizationInformation',
				'clickandpledge_connect_campaign',
				'clickandpledge_TermsCondition',
				'clickandpledge_email_customer_trial',
				'clickandpledge_OrganizationInformation_trial',
				'clickandpledge_TermsCondition_trial',
				'clickandpledge_connect_campaign_trial',
				'clickandpledge_email_customer_recurring',
				'clickandpledge_OrganizationInformation_subscription',
				'clickandpledge_TermsCondition_subscription',
				'clickandpledge_connect_campaign_subscription',
				'use_ssl',
				'tax_state',
				'tax_rate'
			);

			return $options;
		}
		
		/**,
				'accepted_credit_cards'
		 * Set payment options for payment settings page.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_options($options)
		{
			//get example options
			$clickandpledge_options = PMProGateway_clickandpledge::getGatewayOptions();
			//merge with others.
			$options = array_merge($clickandpledge_options, $options);

			return $options;
		}
		public static function get_cnp_settingsinfo(){
        global $wpdb;
        return $wpdb->prefix . "cnp_wp_pmpcnpsettingsinfo";
    }
		public static function get_cnp_tokeninfo(){
        global $wpdb;
        return $wpdb->prefix . "cnp_wp_pmpcnptokeninfo";
    }

	 public static function get_cnp_accountsinfo(){
        global $wpdb;
        return $wpdb->prefix . "cnp_wp_pmpcnpaccountsinfo";
    }
	public static function get_cnppmpaccountsinfo()
	{
		 global $wpdb;
		 $table_name = self::get_cnp_accountsinfo();;
		 $id = $wpdb->get_var("SELECT count(*) as cnt from ". $table_name);
	  return $id;
	}
		public static function getCnPPMPLoginUser() {
		$cnpPMPAccountId ="";
		global $wpdb;
        $table_name = self::get_cnp_accountsinfo();
		$cnpPMPAccountId = $wpdb->get_var("SELECT cnpaccountsinfo_userid FROM " . $table_name." Limit 0,1"); 
	 	return $cnpPMPAccountId;
	}
	public static function getCnPPMPAccountsList() {
		$data['cnpaccounts'] = array();
			 global $wpdb;
        $table_name = self::get_cnp_accountsinfo();
		$query = "SELECT * FROM ". $table_name." order by cast(cnpaccountsinfo_orgid as integer) ASC";
		 $results = $wpdb->get_results($query, ARRAY_A);

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
           	$data['cnpaccounts'][] = array(
       		'AccountId'      => $results[$i]['cnpaccountsinfo_orgid'],
			'GUID'           => $results[$i]['cnpaccountsinfo_accountguid'],
			'Organization'           => $results[$i]['cnpaccountsinfo_orgname']    
   		);
        }

      return $data['cnpaccounts'];
		
	}
public static function getCnPPMPAccountGUID($accid) {
		$cnpAccountGUId ="";
		 	 global $wpdb;
        $table_name = self::get_cnp_accountsinfo();
		$cnpaccountguid = $wpdb->get_var("SELECT cnpaccountsinfo_accountguid FROM $table_name where cnpaccountsinfo_orgid ='".$accid."'");
		
       
	 return $cnpaccountguid;
		
	}
	public static function getCnPPMPactivePaymentList($cnpaccid)
	{
		$cmpacntacptdcards = "";
		$cnpacountid = $cnpaccid;
		$cnpaccountGUID = self::getCnPPMPAccountGUID($cnpacountid);
		$cnpUID = "14059359-D8E8-41C3-B628-E7E030537905";
		$cnpKey = "5DC1B75A-7EFA-4C01-BDCD-E02C536313A3";
		$connect1  = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
	    $client1   = new SoapClient('https://resources.connect.clickandpledge.com/wordpress/Auth2.wsdl', $connect1);
		if( isset($cnpacountid) && $cnpacountid !="" && isset($cnpaccountGUID) &&  $cnpaccountGUID !="")
		{ 
			$xmlr1  = new SimpleXMLElement("<GetAccountDetail></GetAccountDetail>");
			$xmlr1->addChild('accountId',$cnpacountid);
			$xmlr1->addChild('accountGUID',$cnpaccountGUID);
			$xmlr1->addChild('username',$cnpUID);
			$xmlr1->addChild('password',$cnpKey);
			$response1                    =  $client1->GetAccountDetail($xmlr1);
			$optionsarry = get_option('gfcnp_plugin');
			$avcrds = unserialize($optionsarry['available_cards']);
			
			 
			$responsearramex              =  $response1->GetAccountDetailResult->Amex;
			$responsearrJcb               =  $response1->GetAccountDetailResult->Jcb;
			$responsearrMaster            =  $response1->GetAccountDetailResult->Master;
			$responsearrVisa              =  $response1->GetAccountDetailResult->Visa;
			$responsearrDiscover          =  $response1->GetAccountDetailResult->Discover;
			$responsearrecheck            =  $response1->GetAccountDetailResult->Ach;
			$responsearrCustomPaymentType =  $response1->GetAccountDetailResult->CustomPaymentType;
				
			$cnpamex 					  =  $avcrds['American_Express'];
			$cnpjcb 					  =  $avcrds['JCB'];
			$cnpMaster 					  =  $avcrds['MasterCard'];
			$cnpVisa 					  =  $avcrds['Visa'];
			$cnpDiscover 				  =  $avcrds['Discover'];
			$cnpecheck 				      =  $optionsarry['payment_cnp_hidcnpeCheck'];
			$cnpcp 				          =  $optionsarry['payment_cnp_purchas_order'];
			$cnpcc 				          =  $optionsarry['payment_cnp_hidcnpcreditcard'];
				
			$cmpacntacptdcards .= '<input type="hidden" name="payment_cnp_hidcnpcreditcard" id="payment_cnp_hidcnpcreditcard"';
			if($responsearramex == true || $responsearrJcb == true || $responsearrMaster== true || $responsearrVisa ==true || $responsearrDiscover == true ){ 
				$cmpacntacptdcards .= ' value="CreditCard">';
			}else{ $cmpacntacptdcards .= ' value="">'; }
				$cmpacntacptdcards .= '<input type="hidden" name="payment_cnp_hidcnpeCheck" id="payment_cnp_hidcnpeCheck"';
			if($responsearrecheck == true){
				$cmpacntacptdcards .= ' value="eCheck">';
			}else{ $cmpacntacptdcards .= ' value="">'; }
			if($responsearramex == false){
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_amex" ).prop( "checked", false );
			});
			</script>
				<?php
			
			}
			else{
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_amex" ).prop( "checked", true );
			});
			</script>
				<?php
			}
			if($responsearrJcb == false){
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_jcb" ).prop( "checked", false );
			});
			</script>
				<?php
			
			}
			else{
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_jcb" ).prop( "checked", true );
			});
			</script>
				<?php
			}
			if($responsearrMaster == false){
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_mastercard" ).prop( "checked", false );
			});
			</script>
				<?php
			
			}
			else{
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_mastercard" ).prop( "checked", true );
			});
			</script>
				<?php
			}
			if($responsearrVisa == false){
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_visa" ).prop( "checked", false );
			});
			</script>
				<?php
			
			}
			else{
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_visa" ).prop( "checked", true );
			});
			</script>
				<?php
			}
			if($responsearrDiscover == false){
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_discover" ).prop( "checked", false );
			});
			</script>
				<?php
			
			}
			else{
				?><script>
					jQuery(document).ready(function(){ 
			jQuery( "#creditcards_discover" ).prop( "checked", true );
			});
			</script>
				<?php
			}
			$cmpacntacptdcards .= '<table cellpadding="5" cellspacing="3" style="font-weight:bold;padding:2px;" id="tblacceptedcards">
                    <tbody><tr>
                    <td width="200"><input type="checkbox" id="payment_cnp_creditcard" class="checkbox_active" value="CreditCard" name="payment_cnp_creditcard"  onclick="block_creditcard(this.checked);" ';
			if(($responsearramex == true || $responsearrJcb == true || $responsearrMaster== true || $responsearrVisa ==true || $responsearrDiscover == true) )
			{$cmpacntacptdcards .= 'checked="checked"';}
		     $cmpacntacptdcards .= 'checked="checked" disabled="disabled"> Credit Card</td></tr>
			 <tr class="tracceptedcards"><td></td><td>
			 <table cellspacing="0">
					
					<tbody class="accounts">
						<tr class="account">								
									<td style="padding:2px;"><strong>Accepted Credit Cards</strong></td></tr>';
								if($responsearrVisa == true){
									
							      $cmpacntacptdcards .= '<tr class="account">								
									<td style="padding:2px;"><br><input type="Checkbox" name="payment_cnp_Visa" id="payment_cnp_Visa"';
									if(isset($cnpVisa)){ $cmpacntacptdcards .='checked="checked "'; }
									 $cmpacntacptdcards .= 'value="Visa" checked="checked" disabled="disabled">Visa</td></tr>';
								  }
								if($responsearramex == true){
									$cmpacntacptdcards .= '<tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_American_Express" id="payment_cnp_American_Express"';
									if(isset($cnpamex)){ $cmpacntacptdcards .='checked="checked"'; }
									$cmpacntacptdcards .= 'value="American Express" checked="checked" disabled="disabled">American Express</td>
								  </tr>';
								}if($responsearrDiscover == true){
								 $cmpacntacptdcards .= ' <tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_Discover" id="payment_cnp_Discover"'; 
									if(isset($cnpDiscover)){ $cmpacntacptdcards .='checked="checked"'; }
										$cmpacntacptdcards .= ' value="Discover" checked="checked" disabled="disabled">Discover</td>
								  </tr>';
								}if($responsearrMaster == true){
								  $cmpacntacptdcards .= '<tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_MasterCard" id="payment_cnp_MasterCard"';
									if(isset($cnpMaster)){ $cmpacntacptdcards .='checked="checked"'; }
									$cmpacntacptdcards .= ' value="MasterCard"  checked="checked" disabled="disabled">MasterCard</td>
								  </tr>';
								}if($responsearrJcb == true){
								  $cmpacntacptdcards .= '<tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_JCB" id="payment_cnp_JCB"';
									if(isset($cnpjcb)){ $cmpacntacptdcards .='checked="checked"'; }
									$cmpacntacptdcards .= ' value="JCB" checked="checked" disabled="disabled">JCB</td>
								  </tr>';
								}
			$cmpacntacptdcards .= '</tbody></table></td></tr>';
			
					$cmpacntacptdcards .= '</tbody></table>';
				
	
		

		}	
		return $cmpacntacptdcards;
		
	}
	
	
	/**
		 * Display fields for Click & Pledge options.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_option_fields($values, $gateway)
		{
		//global $pmpro_currencies;
		//echo '<pre>';
		//print_r($pmpro_currencies);
				 $cnppmptransactios = PMProGateway_clickandpledge::get_cnppmpaccountsinfo();

		?>
	
<script>
		jQuery(document).ready(function(){ 
		
			<?php if((get_option('pmpro_gateway') == "") && ($cnppmptransactios > 0)){?>
					  jQuery('#gateway').val('clickandpledge');
										  <?php }?>
				admpmpdefaultpayment();
			<?php if ($cnppmptransactios > 0){?>
			jQuery("tr#cnpfrmregister").hide();
			jQuery("tr#cnpfrmsettings").show();
			
			<?php } else {?>
			jQuery("tr#cnpfrmregister").show();
			jQuery('tr#cnpfrmsettings').hide();
			
			
			<?php }?>
			
			
			jQuery(".cnpchangeacnt").click(function(){
  			jQuery("tr#cnpfrmregister").show();
			jQuery('tr#cnpfrmsettings').hide();
			jQuery( "#cnp_emailid" ).focus();
				
		      });
			jQuery(".cnppmpsettings").click(function(){
  			jQuery("tr#cnpfrmregister").hide();
			jQuery('tr#cnpfrmsettings').show();
		      });
			jQuery('#clickandpledge_CustomPayment_Titles').change(function(e) {
		    admpmpdefaultpayment();
	});
		});
	function admpmpdefaultpayment() { 
					var paymethods = [];
					var paymethods_titles = [];
					var str = '';
					var defaultval = jQuery('#clickandpledge_DefaultpaymentMethod').val();
					
					if(jQuery('#payment_cnp_hidcnpcreditcard').val()!=""){						        paymethods.push('CreditCard');
					 paymethods_titles.push('Credit Card');
					}
					if(jQuery('#payment_cnp_hidcnpeCheck').val()!="") {
						paymethods.push('eCheck');
						paymethods_titles.push('eCheck');
					}
					
					if(jQuery('#payment_cnp_purchas_order').is(':checked')) {
						jQuery('#clickandpledge_CustomPayment_Titles').closest('tr').show();
						jQuery('#clickandpledge_ReferenceNumber_Label').closest('tr').show();
						
						var titles = jQuery('#clickandpledge_CustomPayment_Titles').val();
						var titlesarr = titles.split(";");
						for(var j=0;j < titlesarr.length; j++)
						{
							if(titlesarr[j] !=""){
								paymethods.push(titlesarr[j]);
								paymethods_titles.push(titlesarr[j]);
							}
						}
					} else {
						jQuery('#clickandpledge_CustomPayment_Titles').closest('tr').hide();
						jQuery('#clickandpledge_ReferenceNumber_Label').closest('tr').hide();
					}
					
					if(paymethods.length > 0) {
						for(var i = 0; i < paymethods.length; i++) {
							if(paymethods[i] == defaultval) {
							str += '<option value="'+paymethods[i]+'" selected>'+paymethods_titles[i]+'</option>';
							} else {
							str += '<option value="'+paymethods[i]+'">'+paymethods_titles[i]+'</option>';
							}
						}
					} else {
					 str = '<option selected="selected" value="">Please select</option>';
					}
					jQuery('#clickandpledge_DefaultpaymentMethod').html(str);
				}
	
	</script>
	<tr class="pmpro_settings_divider gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<td colspan="2">
			<?php $currentcnppmpusr = self::getCnPPMPLoginUser();?>
				<?php echo "<img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/image_3422.png' title='Click & Pledge' alt='Click & Pledge'/>";?>
				<?php //_e('Click & Pledge Settings', 'pmpro'); 
			   if($currentcnppmpusr !=""){?>  <strong>[You are  logged in as: <?php echo $currentcnppmpusr;?>]</strong> <?php }?>
			</td>
		</tr>
		<tr id="cnpfrmsettings"><td colspan="2"><table width="100%" border=0>
	
			<tr  class="gateway gateway_clickandpledge " <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<td> </td>
		<td align="right" ><a id="cnpchangeacnt" class="cnpchangeacnt" href="#">Change User</a></td></tr>
		
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<?php  $cnptransactios = self::getCnPPMPAccountsList();
			foreach($cnptransactios as $v) {
			   if ($v['AccountId'] == $values['clickandpledge_AccountID']) {
				  $found = true;
				   $cnpactiveuser = $v['AccountId'];
			   }
			} 
		 if(!isset($found)) {$cnpactiveuser = $cnptransactios[0]['AccountId'];} 
		 $cnppmp_acceptedcards = self::getCnPPMPactivePaymentList($cnpactiveuser);

		?>
			<th scope="row" valign="top">	
				<label for="clickandpledge_AccountID"><?php _e('Account ID', 'pmpro');?>:</label>
			</th>
			<td><select name="clickandpledge_AccountID" id="clickandpledge_AccountID" class="form-control">
			<?php foreach($cnptransactios as $cnpacnts){?>
		<option value=<?php echo $cnpacnts['AccountId'];?> <?php if($cnpacnts['AccountId'] == $cnpactiveuser){echo "selected";} ?>><?php echo $cnpacnts['AccountId'];?> [<?php echo stripslashes($cnpacnts['Organization']);?>] </option>
				<?php }?></select>   <a href="#" id="cnppmprfrshtokens">Refresh Accounts </a>
				
			</td>
		</tr>
	
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">	
				<label for="clickandpledge_AccountGuid"><?php _e('Payment Methods', 'pmpro');?>:</label>
			</th>
			<td id="cnpacceptedcards">
				<?php echo $cnppmp_acceptedcards;?>
			</td>
		</tr>
			
		
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">	
				<label for="clickandpledge_email_customer"><?php _e('Send Receipt to Patron (Initial Payment)', 'pmpro');?>:</label>
			</th>
			<td>
				<input type="checkbox" id="clickandpledge_email_customer" name="clickandpledge_email_customer" size="60" value="1" <?php if(esc_attr($values['clickandpledge_email_customer']) == '1') { ?>checked<?php } ?>/>
			</td>
		</tr>
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
					<th valign="top" scope="row"><label>CONNECT Campaign URL Alias:</label></th><td>
					<?php 
			 $cnptransactios1 = self::getCnPPMPAccountsList();
			foreach($cnptransactios1 as $v1) {
			   if ($v1['AccountId'] == $values['clickandpledge_AccountID']) {
				  $found = true;
				   $cnpactiveuser1 = $v1['AccountId'];
			   }
			} 
		 if(!isset($found)) {$cnpactiveuser1 = $cnptransactios1[0]['AccountId'];} 
			
			$pmpcnpconnectcampaign=self::getwcCnPPMPConnectCampaigns($cnpactiveuser1,$values['clickandpledge_connect_campaign']);?>
			<select name="clickandpledge_connect_campaign" id="clickandpledge_connect_campaign" class="input-text regular-input required" >
			<?php echo $pmpcnpconnectcampaign; ?>
		</select>
				
				<br />
			     <div>CONNECT Campaign URL Alias from the respective CONNECT Campaign, so that the receipt assigned to that campaign will replace the generic receipt</div>
				
				</td>
				</tr>
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">	
				<label for="clickandpledge_OrganizationInformation"><?php _e('Receipt Header', 'pmpro');?>:</label>
			</th>
			<td>
				<textarea id="clickandpledge_OrganizationInformation" name="clickandpledge_OrganizationInformation" rows="3" cols="80"><?php echo esc_attr(stripcslashes($values['clickandpledge_OrganizationInformation']))?></textarea>
				<br />
				Maximum: 1500 characters.You have <span id="clickandpledge_OrganizationInformation_countdown">1500</span> characters left.
			</td>
		</tr>
		
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<th scope="row" valign="top">	
				<label for="clickandpledge_TermsCondition"><?php _e('Terms & Conditions', 'pmpro');?>:</label>
			</th>
			<td>
				<textarea id="clickandpledge_TermsCondition" name="clickandpledge_TermsCondition" rows="3" cols="80"><?php echo esc_attr(stripcslashes($values['clickandpledge_TermsCondition']))?></textarea>
				<br />
				Maximum: 1500 characters.You have <span id="clickandpledge_TermsCondition_countdown">1500</span> characters left.
			</td>
		</tr>
		
		<script>
		   jQuery(document).ready(function(){			
				if(jQuery('#gateway').val() == 'clickandpledge')
				{
					jQuery('.gateway_clickandpledge').show();
					/*jQuery('label[for=creditcards_dinersclub], input#creditcards_dinersclub').hide();
					jQuery('label[for=creditcards_enroute], input#creditcards_enroute').hide();*/
					jQuery('input[name="creditcards_dinersclub"]').attr('checked', false);
					jQuery('input[name="creditcards_enroute"]').attr('checked', false);					
				}
				
				jQuery('input[name="creditcards_dinersclub"]').click(function(){
					if(jQuery('#gateway').val() == 'clickandpledge') {
						alert('Click and pledge do not support this credit card type');
						return false;
					}
				});
			
				jQuery('input[name="creditcards_enroute"]').click(function(){
					if(jQuery('#gateway').val() == 'clickandpledge') {
						alert('Click and pledge do not support this credit card type');
						return false;
					}
				});
				function limitText(limitField, limitCount, limitNum) {
					if (limitField.val().length > limitNum) {
						limitField.val( limitField.val().substring(0, limitNum) );
					} else {
						limitCount.html (limitNum - limitField.val().length);
					}
				}
				function in_array(needle, haystack, argStrict) {
				  var key = '',
					strict = !! argStrict;
				  if (strict) {
					for (key in haystack) {
					  if (haystack[key] === needle) {
						return true;
					  }
					}
				  } else {
					for (key in haystack) {
					  if (haystack[key] == needle) {
						return true;
					  }
					}
				  }
				  return false;
				}
				limitText(jQuery('#clickandpledge_OrganizationInformation'),jQuery('#clickandpledge_OrganizationInformation_countdown'),1500);				
				limitText(jQuery('#clickandpledge_TermsCondition'),jQuery('#clickandpledge_TermsCondition_countdown'),1500);
			   
			   limitText(jQuery('#clickandpledge_OrganizationInformation_trial'),jQuery('#clickandpledge_OrganizationInformation_countdown_trail'),1500);				
				limitText(jQuery('#clickandpledge_TermsCondition_trial'),jQuery('#clickandpledge_TermsCondition_countdown_trial'),1500);
				//OrganizationInformation
				jQuery('#clickandpledge_OrganizationInformation').keydown(function(){
					limitText(jQuery('#clickandpledge_OrganizationInformation'),jQuery('#clickandpledge_OrganizationInformation_countdown'),1500);
				});
				jQuery('#clickandpledge_OrganizationInformation').keyup(function(){
					limitText(jQuery('#clickandpledge_OrganizationInformation'),jQuery('#clickandpledge_OrganizationInformation_countdown'),1500);
				});				
				//TermsCondition
				jQuery('#clickandpledge_TermsCondition').keydown(function(){
					limitText(jQuery('#clickandpledge_TermsCondition'),jQuery('#clickandpledge_TermsCondition_countdown'),1500);
				});
				jQuery('#clickandpledge_TermsCondition').keyup(function(){
					limitText(jQuery('#clickandpledge_TermsCondition'),jQuery('#clickandpledge_TermsCondition_countdown'),1500);
				});
			   jQuery('#cnppmprfrshtokens').on('click', function() 
		 {  
		 	 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'cnp_pmprefreshAccounts',
					  	'Accountid':jQuery('#clickandpledge_AccountID').val(),
					  },
				   cache: false,
				  beforeSend: function() {
					jQuery('.cnp_loader').show();
					jQuery("#clickandpledge_AccountID").html("<option>Loading............</option>");
					},
					complete: function() {
						jQuery('.cnp_loader').hide();
						
					//	$("#cnp_btncode").prop('value', 'Login');
					},	
				  success: function(htmlText) {
				  if(htmlText !== "")
				  {
					jQuery("#clickandpledge_AccountID").html(htmlText);  
				  //$(".cnpcode").show();
				  }
				  else
				  {
				  jQuery(".cnperror").show();
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
	 return false;
 });
jQuery('#clickandpledge_AccountID').change(function() {
		
		var  cnppmpaccountid= jQuery('#clickandpledge_AccountID').val().trim();
		
		 	 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'getCnPPMPUserEmailAccountList',
					  	'cnppmpacid':cnppmpaccountid,
						},
				  cache: false,
				  beforeSend: function() {
					
					jQuery("#clickandpledge_connect_campaign").html("<option>Loading............</option>");
					jQuery("#clickandpledge_connect_campaign_trial").html("<option>Loading............</option>");
					jQuery("#clickandpledge_connect_campaign_subscription").html("<option>Loading............</option>");
					},
					complete: function() {
					
					},	
				  success: function(htmlText) {
				
				  if(htmlText !== "")
				  {
                
					
					var res = htmlText.split("||");
					jQuery("#clickandpledge_connect_campaign").html(res[0]);  
					jQuery("#clickandpledge_connect_campaign_trial").html(res[0]); 
					jQuery("#clickandpledge_connect_campaign_subscription").html(res[0]); 
					jQuery("td#cnpacceptedcards").html(res[1]);  
						
				  }
				  else
				  {
				  jQuery(".cnperror").show();
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
	 return false;
 });
				jQuery('#clickandpledge_AccountID').closest('form').submit(function(event){
					if(jQuery('#gateway').val() == 'clickandpledge') {
						if(jQuery.trim(jQuery('#clickandpledge_AccountID').val()) == '')
						{
							alert('Please enter Account ID');
							jQuery('#clickandpledge_AccountID').focus();
							return false;
						}
						if(jQuery('#clickandpledge_AccountID').val().length > 10)
						{
							alert('Please enter only 10 digits');
							jQuery('#clickandpledge_AccountID').focus();
							return false;
						}
						
						
					}
				});				
		   });
		</script>
		<tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <th scope="row" valign="top">	
				<label for="clickandpledge_email_customer_trial"><?php _e('Send Receipt to Patron (Custom Trial)', 'pmpro');?>:</label>
			</th>
			<td>
				<input type="checkbox" id="clickandpledge_email_customer_trial" name="clickandpledge_email_customer_trial" size="60" value="1" <?php if(esc_attr($values['clickandpledge_email_customer_trial']) == '1') { ?>checked<?php } ?>/>
			</td>
	   </tr>
	   <tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
					<th valign="top" scope="row"><label>CONNECT Campaign URL Alias:</label></th><td>
					<?php 
			 $cnptransactios1 = self::getCnPPMPAccountsList();
			foreach($cnptransactios1 as $v1) {
			   if ($v1['AccountId'] == $values['clickandpledge_AccountID']) {
				  $found = true;
				   $cnpactiveuser1 = $v1['AccountId'];
			   }
			} 
		 if(!isset($found)) {$cnpactiveuser1 = $cnptransactios1[0]['AccountId'];} 
			
			$pmpcnpconnectcampaign=self::getwcCnPPMPConnectCampaigns($cnpactiveuser1,$values['clickandpledge_connect_campaign_trial']);?>
			<select name="clickandpledge_connect_campaign_trial" id="clickandpledge_connect_campaign_trial" class="input-text regular-input required" >
			<?php echo $pmpcnpconnectcampaign; ?>
		</select>
				
				<br />
			     <div>CONNECT Campaign URL Alias from the respective CONNECT Campaign, so that the receipt assigned to that campaign will replace the generic receipt</div>
				</td>
				</tr>
	   <tr class="gateway gateway_clickandpledge clickandpledge_OrganizationInformation_trial" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <th scope="row" valign="top">	
				<label for="clickandpledge_OrganizationInformation_trial"><?php _e('Receipt Header', 'pmpro');?>:</label>
			</th>
			<td>
				<textarea id="clickandpledge_OrganizationInformation_trial" name="clickandpledge_OrganizationInformation_trial" rows="3" cols="80"><?php echo esc_attr(stripcslashes($values['clickandpledge_OrganizationInformation_trial']))?></textarea>
				<br />
				Maximum: 1500 characters.You have <span id="clickandpledge_OrganizationInformation_countdown_trial">1500</span> characters left.
			</td>
	   </tr>
	   
	   <tr class="gateway gateway_clickandpledge clickandpledge_TermsCondition_trial" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <th scope="row" valign="top">	
				<label for="clickandpledge_TermsCondition_trial"><?php _e('Terms & Conditions', 'pmpro');?>:</label>
			</th>
			<td>
				<textarea id="clickandpledge_TermsCondition_trial" name="clickandpledge_TermsCondition_trial" rows="3" cols="80"><?php echo esc_attr(stripcslashes(pmpro_getOption("clickandpledge_TermsCondition_trial")))?></textarea>
				<br />
				Maximum: 1500 characters.You have <span id="clickandpledge_TermsCondition_countdown_trial">1500</span> characters left.
			</td>
	   </tr>
	   <script>
	   jQuery(document).ready(function(){
			function limitText(limitField, limitCount, limitNum) {
				if (limitField.val().length > limitNum) {
					limitField.val( limitField.val().substring(0, limitNum) );
				} else {
					limitCount.html (limitNum - limitField.val().length);
				}
			}
			limitText(jQuery('#clickandpledge_OrganizationInformation_trial'),jQuery('#clickandpledge_OrganizationInformation_countdown_trial'),1500);
			limitText(jQuery('#clickandpledge_TermsCondition_trial'),jQuery('#clickandpledge_TermsCondition_countdown_trial'),1500);
			//OrganizationInformation
			jQuery('#clickandpledge_OrganizationInformation_trial').keydown(function(){
				limitText(jQuery('#clickandpledge_OrganizationInformation_trial'),jQuery('#clickandpledge_OrganizationInformation_countdown_trial'),1500);
			});
			jQuery('#clickandpledge_OrganizationInformation').keyup(function(){
				limitText(jQuery('#clickandpledge_OrganizationInformation'),jQuery('#clickandpledge_OrganizationInformation_countdown'),1500);
			});			
			//TermsCondition
			jQuery('#clickandpledge_TermsCondition_trial').keydown(function(){
				limitText(jQuery('#clickandpledge_TermsCondition_trial'),jQuery('#clickandpledge_TermsCondition_countdown_trial'),1500);
			});
			jQuery('#clickandpledge_TermsCondition_trial').keyup(function(){
				limitText(jQuery('#clickandpledge_TermsCondition_trial'),jQuery('#clickandpledge_TermsCondition_countdown_trial'),1500);
			});			
	   });
	   </script>
	   
	   <tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <th scope="row" valign="top">	
				<label for="clickandpledge_email_customer_recurring"><?php _e('Send Receipt to Patron (Recurring Subscription)', 'pmpro');?>:</label>
			</th>
			<td>
				<input type="checkbox" id="clickandpledge_email_customer_recurring" name="clickandpledge_email_customer_recurring" size="60" value="1" <?php if(esc_attr($values['clickandpledge_email_customer_recurring']) == '1') { ?>checked<?php } ?>/>
			</td>
	   </tr>
	   <tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
					<th valign="top" scope="row"><label>CONNECT Campaign URL Alias:</label></th><td>
					<?php 
			 $cnptransactios1 = self::getCnPPMPAccountsList();
			foreach($cnptransactios1 as $v1) {
			   if ($v1['AccountId'] == $values['clickandpledge_AccountID']) {
				  $found = true;
				   $cnpactiveuser1 = $v1['AccountId'];
			   }
			} 
		 if(!isset($found)) {$cnpactiveuser1 = $cnptransactios1[0]['AccountId'];} 
			
			$pmpcnpconnectcampaign=self::getwcCnPPMPConnectCampaigns($cnpactiveuser1,$values['clickandpledge_connect_campaign_subscription']);?>
			<select name="clickandpledge_connect_campaign_subscription" id="clickandpledge_connect_campaign_subscription" class="input-text regular-input required" >
			<?php echo $pmpcnpconnectcampaign; ?>
		</select>
			
				<br />
			     <div>CONNECT Campaign URL Alias from the respective CONNECT Campaign, so that the receipt assigned to that campaign will replace the generic receipt</div>
				
				</td>
				</tr>
		<tr class="gateway gateway_clickandpledge clickandpledge_OrganizationInformation_subscription" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <th scope="row" valign="top">	
				<label for="clickandpledge_OrganizationInformation_subscription"><?php _e('Receipt Header', 'pmpro');?>:</label>
			</th>
			<td>
				<textarea id="clickandpledge_OrganizationInformation_subscription" name="clickandpledge_OrganizationInformation_subscription" rows="3" cols="80"><?php echo esc_attr(stripcslashes($values['clickandpledge_OrganizationInformation_subscription']))?></textarea>
				<br />
				Maximum: 1500 characters.You have <span id="clickandpledge_OrganizationInformation_countdown_subscription">1500</span> characters left.
			</td>
	   </tr>
	  
	   <tr class="gateway gateway_clickandpledge clickandpledge_TermsCondition_subscription" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <th scope="row" valign="top">	
				<label for="clickandpledge_TermsCondition_subscription"><?php _e('Terms & Conditions', 'pmpro');?>:</label>
			</th>
			<td>
				<textarea id="clickandpledge_TermsCondition_subscription" name="clickandpledge_TermsCondition_subscription" rows="3" cols="80"><?php echo esc_attr(stripcslashes($values['clickandpledge_TermsCondition_subscription']))?></textarea>
				<br />
				Maximum: 1500 characters.You have <span id="clickandpledge_TermsCondition_countdown_subscription">1500</span> characters left.
			</td>
	   </tr>
	   <script>
		   jQuery(document).ready(function(){
				function limitText(limitField, limitCount, limitNum) {
					if (limitField.val().length > limitNum) {
						limitField.val( limitField.val().substring(0, limitNum) );
					} else {
						limitCount.html (limitNum - limitField.val().length);
					}
				}
				limitText(jQuery('#clickandpledge_OrganizationInformation_subscription'),jQuery('#clickandpledge_OrganizationInformation_countdown_subscription'),1500);
				limitText(jQuery('#clickandpledge_TermsCondition_subscription'),jQuery('#clickandpledge_TermsCondition_countdown_subscription'),1500);
				
				//OrganizationInformation
				jQuery('#clickandpledge_OrganizationInformation_subscription').keydown(function(){
					limitText(jQuery('#clickandpledge_OrganizationInformation_subscription'),jQuery('#clickandpledge_OrganizationInformation_countdown_subscription'),1500);
				});
				jQuery('#clickandpledge_OrganizationInformation_subscription').keyup(function(){
					limitText(jQuery('#clickandpledge_OrganizationInformation_subscription'),jQuery('#clickandpledge_OrganizationInformation_countdown_subscription'),1500);
				});
				
				//TermsCondition
				jQuery('#clickandpledge_TermsCondition_subscription').keydown(function(){
					limitText(jQuery('#clickandpledge_TermsCondition_subscription'),jQuery('#clickandpledge_TermsCondition_countdown_subscription'),1500);
				});
				jQuery('#clickandpledge_TermsCondition_subscription').keyup(function(){
					limitText(jQuery('#clickandpledge_TermsCondition_subscription'),jQuery('#clickandpledge_TermsCondition_countdown_subscription'),1500);
				});
		   });
		</script>
			</table></td></tr>
			 
			 <?php  $cnppmptransactios = PMProGateway_clickandpledge::get_cnppmpaccountsinfo(); ?>
			 <tr   <?php if($gateway != "clickandpledge" && $cnppmptransactios<=0) { ?>style="display: none;"<?php } ?> id="cnpfrmregister" ><td colspan="2"><table width="100%">
				 <tr class=" gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
			<td colspan="2"><?php _e('<strong>Login</strong>', 'pmpro'); ?>
			</td>
		</tr>
	   <?php 
			 if ($cnppmptransactios > 0){?>
			
			 <tr  style="float: right;" class="cnppmpsettings"  <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>><td><a name="cnpsettings" href="#">Go to Settings</a></td></tr>
			 
	<?php }?>
	   <tr class="gateway gateway_clickandpledge clickandpledge_TermsCondition_subscription" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>>
		   <td  valign="top" colspan=2>	
				  <p>1. Enter the email address associated with your Click & Pledge account, and click on <strong>Get the Code</strong>.</p>
			  <p>2. Please check your email inbox for the Login Verification Code email.</p>
			  <p>3. Enter the provided code and click <strong>Login</strong>.</p>
			</th>
			
	   </tr>
	   <tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>><td colspan="2"><input type="textbox" id="cnp_emailid" placeholder="CONNECT User Name" name="cnp_emailid" maxlength="50" min="6" size="40" ></td></tr>
	    <tr id="cnploaderimage"  style="display: none" ><td colspan="2"><img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'loading.gif'; ?>"></td></tr>
	     <tr id="cnpcode" style="display: none"><td colspan="2"><input type="textbox" id="cnp_code" placeholder="Code" name="cnp_code" size="40"></td></tr>
	      <tr class="gateway gateway_clickandpledge" <?php if($gateway != "clickandpledge") { ?>style="display: none;"<?php } ?>><td colspan="2"> <input type="button" id="cnp_btncode" value="Get the code" name="cnp_btncode" ></td></tr>
	      <tr id="cnpmsgdesc" style="display: none">
			
			<td colspan="2">
						
							<span class="text-danger" style="color:red">Sorry but we cannot find the email in our system. Please try again.</span>
							<span class="text-success"></span>
							
						
			</td>
		</tr>
	  </table></td> </tr>
	  <script>
		
		function validateEmail($email) 
		{
			 var emailReg = /^([\w-\.\+]+@([\w-]+\.)+[\w-]{2,4})?$/;
			 return emailReg.test( $email );
		}
		jQuery('#cnp_emailid').on('keypress', function(e) {
        if (e.which == 32)
            return false;
    	});
		jQuery('#cnp_btncode').on('click', function() 
		 {  
		 	 if(jQuery('#cnp_btncode').val() == "Get the code")
			 {
			 var cnpemailid = jQuery('#cnp_emailid').val();
			//	var ajaxurl = "admin-ajax.php" 
			 if(jQuery('#cnp_emailid').val() != "" && validateEmail(cnpemailid))
			 {
				 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'cnp_getcode',
						'cnpemailid' : cnpemailid
					  },
					cache: false,
					beforeSend: function() {
					jQuery('tr#cnploaderimage').show();
					jQuery('#cnp_emailid').show();
					
					jQuery("#cnp_emailid").attr("disabled", "disabled"); 
					jQuery(".cnperror").hide();
					},
					complete: function() {
					jQuery('tr#cnploaderimage').hide();
						
					},	
				 success: function(htmlText) {
                 console.log(htmlText);
				 if((htmlText != "") ){
				  var htmlText = jQuery.parseJSON(htmlText);
				  //var obj = jQuery.parseJSON(htmlText);
				 
				  if(htmlText == "Code has been sent successfully")
				  {
				  jQuery("tr#cnpcode").show();

				  jQuery("#cnp_btncode").prop('value', 'Login');
				  jQuery("tr#cnpmsgdesc").show();
				  jQuery(".text-danger").html("");
				  jQuery(".text-success").html("");
				  jQuery(".text-success").html("<span style='color:green'><strong>Please enter the code sent to your email</strong></span>");
				  }
				} 
				  else  
				  {
				    jQuery("#cnp_emailid").prop('disabled', '');
				  	  jQuery("tr#cnpmsgdesc").show();
				  
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
                   console.log(xhr.responseText);
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
			  }
			  else{
			  alert("Please enter valid CONNECT user name");
			  jQuery('#cnp_emailid').focus();
			  return false;
			  }
			 }
			 if(jQuery('#cnp_btncode').val() == "Login")
			 {
			 	 var cnpemailid = jQuery('#cnp_emailid').val().trim();
				 var cnpcode    = jQuery('#cnp_code').val().trim();
			 if(cnpemailid != "" && cnpcode != "")
			 {
				 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'cnp_pmpgetaccounts',
						'cnpemailid' : cnpemailid,
					  	'cnpcode' : cnpcode
					  },
				  cache: false,
				  beforeSend: function() {
					jQuery("#cnp_btncode").prop('value', 'Loading....');
					jQuery("#cnp_btncode").prop('disabled', 'disabled');
					jQuery("#cnp_code").attr("disabled", "disabled"); 
					},
					complete: function() {
						//$('.cnploaderimage').hide();
						//$("#cnp_btncode").prop('value', 'Login');
					},	
				  success: function(htmlText) {
				
				  if(htmlText != "error")
				  {
				      jQuery('#cnp_emailid').val("");
					  jQuery('#cnp_code').val("");
  				  	  jQuery("tr#cnpcode").hide();
					  jQuery("#cnp_code").prop('disabled', '');
					  jQuery("#cnp_btncode").prop('disabled', '');
				      jQuery("#cnp_btncode").prop('value', 'Get the code');
					  location.reload();
					  jQuery("tr#cnpfrmsettings").show(); 
        			  console.log('add success');
				  }
				  else
				  {
				    jQuery(".text-danger").html("");
				    jQuery(".text-success").html("");
				    jQuery(".cnperror").show();
					jQuery("#cnp_code").prop('disabled', '');
				    jQuery(".text-danger").html("<span style='color:red'><strong>Invalid Code</strong></span>");
				    jQuery("#cnp_btncode").prop('value', 'Login');
					jQuery("#cnp_btncode").prop('disabled', false);
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
			  }
			 }
			 else if(jQuery('#cnp_emailid').val() == "")
			 {
			  alert("Please enter CONNECT user name");
			  return false;
			 }
		 
		
		 });
		
	</script>
		<?php
			
		}

		/**
		 * Filtering orders at checkout.
		 *
		 * @since 1.8
		 */
		 
		 static function cnp_custom_function ()
		 {
		// $clickandpledge_pmpro_gateways = PMProGateway_clickandpledge::pmpro_gateways('');
		 //print_r($clickandpledge_pmpro_gateways);
		 if (get_option('pmpro_gateway') == 'clickandpledge') {
		 	?>
	   <script>

		   jQuery(document).ready(function(){
			   

				function limitText(limitField, limitCount, limitNum) {
					re =  /[=\"\<\>\\\{\}]/;
					var isValid = re.test(limitField.val());
					if(isValid){ limitField.val(limitField.val().replace(/[=\"\<\>\\\{\}]/, "")); }
					if (limitField.val().length > limitNum) {
						limitField.val( limitField.val().substring(0, limitNum) );
					} else {
						limitCount.html (limitNum - limitField.val().length);
					}
				}
				limitText(jQuery('#clickandpledge_sku'),jQuery('#clickandpledge_sku_countdown_subscription'),100);
				
				//OrganizationInformation
				jQuery('#clickandpledge_sku').keyup(function(){
					limitText(jQuery('#clickandpledge_sku'),jQuery('#clickandpledge_sku_countdown_subscription'),100);
				});	
			   jQuery('#clickandpledge_sku').keydown(function(){
					limitText(jQuery('#clickandpledge_sku'),jQuery('#clickandpledge_sku_countdown_subscription'),100);
				});	
				function campaignlimitText(limitField, limitCount, limitNum) {
					
					var regex = new RegExp("^[a-zA-Z0-9-_]+$");
					var isValidcamp = regex.test(limitField.val());
					
   					
					if(!isValidcamp){ limitField.val(limitField.val().replace(/[^a-zA-Z0-9-_]/, "")); }
					if (limitField.val().length > limitNum) {
						limitField.val( limitField.val().substring(0, limitNum) );
					} else {
						limitCount.html (limitNum - limitField.val().length);
					}
				}
				campaignlimitText(jQuery('#clickandpledge_connect_campaign'),jQuery('#clickandpledge_campaign_countdown_subscription'),50);
				
				//OrganizationInformation
				jQuery('#clickandpledge_connect_campaign').keyup(function(){
					campaignlimitText(jQuery('#clickandpledge_connect_campaign'),jQuery('#clickandpledge_campaign_countdown_subscription'),50);
				});	
						
						
		});
		  
	</script>
			
			<h3 class="topborder"><?php _e('Click & Pledge Settings :', 'pmpro'); 
			 $temp_name = 'pmpro_clickandpledge_level_sku_'.$_GET['edit']; 
			 $connect_name = 'pmpro_clickandpledge_connect_campaign_'.$_GET['edit']; 
			 ?>
			 </h3>
			<table class="form-table">
				<tr>
					<th valign="top" scope="row"><label>SKU :</label></th><td><input id="clickandpledge_sku" type="text" name="pmpro_clickandpledge_level_sku_<?php echo $_GET['edit']; ?>" value="<?php echo get_option($temp_name); ?>" />
				<br />
				Maximum: 100 characters.<br>You have <span id="clickandpledge_sku_countdown_subscription">100</span> characters left.
				<div>Not allowed characters <code>{}="<>\</code></div>
				</td>
				</tr>
				
			</table>
		 <?php 
		 }
		 	return TRUE;
		 }
		 //save for SKU filed
		 static function cnp_custom_function_save ($saveid)
		 {
		 $get_options = get_option('pmpro_clickandpledge_level_sku_'.$saveid);
		 $campaign_options = get_option('pmpro_clickandpledge_connect_campaign_'.$saveid);
		 $get_value = $_REQUEST['pmpro_clickandpledge_level_sku_'.$saveid];
		 $campaign_value = $_REQUEST['pmpro_clickandpledge_connect_campaign_'.$saveid];
		 if ($get_options == TRUE || $get_value != NULL) {
			 update_option( 'pmpro_clickandpledge_level_sku_'.$saveid,$get_value );
		 } else {
		 	$get_value = $_REQUEST['pmpro_clickandpledge_level_sku_-1'];
		 	add_option( 'pmpro_clickandpledge_level_sku_'.$saveid, $get_value);
		 }
		  if ($campaign_options == TRUE || $campaign_value != NULL) {
			 update_option( 'pmpro_clickandpledge_connect_campaign_'.$saveid,$campaign_value );
		 } else {
		 	$campaign_value = $_REQUEST['pmpro_clickandpledge_connect_campaign_-1'];
		 	add_option( 'pmpro_clickandpledge_connect_campaign_'.$saveid, $campaign_value);
		 }
		 return $saveid;
		 }
		 
		static function pmpro_checkout_order($morder)
		{
			return $morder;
		}

		/**
		 * Code to run after checkout
		 *
		 * @since 1.8
		 */
		static function pmpro_after_checkout($user_id, $morder)
		{
		}
		
		/**
		 * Fields shown on edit user page
		 *
		 * @since 1.8
		 */
		static function user_profile_fields($user)
		{
		}

		/**
		 * Process fields from the edit user page
		 *
		 * @since 1.8
		 */
		static function user_profile_fields_save($user_id)
		{
		}

		/**
		 * Cron activation for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_activation()
		{
			wp_schedule_event(time(), 'daily', 'pmpro_cron_example_subscription_updates');
				
		}

		/**
		 * Cron deactivation for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_deactivation()
		{
			wp_clear_scheduled_hook('pmpro_cron_example_subscription_updates');
		}

		/**
		 * Cron job for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_cron_example_subscription_updates()
		{
		}

		function fetch_periodicity($cycle_period, $cycle_number)
		{
			$Periodicity = '';
			switch($cycle_period)
			{
				case 'Day':
					if(in_array($cycle_number, array(7,14,30,61,91,183,365))) {
						if($cycle_number == 7) {
						$Periodicity = 'Week';
						}
						elseif($cycle_number == 14) {
						$Periodicity = '2 Weeks';
						}
						elseif($cycle_number == 30) {
						$Periodicity = 'Month';
						}
						elseif($cycle_number == 61) {
						$Periodicity = '2 Months';
						}
						elseif($cycle_number == 91) {
						$Periodicity = 'Quarter';
						}elseif($cycle_number == 183) {
						$Periodicity = '6 Months';
						} else {
						$Periodicity = 'Year';
						}
					}
					else
					{
						$Periodicity = $cycle_number . " Days";
					}
				break;
				case 'Week':
					$days = $cycle_number; //This will convert week into days
					if(in_array($days, array(1,2,4,8,12,24,52))) {
						if($cycle_number == 1) {
						$Periodicity = 'Week';
						}
						elseif($cycle_number == 2) {
						$Periodicity = '2 Weeks';
						}
						elseif($cycle_number == 4) {
						$Periodicity = 'Month';
						}
						elseif($cycle_number == 8) {
						$Periodicity = '2 Months';
						}
						elseif($cycle_number == 12) {
						$Periodicity = 'Quarter';
						}
						elseif($cycle_number == 24) {
						$Periodicity = '6 Months';
						}
						elseif($cycle_number == 52) {
						$Periodicity = 'Year';
						}
					}
					else
					{
						$Periodicity = $cycle_number . " Week(s)";
					}
				break;
				case 'Month':
					if(in_array($cycle_number, array(1,2,3,6,12))) {
						if($cycle_number == 1) {
						$Periodicity = 'Month';
						}elseif($cycle_number == 2) {
						$Periodicity = '2 Months';
						}elseif($cycle_number == 3) {
						$Periodicity = 'Quarter';
						}elseif($cycle_number == 6) {
						$Periodicity = '6 Months';
						} else {
						$Periodicity = 'Year';
						}
					}
					else
					{
						$Periodicity = $cycle_number . " Months";
					}
				break;
				case 'Year':
					if(in_array($cycle_number, array(1))) {
						$Periodicity = 'Year';
					}
					else
					{
						$Periodicity = $cycle_number . " Years";
					}
				break;
				
			}
			return $Periodicity;
		}
		
	public	function process(&$order)
		{		
        
			if(!extension_loaded('soap')) {
				$order->error .= " " . __("SOAP Client is need to process C&P Transaction. Please contact administrator.", "pmpro");
				return false;
			}
			$currency = pmpro_getOption("currency");
			if(!in_array($currency,array('USD','EUR','CAD','GBP','HKD'))){
				$order->error .= " " . __("Click & Pledge does not allow <b>$currency</b> currency for transactions. Please contact administrator.", "pmpro");
				return false;
			}
		/*	$accepted_credit_cards = explode(',',pmpro_getOption("accepted_credit_cards"));
        $optionsarry = get_option('gfcnp_plugin');
       
			$avcrds = unserialize($optionsarry['available_cards']);
        
			$card_type = $order->cardtype;
			if(isset($accepted_credit_cards) && count($accepted_credit_cards) > 0) {
				if(!in_array($card_type, $accepted_credit_cards)) {
					$order->error .= " " . __("We are not accepting <b>$card_type</b> type card.", "pmpro");
					return false;
				}
			}		
			*/
			if(pmpro_getOption("clickandpledge_AccountID") == '') {
				$order->error .= " " . __("Click & Pledge payments settings wrong. Account ID not set properly. Please contact administrator.", "pmpro");
				return false;
			}			
			if(pmpro_isLevelRecurring($order->membership_level))
			{
				$Periodicity = '';
				$Periodicity = $this->fetch_periodicity($order->membership_level->cycle_period, $order->membership_level->cycle_number);
								
				if(!in_array($Periodicity, array('Week', '2 Weeks', 'Month', '2 Months', 'Quarter', '6 Months', 'Year'))) {
					$order->error .= " " . __("Click & Pledge does not allow <b>$Periodicity</b> for recurring. Please contact administrator.", "pmpro");
					return false;
				}
				
				if(floatval($order->PaymentAmount) == 0 && floatval($order->membership_level->trial_amount) == 0)
				{
					$order->error .= " " . __("Click & Pledge does not allow 0 amount for recurring. Please contact administrator.", "pmpro");
					return false;
				}				
			}
			//Transactions start			
			$result = $this->charge($order);
       
			if($result) {				
				//setup recurring billing
				if(pmpro_isLevelRecurring($order->membership_level))
				{	
					if(pmpro_isLevelTrial($order->membership_level) && floatval($order->membership_level->trial_amount) != 0)
					{
						//$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";
						$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $order->BillingFrequency . " " . $order->BillingPeriod)) . "T0:0:0";		
						$this->VaultGUID = $result['VaultGUID']; //1st Payment VaultGUID
						$this->TransactionNumber = $result['TransactionNumber']; //1st Payment Transaction Number
						
						$trialauth = $this->authorize($order);
                   // $trialauth = $this->authorize($order, 'trial',$this->VaultGUID,$this->TransactionNumber);
						if( $trialauth )
						{
							$order->notes = "Trial Period Transaction ID : " . $trialauth['TransactionNumber'];	
							$order->updateStatus("authorized");
							$order->status = "success";	
							if(floatval($order->PaymentAmount) == 0) {
								return true;
							}
							
														
							$trial_period_days = (ceil(abs(strtotime(date("Y-m-d")) - strtotime($order->ProfileStartDate)) / 86400));							
							if(!empty($order->TrialBillingCycles))						
							{							
								$trialOccurrences = (int)$order->TrialBillingCycles;
								
								// If Billing Period less then trial billing period
								if((int)$order->TotalBillingCycles!=0 && (int)$order->TotalBillingCycles<$trialOccurrences)
								{
									return true;
								}
								
								
								if($order->TrialBillingPeriod == "Year")
									$trial_period_days = $trial_period_days + (365 * $order->TrialBillingFrequency * $trialOccurrences);	//annual
								elseif($order->BillingPeriod == "Day")
									$trial_period_days = $trial_period_days + (1 * $order->TrialBillingFrequency * $trialOccurrences);		//daily
								elseif($order->BillingPeriod == "Week")
									$trial_period_days = $trial_period_days + (7 * $order->TrialBillingFrequency * $trialOccurrences);	//weekly
								else
									$trial_period_days = $trial_period_days + (30 * $order->TrialBillingFrequency * $trialOccurrences);	//assume monthly				
							}
							
							
							//add a period to the start date to account for the initial payment
							$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $trial_period_days . " Day")) . "T0:0:0";
							
							
							if(floatval($order->PaymentAmount) != 0) 
							{

								
								if($order->membership_level->billing_limit!=0)
								{	
									 $order->TotalBillingCycles = (int)$order->TotalBillingCycles-(int)$order->TrialBillingCycles;
									
									if($order->TotalBillingCycles == 0)
										return true;
								}
								else if((int)$order->TrialBillingCycles==999)
								{
										return true;
								}
                            $auth = $this->authorize($order); //Recurring
							//	$auth = $this->authorize($order, 'authorize',$this->VaultGUID,$this->TransactionNumber); //Recurring
								
								if( $auth )
								{
									//$order->subscription_transaction_id = $auth['VaultGUID'];
									$order->subscription_transaction_id = $auth['TransactionNumber'];
									$order->updateStatus("authorized");
									$order->status = "success";	
									
									return true;
								}
								else
								{
									if(empty($order->error))
									$order->error = __("Unknown error: Payment failed.", "pmpro");
									$order->error .= " " . __("partial payment was made that we could not refund. Please contact the site owner immediately to correct this.", "pmpro");
									return false;
								}
							}
						}
						else
						{
							if(empty($order->error))
							$order->error = __("Unknown error: Payment failed.", "pmpro");
							$order->error .= " " . __("1A partial payment was made that we could not refund. Please contact the site owner immediately to correct this.", "pmpro");
							return false;
						}
					}
					else
					{
						//add a period to the start date to account for the initial payment
						$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $order->BillingFrequency . " " . $order->BillingPeriod)) . "T0:0:0";				
					}
					$order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);
					
					$this->VaultGUID = $result['VaultGUID'];
					$this->TransactionNumber = $result['TransactionNumber'];					
					
				//	$auth = $this->authorize($order, 'authorize',$this->VaultGUID,$this->TransactionNumber);
                $auth = $this->authorize($order);
					
					if( $auth )
					{
						$order->subscription_transaction_id = $auth['TransactionNumber'];
						$order->updateStatus("authorized");
						$order->status = "success";	
						return true;
					}
					else
					{
						if(empty($order->error))
						$order->error = __("Unknown error: Payment failed.", "pmpro");
						$order->error .= " " . __("2A partial payment was made that we could not refund. Please contact the site owner immediately to correct this.", "pmpro");
						return false;
					}
				}
				else
				{
                //only a one time charge
					$order->status = "success";	//saved on checkout page										
					return true;
				}
			}
			else
			{					
				if(empty($order->error))
					$order->error = __("Unknown error: Payment failed.", "pmpro");
				
				return false;
			}			
		}
		
		/*
			Run an authorization at the gateway.

			Required if supporting recurring subscriptions
			since we'll authorize $1 for subscriptions
			with a $0 initial payment.
		*/
		public function authorize(&$order)
		{
          global $payinc;
          if(pmpro_isLevelTrial($order->membership_level) && floatval($order->membership_level->trial_amount) != 0 && $payinc == "1")
			{
          $case = "trial"; $payinc++;
           } 
        else{ $case = "authorize"; } 
      	 	 $cnpVaultGUID = $order->Gateway->VaultGUID;
       		 $cnpTransactionNumber = $order->Gateway->TransactionNumber;
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			if(!empty($order->gateway_environment))
				$gateway_environment = $order->gateway_environment;
			if(empty($gateway_environment))
				$gateway_environment = pmpro_getOption("gateway_environment");
			if($gateway_environment == "live")
				$mode = "Production";		
			else
				$mode = "Test";	
			
			$xml = $this->getPaymentXML( $order, $cnpVaultGUID,$cnpTransactionNumber,$case);
			
			$response = $this->sendPayment( $xml );
			if($response === FALSE)
			{
				$order->error = 'We are unable to connect Click & Pledge. Please try after some time.';
				$order->shorterror = 'We are unable to connect Click & Pledge. Please try after some time.';
				return false;
			}
			$ResultCode = $response->OperationResult->ResultCode;
			$transation_number = $response->OperationResult->TransactionNumber;
			$VaultGUID = $response->OperationResult->VaultGUID;
			if ($ResultCode == '0') {
				// transaction was successful, so record transaction number and continue
				//$order->payment_transaction_id = $transation_number;
			//	$order->updateStatus("authorized");
				$return = array(
					'VaultGUID' => $VaultGUID,
					'TransactionNumber' => $transation_number,					
				);
				return $return;				
			}
			else {
				$order->errorcode = true;
				if( in_array( $ResultCode, array( 2051,2052,2053 ) ) )
				{
					$AdditionalInfo = $response->OperationResult->AdditionalInfo;
				}
				else
				{
					if( isset( $this->responsecodes[$ResultCode] ) )
					{
						$AdditionalInfo = $this->responsecodes[$ResultCode];
					}
					else
					{
						$AdditionalInfo = 'Unknown error ('.$ResultCode.')';
					}
				}
				$order->error      = $AdditionalInfo;
				$order->shorterror = $AdditionalInfo;
				return false;
			}															
			return true;					
		}
		
		/*
			Void a transaction at the gateway.

			Required if supporting recurring transactions
			as we void the authorization test on subs
			with a $0 initial payment and void the initial
			payment if subscription setup fails.
		*/
		function void(&$order)
		{
			//need a transaction id
			if(empty($order->payment_transaction_id))
				return false;
			
			//code to void an order at the gateway and test results would go here

			//simulate a successful void
			$order->payment_transaction_id = "TEST" . $order->code;
			$order->updateStatus("voided");					
			return true;
		}

		private function sendPayment($xml) {			
			$connect = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
			$client = new SoapClient('https://paas.cloud.clickandpledge.com/paymentservice.svc?wsdl', $connect);
			$soapParams = array('instruction'=>$xml);		 
			$response = $client->Operation($soapParams);
			return $response;
		}		
		
		/*
			Make a charge at the gateway.

			Required to charge initial payments.
		*/
		function charge(&$order)
		{
			
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			$xml = $this->getPaymentXML( $order,"","", 'charge');
				
			$response = $this->sendPayment( $xml );
			
			if($response === FALSE)
			{
				$order->error = 'We are unable to connect Click & Pledge. Please try after some time.';
				$order->shorterror = 'We are unable to connect Click & Pledge. Please try after some time.';
				return false;
			}
			$ResultCode        = $response->OperationResult->ResultCode;
			$transation_number = $response->OperationResult->TransactionNumber;
			$VaultGUID         = $response->OperationResult->VaultGUID;
			if ($ResultCode == '0') {
				// transaction was successful, so record transaction number and continue
				$order->payment_transaction_id = $transation_number;
				$order->updateStatus("success");
				$return = array(
					'VaultGUID' => $VaultGUID,
					'TransactionNumber' => $transation_number,					
				);
				return $return;				
			}
			else {
				$order->errorcode = true;
				if( in_array( $ResultCode, array( 2051,2052,2053 ) ) )
				{
					$AdditionalInfo = $response->OperationResult->AdditionalInfo;
				}
				else
				{
					if( isset( $this->responsecodes[$ResultCode] ) )
					{
						$AdditionalInfo = $this->responsecodes[$ResultCode];
					}
					else
					{
						$AdditionalInfo = 'Unknown error ('.$ResultCode.')';
					}
				}
				$order->error = $AdditionalInfo;
				$order->shorterror = $AdditionalInfo;
				return false;
			}
			//code to charge with gateway and test results would go here
			//simulate a successful charge
			//$order->payment_transaction_id = "TEST" . $order->code;
			//$order->updateStatus("success");					
			return true;						
		}
		
		function safeString( $str,  $length=1, $start=0 )
		{
			$str = preg_replace('/\x03/', '', $str); //Remove new line characters
			return substr( htmlspecialchars_decode( ( $str ) ), $start, $length );
		}
		
		function search_country( $country )
		{
			foreach ($this->country_code as $cname => $code)
			{
				if ($cname == $country)
					return $code;
			}
		}
		
		/**
	     * Get user's IP address
	     */
		function get_user_ip() {
			$ipaddress = '';
			 if (isset($_SERVER['HTTP_CLIENT_IP']))
				 $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			 else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				 $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			 else if(isset($_SERVER['HTTP_X_FORWARDED']))
				 $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			 else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
				 $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			 else if(isset($_SERVER['HTTP_FORWARDED']))
				 $ipaddress = $_SERVER['HTTP_FORWARDED'];
			 else
				 $ipaddress = $_SERVER['REMOTE_ADDR'];
			$parts = explode(',', $ipaddress);
			if((isset($parts) && count($parts) > 1)) $ipaddress = $parts[0];
			 return $ipaddress; 
		}
		public function getPaymentXML( $orderplaced, $cnpVaultGUID,$cnpTransactionNumber,$case = '')
		{ 
		    /* print_r($orderplaced);exit;
			echo $orderplaced->membership_level->expiration_number." ".$orderplaced->membership_level->expiration_period."s";
			 echo $some_time = strtotime($orderplaced->membership_level->expiration_number." ".$orderplaced->membership_level->expiration_period."s");
			echo date('Y-m-d 00:00:00','1562005800')."<br>";
			echo strtotime('2019-07-01 00:00:00')."<br>";
			echo date('Y-m-d 00:00:00',strtotime('2019-07-01 00:00:00'));
			echo date_i18n(get_option('date_format'), $some_time);*/
       
			$morder = new MemberOrder();
			$tax_state_value = 0;
			

			if($orderplaced->billing->country=='US' && pmpro_getOption('tax_state')==$orderplaced->billing->state)
			{
				 $tax_state_value = pmpro_getOption('tax_rate');
			}
			
			$cnpversion = "4.24080000-WP6.6.1-PMP3.1.3";
			$dom = new DOMDocument('1.0', 'UTF-8');
			$root = $dom->createElement('CnPAPI', '');
			$root->setAttribute("xmlns","urn:APISchema.xsd");
			$root = $dom->appendChild($root);
			$version=$dom->createElement("Version","1.5");
			$version=$root->appendChild($version);
			$engine = $dom->createElement('Engine', '');
			$engine = $root->appendChild($engine);
			$application = $dom->createElement('Application','');
			$application = $engine->appendChild($application);
			$applicationid=$dom->createElement('ID','CnP_WordPress_PaidMembershipsPro');
			$applicationid=$application->appendChild($applicationid);
			$applicationname=$dom->createElement('Name','CnP_WordPress_PaidMembershipsPro'); 
			$applicationid=$application->appendChild($applicationname);
			$applicationversion=$dom->createElement('Version',$cnpversion);
			$applicationversion=$application->appendChild($applicationversion);
		
			$request = $dom->createElement('Request', '');
			$request = $engine->appendChild($request);
			$operation = $dom->createElement('Operation','');
			$operation = $request->appendChild( $operation );
			$operationtype = $dom->createElement('OperationType','Transaction');
			$operationtype = $operation->appendChild($operationtype);
			
			if($this->get_user_ip() != '') {
				$ipaddress=$dom->createElement('IPAddress',$this->get_user_ip());
				$ipaddress=$operation->appendChild($ipaddress);
			}
			
			$httpreferrer=$dom->createElement('UrlReferrer',htmlentities($_SERVER['HTTP_REFERER']));
			$httpreferrer=$operation->appendChild($httpreferrer);
			
			$authentication=$dom->createElement('Authentication','');
			$authentication=$request->appendChild($authentication);
			if(pmpro_getOption("clickandpledge_AccountGuid") == "")
			{
				$PMPAccountGuid = self::getCnPPMPAccountGUID(pmpro_getOption("clickandpledge_AccountID"));
			}
			else
			{
				$PMPAccountGuid = pmpro_getOption("clickandpledge_AccountGuid");
			}
		
			$accounttype=$dom->createElement('AccountGuid',$PMPAccountGuid ); 
			$accounttype=$authentication->appendChild($accounttype);
			
			$accountid=$dom->createElement('AccountID',pmpro_getOption("clickandpledge_AccountID") );
			$accountid=$authentication->appendChild($accountid);
			 
			$order=$dom->createElement('Order','');
			$order=$request->appendChild($order);
			
			if(empty($order->gateway_environment))
				$gateway_environment = pmpro_getOption("gateway_environment");
			else
				$gateway_environment = $order->gateway_environment;
			if($gateway_environment == "live")
				$mode = "Production";		
			else
				$mode = "Test";
			
			$ordermode=$dom->createElement('OrderMode',$mode);
			$ordermode=$order->appendChild($ordermode);				
			$clickandpledge_email_customercc = pmpro_getOption("clickandpledge_email_customer");
			$clickandpledge_email_customer_recurringcc = pmpro_getOption("clickandpledge_email_customer_recurring");
			$clickandpledge_email_customer_trialcc = pmpro_getOption("clickandpledge_email_customer_trial");
			
			if ($clickandpledge_email_customercc == '1' && $case == 'charge') 
			{
			if (get_option('pmpro_clickandpledge_connect_campaign') ) {

				/*$trans_cmp = $dom->createElement('ConnectCampaignAlias', substr(get_option('pmpro_clickandpledge_connect_campaign'), 0, 50));
				$trans_cmp = $order->appendChild($trans_cmp);*/
				
				$trans_cmp  = $dom->createElement('ConnectCampaignAlias','');
				$trans_cmp  = $order->appendChild($trans_cmp);
				$trans_cmp->appendChild($dom->createCDATASection(substr(get_option('pmpro_clickandpledge_connect_campaign'), 0, 50)));
				
				}
			}
			elseif ($clickandpledge_email_customer_recurringcc == '1' && $case == 'authorize') {
				if (get_option('pmpro_clickandpledge_connect_campaign_subscription') ) {
				
				$trans_cmp  = $dom->createElement('ConnectCampaignAlias','');
				$trans_cmp  = $order->appendChild($trans_cmp);
				$trans_cmp->appendChild($dom->createCDATASection(substr(get_option('pmpro_clickandpledge_connect_campaign_subscription'), 0, 50)));
				
					/*$trans_cmp = $dom->createElement('ConnectCampaignAlias', substr(get_option('pmpro_clickandpledge_connect_campaign_subscription'), 0, 50));
				$trans_cmp = $order->appendChild($trans_cmp);*/
					
				}
			}
			elseif ($clickandpledge_email_customer_trialcc == '1' && $case == 'trial') {
				
				if (get_option('pmpro_clickandpledge_connect_campaign_trial') ) {
				
				$trans_cmp  = $dom->createElement('ConnectCampaignAlias','');
				$trans_cmp  = $order->appendChild($trans_cmp);
				$trans_cmp->appendChild($dom->createCDATASection(substr(get_option('pmpro_clickandpledge_connect_campaign_trial'), 0, 50)));
				/*$trans_cmp = $dom->createElement('ConnectCampaignAlias', substr(get_option('pmpro_clickandpledge_connect_campaign_trial'), 0, 50));
				$trans_cmp = $order->appendChild($trans_cmp);*/
				}
			}
			//}							
			$cardholder=$dom->createElement('CardHolder','');
			$cardholder=$order->appendChild($cardholder);
			
			if(isset($orderplaced->billing)) {
			$billinginfo=$dom->createElement('BillingInformation','');
			$billinginfo=$cardholder->appendChild($billinginfo);
			
			if($orderplaced->FirstName) {
				$billfirst_name  = $dom->createElement('BillingFirstName','');
				$billfirst_name  = $billinginfo->appendChild($billfirst_name);
				$billfirst_name->appendChild($dom->createCDATASection($this->safeString($orderplaced->FirstName,50)));
			
			}			
		
			if($orderplaced->LastName) {	
			/*$billlast_name=$dom->createElement('BillingLastName',$this->safeString($orderplaced->LastName,50));
			$billlast_name=$billinginfo->appendChild($billlast_name);*/
				
				$billlast_name  = $dom->createElement('BillingLastName','');
				$billlast_name  = $billinginfo->appendChild($billlast_name);
				$billlast_name->appendChild($dom->createCDATASection($this->safeString($orderplaced->LastName,50)));
			}
			if (isset($orderplaced->Email) && $orderplaced->Email != '')
			{
/*				$bill_email=$dom->createElement('BillingEmail',$orderplaced->Email);
				$bill_email=$billinginfo->appendChild($bill_email);*/
				
				$bill_email  = $dom->createElement('BillingEmail','');
				$bill_email  = $billinginfo->appendChild($bill_email);
				$bill_email->appendChild($dom->createCDATASection($this->safeString($orderplaced->Email,50)));
				
				
			}
						
			if( $orderplaced->billing->phone != '' )
			{
				/*$bill_phone=$dom->createElement('BillingPhone',$this->safeString($orderplaced->billing->phone, 50));
				$bill_phone=$billinginfo->appendChild($bill_phone);*/
				
				$bill_phone  = $dom->createElement('BillingPhone','');
				$bill_phone  = $billinginfo->appendChild($bill_phone);
				$bill_phone->appendChild($dom->createCDATASection($this->safeString($orderplaced->billing->phone,50)));
				
			}
			} //Billing Information
			
			
			if( $orderplaced->Address1 != '' ) {		
			$billingaddress=$dom->createElement('BillingAddress','');
			$billingaddress=$cardholder->appendChild($billingaddress);
			
			if( $orderplaced->Address1 != '' ) {
				$billingaddress1  = $dom->createElement('BillingAddress1','');
				$billingaddress1  = $billingaddress->appendChild($billingaddress1);
				$billingaddress1->appendChild($dom->createCDATASection($this->safeString($orderplaced->Address1,100)));
				
				/*$billingaddress1=$dom->createElement('BillingAddress1',$this->safeString($orderplaced->Address1,100));
				$billingaddress1=$billingaddress->appendChild($billingaddress1);*/
				
			}
			if( $orderplaced->Address2 != '' ) {
				$billingaddress2  = $dom->createElement('BillingAddress2','');
				$billingaddress2  = $billingaddress->appendChild($billingaddress2);
				$billingaddress2->appendChild($dom->createCDATASection($this->safeString($orderplaced->Address2,100)));
				
				/*$billingaddress2=$dom->createElement('BillingAddress2',$this->safeString($orderplaced->Address2,100));
			$billingaddress2=$billingaddress->appendChild($billingaddress2);*/
			}
			
			if(!empty($orderplaced->billing->city)) {
				$billing_city  = $dom->createElement('BillingCity','');
				$billing_city  = $billingaddress->appendChild($billing_city);
				$billing_city->appendChild($dom->createCDATASection($this->safeString($orderplaced->billing->city,50)));
				
			/*$billing_city=$dom->createElement('BillingCity',$this->safeString($orderplaced->billing->city,50));
			$billing_city=$billingaddress->appendChild($billing_city);*/
			}
			if(!empty($orderplaced->billing->state)) {
			/*$billing_state=$dom->createElement('BillingStateProvince',$this->safeString($orderplaced->billing->state,50));
			$billing_state=$billingaddress->appendChild($billing_state);*/
				
					$billing_state  = $dom->createElement('BillingStateProvince','');
				$billing_state  = $billingaddress->appendChild($billing_state);
				$billing_state->appendChild($dom->createCDATASection($this->safeString($orderplaced->billing->state,50)));
			}
			
			if(!empty($orderplaced->billing->zip)) {		
			/*$billing_zip=$dom->createElement('BillingPostalCode',$this->safeString( $orderplaced->billing->zip,20 ));
			$billing_zip=$billingaddress->appendChild($billing_zip);*/

			$billing_zip  = $dom->createElement('BillingPostalCode','');
			$billing_zip  = $billingaddress->appendChild($billing_zip);
			$billing_zip->appendChild($dom->createCDATASection($this->safeString($orderplaced->billing->zip,20)));
			}
		
		if(!empty($orderplaced->billing->country)) {
		$billing_country_id = '';
		if(ini_get('allow_url_fopen')) {//To check if fopen is "ON"
			
			$countries = simplexml_load_file( plugin_dir_path( __FILE__ ).'Countries.xml' );	
		
			foreach( $countries as $country ){
				if( $country->attributes()->Abbrev == $orderplaced->billing->country ){
					$billing_country_id = $country->attributes()->Code;
				} 
			}
		}
		if($billing_country_id) {
		$billing_country=$dom->createElement('BillingCountryCode',str_pad($billing_country_id, 3, "0", STR_PAD_LEFT));
		$billing_country=$billingaddress->appendChild($billing_country);
		}
		}
		
			} //Billing Address
		
		 //Shipping Address
		if(sanitize_text_field($_REQUEST['sfirstname'])!== "") {
		//if(isset($orderplaced->order_shipping)) {
			
		if( sanitize_text_field($_REQUEST['saddress1'])!=""  &&  sanitize_text_field($_REQUEST['scity'])!="" && sanitize_text_field($_REQUEST['scountry'])!="" )
		{
			$shippinginfo=$dom->createElement('ShippingInformation','');
			$shippinginfo=$cardholder->appendChild($shippinginfo);
			
			//Newly Added
			$ShippingContactInformation=$dom->createElement('ShippingContactInformation','');
			$ShippingContactInformation=$shippinginfo->appendChild($ShippingContactInformation);
			
			if( sanitize_text_field($_REQUEST['sfirstname'])!="" )
			{
				/*$shipping_first_name=$dom->createElement('ShippingFirstName',$this->safeString(sanitize_text_field($_REQUEST['sfirstname']),50));
				$shipping_first_name=$ShippingContactInformation->appendChild($shipping_first_name);*/
				
				$shipping_first_name  = $dom->createElement('ShippingFirstName','');
				$shipping_first_name  = $ShippingContactInformation->appendChild($shipping_first_name);
				$shipping_first_name->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['sfirstname']),50)));
			}
			
			if( sanitize_text_field($_REQUEST['slastname'])!=""  )
			{
				/*$shipping_last_name=$dom->createElement('ShippingLastName',$this->safeString(sanitize_text_field($_REQUEST['slastname']),50));
				$shipping_last_name=$ShippingContactInformation->appendChild($shipping_last_name);*/
				
				$shipping_last_name  = $dom->createElement('ShippingLastName','');
				$shipping_last_name  = $ShippingContactInformation->appendChild($shipping_last_name);
				$shipping_last_name->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['slastname']),50)));
			}
									
			$shippingaddress=$dom->createElement('ShippingAddress','');
			$shippingaddress=$shippinginfo->appendChild($shippingaddress);
			
			if( sanitize_text_field($_REQUEST['saddress1'])!=""  )
			{
				/*$ship_address1=$dom->createElement('ShippingAddress1',$this->safeString(sanitize_text_field($_REQUEST['saddress1']),100));
				$ship_address1=$shippingaddress->appendChild($ship_address1);*/
				
				$ship_address1  = $dom->createElement('ShippingAddress1','');
				$ship_address1  = $shippingaddress->appendChild($ship_address1);
				$ship_address1->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['saddress1']),100)));
			}

			if( sanitize_text_field($_REQUEST['saddress2'])!="" )
			{
				/*$ship_address2=$dom->createElement('ShippingAddress2',$this->safeString(sanitize_text_field($_REQUEST['saddress2']),100));
				$ship_address2=$shippingaddress->appendChild($ship_address2);*/
				
				$ship_address2  = $dom->createElement('ShippingAddress2','');
				$ship_address2  = $shippingaddress->appendChild($ship_address2);
				$ship_address2->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['saddress2']),100)));
			}

			if( sanitize_text_field($_REQUEST['scity'])!="" )
			{
			/*	$ship_city=$dom->createElement('ShippingCity',$this->safeString(sanitize_text_field($_REQUEST['scity']), 50));
				$ship_city=$shippingaddress->appendChild($ship_city);*/
				
				$ship_city  = $dom->createElement('ShippingCity','');
				$ship_city  = $shippingaddress->appendChild($ship_city);
				$ship_city->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['scity']),50)));
			}

			if( sanitize_text_field($_REQUEST['sstate'])!="" )
			{
				/*$ship_state=$dom->createElement('ShippingStateProvince',$this->safeString(sanitize_text_field($_REQUEST['sstate']), 50));
				$ship_state=$shippingaddress->appendChild($ship_state);*/
				
				$ship_state  = $dom->createElement('ShippingStateProvince','');
				$ship_state  = $shippingaddress->appendChild($ship_state);
				$ship_state->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['sstate']),50)));
			}
			
			if( sanitize_text_field($_REQUEST['szipcode'])!="" )
			{
				/*$ship_zip=$dom->createElement('ShippingPostalCode',$this->safeString(sanitize_text_field($_REQUEST['szipcode']), 20));
				$ship_zip=$shippingaddress->appendChild($ship_zip);*/


			$ship_zip  = $dom->createElement('ShippingPostalCode','');
			$ship_zip  = $shippingaddress->appendChild($ship_zip);
			$ship_zip->appendChild($dom->createCDATASection($this->safeString(sanitize_text_field($_REQUEST['szipcode']),20)));
			}

			if( sanitize_text_field($_REQUEST['scountry'])!="" )
			{
				$shipping_country = '';
				if(ini_get('allow_url_fopen')) //To check if fopen is "ON"
				{
					foreach( $countries as $country ){
						if( $country->attributes()->Abbrev == sanitize_text_field($_REQUEST['scountry']) ){
							$shipping_country = $country->attributes()->Code;
						} 
					}
				}
				
				if($shipping_country)
				{
				$ship_country=$dom->createElement('ShippingCountryCode',str_pad($shipping_country, 3, "0", STR_PAD_LEFT));
				$ship_country=$shippingaddress->appendChild($ship_country);
				}
				else
				{
					$shipping_country = $this->search_country(sanitize_text_field($_REQUEST['scountry']));
					if($shipping_country) {
						$ship_country=$dom->createElement('ShippingCountryCode',str_pad($shipping_country, 3, "0", STR_PAD_LEFT));
						$ship_country=$shippingaddress->appendChild($ship_country);
					}
				}
			}
		}//End of Shipping Address node
		}
		 
			if($orderplaced->membership_level->expiration_number != "" && $orderplaced->membership_level->expiration_number != 0){
			$customfieldlist = $dom->createElement('CustomFieldList','');
			 $customfieldlist = $cardholder->appendChild($customfieldlist);
		 
			
		    $customfield = $dom->createElement('CustomField','');
		    $customfield = $customfieldlist->appendChild($customfield);
				
			$fieldname = $dom->createElement('FieldName',"Membership Expiration");
			$fieldname = $customfield->appendChild($fieldname);
					
			
			 $some_time = strtotime($orderplaced->membership_level->expiration_number." ".$orderplaced->membership_level->expiration_period."s");
		
					 $newsignupexp = date('Y-m-d', $some_time);
					$signupDate = strtotime($newsignupexp ." -5 hours");//}
				
			$fieldvalue = $dom->createElement('FieldValue',date('d F, Y', $signupDate));
			$fieldvalue = $customfield->appendChild($fieldvalue);
		 
		 } 
			$VaultGUID = $this->VaultGUID;
			$paymentmethod=$dom->createElement('PaymentMethod','');
			$paymentmethod=$cardholder->appendChild($paymentmethod);
			if($cnpVaultGUID == ""){
			$payment_type=$dom->createElement('PaymentType','CreditCard');
			$payment_type=$paymentmethod->appendChild($payment_type);
			$creditcard=$dom->createElement('CreditCard','');
			$creditcard=$paymentmethod->appendChild($creditcard);
			$credit_card_name = $orderplaced->billing->name;						
			$credit_name  = $dom->createElement('NameOnCard','');
			$credit_name  = $creditcard->appendChild($credit_name);
			$credit_name->appendChild($dom->createCDATASection($this->safeString($credit_card_name,50)));
			$credit_number=$dom->createElement('CardNumber',$this->safeString( str_replace(' ', '', $orderplaced->accountnumber), 17));
			$credit_number=$creditcard->appendChild($credit_number);
			$credit_cvv=$dom->createElement('Cvv2',$orderplaced->CVV2);
			$credit_cvv=$creditcard->appendChild($credit_cvv);
			$credit_expdate=$dom->createElement('ExpirationDate',str_pad($orderplaced->expirationmonth,2,'0',STR_PAD_LEFT) ."/" .substr($orderplaced->expirationyear,2,2));
			$credit_expdate=$creditcard->appendChild($credit_expdate);
		}
		else
		{
			$payment_type=$dom->createElement('PaymentType','ReferenceTransaction');
			$payment_type=$paymentmethod->appendChild($payment_type);
			$ReferenceTransaction=$dom->createElement('ReferenceTransaction','');
			$ReferenceTransaction=$paymentmethod->appendChild($ReferenceTransaction);
			$OrderNumber=$dom->createElement('OrderNumber',$cnpTransactionNumber);
			$OrderNumber=$ReferenceTransaction->appendChild($OrderNumber);
			$VaultGUID=$dom->createElement('VaultGUID',$cnpVaultGUID);
			$VaultGUID=$ReferenceTransaction->appendChild($VaultGUID);
		
		}
		
			
			$total_calculate = 0;
			
			if(isset($orderplaced->membership_level) && $orderplaced->membership_level->id !="")
			{
				$orderitemlist=$dom->createElement('OrderItemList','');
				$orderitemlist=$order->appendChild($orderitemlist);		
				$p = 101200;
				
				$orderitem=$dom->createElement('OrderItem','');
				$orderitem=$orderitemlist->appendChild($orderitem);
				$itemid=$dom->createElement('ItemID',($p+1));
				$itemid=$orderitem->appendChild($itemid);				
			
			
				$itemname  = $dom->createElement('ItemName','');
				$itemname  = $orderitem->appendChild($itemname);
				$itemname->appendChild($dom->createCDATASection($this->safeString(substr($orderplaced->membership_level->name,0,100),100)));
				
				$quntity=$dom->createElement('Quantity',1);
				$quntity=$orderitem->appendChild($quntity);					
				
				if(pmpro_isLevelRecurring($orderplaced->membership_level) && $case == 'authorize') {	
					$unitprice=$dom->createElement('UnitPrice',($orderplaced->PaymentAmount*100));
				}else if(pmpro_isLevelRecurring($orderplaced->membership_level) && pmpro_isLevelTrial($orderplaced->membership_level) && $case == 'trial') {	
					$unitprice=$dom->createElement('UnitPrice',($orderplaced->TrialAmount*100));
				} else {
					$unitprice=$dom->createElement('UnitPrice',($orderplaced->InitialPayment*100));
				}				
				$unitprice=$orderitem->appendChild($unitprice);
				
				if ( get_option('pmpro_clickandpledge_level_sku_'.$orderplaced->membership_id) ) {
					
			
				
					 $sku_code  = $dom->createElement('SKU','');
					 $sku_code  = $orderitem->appendChild($sku_code);
					 $sku_code->appendChild($dom->createCDATASection(substr(get_option('pmpro_clickandpledge_level_sku_'.$orderplaced->membership_id), 0, 100)));
					
				}
				
			}
				
		
			if(sanitize_text_field($_REQUEST['sfirstname'])!== "") {
		
		
		if( sanitize_text_field($_REQUEST['saddress1'])!=""  &&  sanitize_text_field($_REQUEST['scity'])!="" && sanitize_text_field($_REQUEST['scountry'])!="" )
		{
			$shipping=$dom->createElement('Shipping','');
			$shipping=$order->appendChild($shipping);
			$shipping_method=$dom->createElement('ShippingMethod',"Shipping");
			$shipping_method=$shipping->appendChild($shipping_method);
			$shipping_value = $dom->createElement('ShippingValue', "0");
			$shipping_value=$shipping->appendChild($shipping_value);	
		}
			}
			 $clickandpledge_email_customer = pmpro_getOption("clickandpledge_email_customer");
		 	 $clickandpledge_email_customer_recurring = pmpro_getOption("clickandpledge_email_customer_recurring");
			 $clickandpledge_email_customer_trial = pmpro_getOption("clickandpledge_email_customer_trial");
		     $receipt = $dom->createElement('Receipt','');
			 $receipt = $order->appendChild($receipt);
			
							
						if($clickandpledge_email_customer == '1' && $case == 'charge' )
						{
						
						   $email_sendreceipt =$dom->createElement('SendReceipt',"true");
						   $email_sendreceipt=$receipt->appendChild($email_sendreceipt);
						}
						elseif($clickandpledge_email_customer != '1' && $case == 'charge'){
						  $email_sendreceipt=$dom->createElement('SendReceipt',"false");
						  $email_sendreceipt=$receipt->appendChild($email_sendreceipt);		
						}	
						 if($clickandpledge_email_customer_recurring == '1' && $case == 'authorize') 
						{
							$email_sendreceipt =$dom->createElement('SendReceipt',"true");
						    $email_sendreceipt=$receipt->appendChild($email_sendreceipt);
						}
					   elseif($clickandpledge_email_customer_recurring != '1' && $case == 'authorize'){
						  $email_sendreceipt=$dom->createElement('SendReceipt',"false");
						  $email_sendreceipt=$receipt->appendChild($email_sendreceipt);		
						}	
						if($clickandpledge_email_customer_trial == '1' && $case == 'trial')
						{
							$email_sendreceipt =$dom->createElement('SendReceipt',"true");
						   $email_sendreceipt=$receipt->appendChild($email_sendreceipt);
							
						}elseif($clickandpledge_email_customer_trial != '1' && $case == 'trial'){
						  $email_sendreceipt = $dom->createElement('SendReceipt',"false");
						  $email_sendreceipt = $receipt->appendChild($email_sendreceipt);		
						}	
		 				  $recipt_lang = $dom->createElement('Language','ENG');			
						  $recipt_lang = $receipt->appendChild($recipt_lang);
			
			if($clickandpledge_email_customer == '1' || $clickandpledge_email_customer_recurring == '1' || $clickandpledge_email_customer_trial == '1') 
			{
				       
				
				if(isset($orderplaced->Email) && $orderplaced->Email != '')
				{
					if ($clickandpledge_email_customer == '1' && $case == 'charge') 
					{					
						$clickandpledge_OrganizationInformation = pmpro_getOption("clickandpledge_OrganizationInformation");		
						if( $clickandpledge_OrganizationInformation != '')
						{
							
							$recipt_org  = $dom->createElement('OrganizationInformation','');
							$recipt_org  = $receipt->appendChild($recipt_org);
							$recipt_org->appendChild($dom->createCDATASection($this->safeString($clickandpledge_OrganizationInformation,1500)));
						}
						
						$clickandpledge_ThankYouMessage = pmpro_getOption("clickandpledge_ThankYouMessage");
						if( $clickandpledge_ThankYouMessage != '')
						{
							
							
							$recipt_thanks  = $dom->createElement('ThankYouMessage','');
							$recipt_thanks  = $receipt->appendChild($recipt_thanks);
							$recipt_thanks->appendChild($dom->createCDATASection($this->safeString($clickandpledge_ThankYouMessage,500)));
						}
						
						$clickandpledge_TermsCondition = pmpro_getOption("clickandpledge_TermsCondition");
						if( $clickandpledge_TermsCondition != '')
						{
							
							
							$recipt_terms  = $dom->createElement('TermsCondition','');
							$recipt_terms  = $receipt->appendChild($recipt_terms);
							$recipt_terms->appendChild($dom->createCDATASection($this->safeString($clickandpledge_TermsCondition,1500)));
						}
						
						$recipt_email=$dom->createElement('EmailNotificationList','');
						$recipt_email=$receipt->appendChild($recipt_email);	
						$email_note=$dom->createElement('NotificationEmail','');
						$email_note=$recipt_email->appendChild($email_note);
					}
					elseif ($clickandpledge_email_customer_recurring == '1' && $case == 'authorize') 
					{		
						
						$recipt_lang=$receipt->appendChild($recipt_lang);							
						$clickandpledge_OrganizationInformation_subscription = pmpro_getOption("clickandpledge_OrganizationInformation_subscription");		
						if( $clickandpledge_OrganizationInformation_subscription != '')
						{
							
							
							$recipt_org  = $dom->createElement('OrganizationInformation','');
							$recipt_org  = $receipt->appendChild($recipt_org);
							$recipt_org->appendChild($dom->createCDATASection($this->safeString($clickandpledge_OrganizationInformation_subscription,1500)));
						}
						
						$clickandpledge_ThankYouMessage_subscription = pmpro_getOption("clickandpledge_ThankYouMessage_subscription");
						if( $clickandpledge_ThankYouMessage_subscription != '')
						{
						
							
							$recipt_thanks  = $dom->createElement('ThankYouMessage','');
							$recipt_thanks  = $receipt->appendChild($recipt_thanks);
							$recipt_thanks->appendChild($dom->createCDATASection($this->safeString($clickandpledge_ThankYouMessage_subscription,500)));
						}
						
						$clickandpledge_TermsCondition_subscription = pmpro_getOption("clickandpledge_TermsCondition_subscription");
						if( $clickandpledge_TermsCondition_subscription != '')
						{
							
							$recipt_terms  = $dom->createElement('TermsCondition','');
							$recipt_terms  = $receipt->appendChild($recipt_terms);
							$recipt_terms->appendChild($dom->createCDATASection($this->safeString($clickandpledge_TermsCondition_subscription,1500)));
						}
						
						$recipt_email=$dom->createElement('EmailNotificationList','');
						$recipt_email=$receipt->appendChild($recipt_email);					
						$email_note=$dom->createElement('NotificationEmail','');
						$email_note=$recipt_email->appendChild($email_note);
					}				
					elseif ($clickandpledge_email_customer_trial == '1' && $case == 'trial') 
					{	
						$recipt_lang=$receipt->appendChild($recipt_lang);						
						$clickandpledge_OrganizationInformation_trial = pmpro_getOption("clickandpledge_OrganizationInformation_trial");		
						if( $clickandpledge_OrganizationInformation_trial != '')
						{
						
							
							$recipt_org  = $dom->createElement('OrganizationInformation','');
							$recipt_org  = $receipt->appendChild($recipt_org);
							$recipt_org->appendChild($dom->createCDATASection($this->safeString($clickandpledge_OrganizationInformation_trial,1500)));
						}
						
						$clickandpledge_ThankYouMessage_trial = pmpro_getOption("clickandpledge_ThankYouMessage_trial");
						if( $clickandpledge_ThankYouMessage_trial != '')
						{
							
							
							$recipt_thanks  = $dom->createElement('ThankYouMessage','');
							$recipt_thanks  = $receipt->appendChild($recipt_thanks);
							$recipt_thanks->appendChild($dom->createCDATASection($this->safeString($clickandpledge_ThankYouMessage_trial,500)));
						}
						
						$clickandpledge_TermsCondition_trial = pmpro_getOption("clickandpledge_TermsCondition_trial");
						if( $clickandpledge_TermsCondition_trial != '')
						{
							
							
								
							$recipt_terms  = $dom->createElement('TermsCondition','');
							$recipt_terms  = $receipt->appendChild($recipt_terms);
							$recipt_terms->appendChild($dom->createCDATASection($this->safeString($clickandpledge_TermsCondition_trial,1500)));
						}
							
						$recipt_email=$dom->createElement('EmailNotificationList','');
						$recipt_email=$receipt->appendChild($recipt_email);	
						$email_note=$dom->createElement('NotificationEmail','');
						$email_note=$recipt_email->appendChild($email_note);
					}
				}
			}
			
			
			$transation=$dom->createElement('Transaction','');
			$transation=$order->appendChild($transation);
			$trans_type=$dom->createElement('TransactionType','Payment');
			$trans_type=$transation->appendChild($trans_type);
			$trans_desc=$dom->createElement('DynamicDescriptor','DynamicDescriptor');
			$trans_desc=$transation->appendChild($trans_desc); 
			
			if(isset($orderplaced->BillingPeriod) && $orderplaced->BillingPeriod != "" && in_array($case, array('authorize', 'trial'))){		
				if(($case=='trial' && ($orderplaced->TrialBillingCycles >1 || $orderplaced->TrialBillingCycles==0)) || ($case=='authorize' && ($orderplaced->TotalBillingCycles>1 || $orderplaced->TotalBillingCycles==0 || (isset($order->membership_level->billing_limit) && $order->membership_level->billing_limit==0)))) {
					
					
					
					//Recurring
					$trans_recurr=$dom->createElement('Recurring','');
					$trans_recurr=$transation->appendChild($trans_recurr);
					if($case == 'authorize') {
						//echo $orderplaced->BillingFrequency;
						if($orderplaced->BillingFrequency >= 1 && $orderplaced->membership_level->billing_limit == 0) {
							$temp_cycle = 999;
							if((int)$orderplaced->TrialBillingCycles>0)
							{
								$temp_cycle = $temp_cycle - (int)$orderplaced->TrialBillingCycles;
							}
							$total_installment=$dom->createElement('Installment',$temp_cycle);
							$total_installment=$trans_recurr->appendChild($total_installment);
						}
						else
						{
							$total_installment=$dom->createElement('Installment',$orderplaced->TotalBillingCycles);
							$total_installment=$trans_recurr->appendChild($total_installment);
						}
					} else {
						//Trial
						if($orderplaced->BillingFrequency >= 1 && $orderplaced->TrialBillingCycles == 0) {
							$total_installment=$dom->createElement('Installment',999);
							$total_installment=$trans_recurr->appendChild($total_installment);
						}
						else
						{
							$total_installment=$dom->createElement('Installment',$orderplaced->TrialBillingCycles);
							$total_installment=$trans_recurr->appendChild($total_installment);
							
						}
					}
					
					$Periodicity = '';
					 $Periodicity = $this->fetch_periodicity($orderplaced->membership_level->cycle_period, $orderplaced->membership_level->cycle_number);
					
					 
					 
					if($Periodicity)
					{
						$total_periodicity=$dom->createElement('Periodicity',$Periodicity);
						$total_periodicity=$trans_recurr->appendChild($total_periodicity);

					}
					else
					{
						return false;
					}
					
					$RecurringMethod=$dom->createElement('RecurringMethod','Subscription');
					$RecurringMethod=$trans_recurr->appendChild($RecurringMethod);
				}
			}
			
			$trans_totals=$dom->createElement('CurrentTotals','');
			$trans_totals=$transation->appendChild($trans_totals);				
			$tax_new = 0;
		
			$amount          = $orderplaced->InitialPayment;
		$orderplaced->subtotal = $amount;
		$cnpntax             = $orderplaced->getTax( true );

			if( isset($cnpntax) && $cnpntax != 0 && in_array($case, array('trial', 'authorize','charge') ))
			{	
				
				if (!in_array($case, array('trial', 'authorize'))) { 
				
				$tax_new =  $cnpntax; 
				$total_tax=$dom->createElement('TotalTax',number_format($tax_new, 2, '.', '')*100);
				$total_tax=$trans_totals->appendChild($total_tax);
				} else if ($case == 'authorize') {
				$tax_new = ($orderplaced->tax * 100) / $orderplaced->subtotal;
				$tax_new = ($orderplaced->PaymentAmount * $tax_new) / 100; 
				$total_tax=$dom->createElement('TotalTax',number_format($tax_new, 2, '.', '')*100);
				$total_tax=$trans_totals->appendChild($total_tax);
				} else {
				$tax_new = ($orderplaced->tax * 100) / $orderplaced->subtotal;
				$tax_new = ($orderplaced->TrialAmount * $tax_new) / 100; 
				$total_tax=$dom->createElement('TotalTax',number_format($tax_new, 2, '.', '')*100);
				$total_tax=$trans_totals->appendChild($total_tax);
				}
			}
			else if($tax_state_value!=0 && in_array($case, array('authorize','trial')))
			{
				if ($case == 'authorize') {
			
					$tax_new = ($orderplaced->PaymentAmount * $tax_state_value);
					$total_tax=$dom->createElement('TotalTax',number_format($tax_new, 2, '.', '')*100);
					$total_tax=$trans_totals->appendChild($total_tax);
				} else {
						
					$tax_new = ($orderplaced->TrialAmount * $tax_state_value);
					$total_tax=$dom->createElement('TotalTax',number_format($tax_new, 2, '.', '')*100);
					$total_tax=$trans_totals->appendChild($total_tax);
				}
			}
			
			if(pmpro_isLevelRecurring($orderplaced->membership_level) && in_array($case, array('trial', 'authorize'))){
				if($case == 'authorize') {
					$total_amount=$dom->createElement('Total',(($orderplaced->PaymentAmount+number_format($tax_new, 2, '.', ''))*100));
					$total_amount=$trans_totals->appendChild($total_amount);
				} else {
					$total_amount=$dom->createElement('Total',(($orderplaced->TrialAmount+number_format($tax_new, 2, '.', ''))*100));
					$total_amount=$trans_totals->appendChild($total_amount);
				}
			} else {
				$InitialPayment = $orderplaced->tax+$orderplaced->InitialPayment;
				$total_amount=$dom->createElement('Total',($InitialPayment*100));
				$total_amount=$trans_totals->appendChild($total_amount);
			}
			if( isset($orderplaced->discount_code) && $orderplaced->discount_code != '' )
			{
				$trans_coupon=$dom->createElement('CouponCode',$this->safeString($orderplaced->discount_code,50));
				$trans_coupon=$transation->appendChild($trans_coupon);
			}
			
			if( isset($orderplaced->tax) && $orderplaced->tax != 0 && in_array($case, array('trial', 'authorize','charge') ))
			{
				if (!in_array($case, array('trial', 'authorize'))) { 
					$trans_tax=$dom->createElement('TransactionTax',number_format($orderplaced->tax, 2, '.', '')*100);
					$trans_tax=$transation->appendChild($trans_tax);
				} else if ($case == 'authorize') {
					$trans_tax=$dom->createElement('TransactionTax',number_format($tax_new, 2, '.', '')*100);
					$trans_tax=$transation->appendChild($trans_tax);
				} else {
					$trans_tax=$dom->createElement('TransactionTax',number_format($tax_new, 2, '.', '')*100);
					$trans_tax=$transation->appendChild($trans_tax);
				}		
			}
			else if($tax_state_value!=0 && in_array($case, array('authorize','trial')))
			{
			
				if ($case == 'authorize') {
					$trans_tax=$dom->createElement('TransactionTax',number_format($tax_new, 2, '.', '')*100);
					$trans_tax=$transation->appendChild($trans_tax);
				} else {
					$trans_tax=$dom->createElement('TransactionTax',number_format($tax_new, 2, '.', '')*100);
					$trans_tax=$transation->appendChild($trans_tax);
				}
			
			}
			if(pmpro_isLevelRecurring($orderplaced->membership_level) && in_array($case, array('trial', 'authorize'))){
				$chargeDate=$dom->createElement('ChargeDate',date('y/m/d', strtotime($orderplaced->ProfileStartDate)));
				$chargeDate=$transation->appendChild($chargeDate);

			}
			
			$strParam =$dom->saveXML(); 
//print_r($strParam);
			return $strParam;
		}
		
		/*
			Setup a subscription at the gateway.

			Required if supporting recurring subscriptions.
		*/
		function subscribe(&$order)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//filter order before subscription. use with care.
			$order = apply_filters("pmpro_subscribe_order", $order, $this);
			
			//code to setup a recurring subscription with the gateway and test results would go here

			//simulate a successful subscription processing
			$order->status = "success";		
			$order->subscription_transaction_id = "TEST" . $order->code;				
			return true;
		}	
		
		/*
			Update billing at the gateway.

			Required if supporting recurring subscriptions and
			processing credit cards on site.
		*/
		function update(&$order)
		{
			//code to update billing info on a recurring subscription at the gateway and test results would go here

			//simulate a successful billing update
			return true;
		}
		
		/*
			Cancel a subscription at the gateway.

			Required if supporting recurring subscriptions.
		*/
		function cancel(&$order)
		{
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//code to cancel a subscription at the gateway and test results would go here

			//simulate a successful cancel			
			$order->updateStatus("cancelled");					
			return true;
		}	
		
		/*
			Get subscription status at the gateway.

			Optional if you have code that needs this or
			want to support addons that use this.
		*/
		function getSubscriptionStatus(&$order)
		{
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//code to get subscription status at the gateway and test results would go here

			//this looks different for each gateway, but generally an array of some sort
			return array();
		}

		/*
			Get transaction status at the gateway.

			Optional if you have code that needs this or
			want to support addons that use this.
		*/
		function getTransactionStatus(&$order)
		{			
			//code to get transaction status at the gateway and test results would go here

			//this looks different for each gateway, but generally an array of some sort
			return array();
		}
	}
		function cnppmp_version_example() { 
		$gateway = pmpro_getOption("gateway");
		if($gateway == "clickandpledge"){
		
		echo '<div style="display:none;"><input type="hidden" name="cnpversion" id="cnpversion" value="4.23110000-WP6.4.1-PMP2.12.4" /></div>';
		}
	}
		
	add_action('wp_footer', 'cnppmp_version_example'); 