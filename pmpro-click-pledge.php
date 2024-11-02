<?php
/*
Plugin Name: Paid Memberships Pro Click & Pledge Gateway
Plugin URI: http://manual.clickandpledge.com/PaidMembershipsPro.html
Description: With Click & Pledge, Accept all major credit cards directly on your Paid Memberships Pro website/membership levels with a seamless and secure checkout experience.<a href="https://www.clickandpledge.com/Apply/" target="_blank">Click Here</a> to get a Click & Pledge account.
Version: 4.24080000-WP6.6.1-PMP3.1.3
Author: Click & Pledge
Author URI: http://www.clickandpledge.com
*/

define("PMPRO_CLICKANDPLEDGEGATEWAY_DIR", dirname(__FILE__));

//load payment gateway class
require_once(PMPRO_CLICKANDPLEDGEGATEWAY_DIR . "/classes/class.pmprogateway_clickandpledge.php");

