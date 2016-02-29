<?php

namespace Blog\Messaging;

class BrowserProfile {
	
	private $browser;
	private $rawsig;
	private $sig;
	
	const SIGNATURE_HASH	= 'tiger160,4';
	
	private $markers = 
	array(
		'HTTP_ACCEPT_CHARSET',
		'HTTP_ACCEPT_ENCODING',
		'HTTP_ACCEPT_LANGUAGE',
		'HTTP_CONNECTION',
		
		'HTTP_DNT',
		'HTTP_X_DO_NOT_TRACK',
		
		'HTTP_UPGRADE_INSECURE_REQUESTS',
		'HTTP_PROXY_AUTHORIZATION',
		'HTTP_HOST',
		'HTTP_TE',
		'HTTP_MVNO',
		
		'HTTP_VERSION',
		'HTTP_VER',
		'HTTP_ATOR',
		'HTTP_S',
		'HTTP_ME',
		'HTTP_CHE',
		'HTTP_MEPASS',
		'HTTP_OR',
		'HTTP_AME',
		
		'HTTP_UA_OS',
		'HTTP_UA_CPU',
		'HTTP_UA_COLOR',
		'HTTP_UA_PIXELS',
		'HTTP_UA_VOICE',
		
		'HTTP_USER_AGENT',
		'HTTP_VIA',
		'HTTP_MAX_FORWARDS',
		'HTTP_PROFILE',
		'HTTP_DRM_VERSION',
		'HTTP_WAP_CONNECTION',
		'HTTP_DEVICE_STOCK_UA',
		'HTTP_PROXY_AGENT',
		
		'HTTP_IDENT',
		'HTTP_IDENT_USER',
		
		'HTTP_E',
		'HTTP_ET',
		'HTTP_PE',
		
		'HTTP_SERVICECONTROLINFO',
		'HTTP_QPR_LOOP',
		'HTTP_MAC',
		
		'HTTP_X_IMFORWARDS',
		'HTTP_X_NAI_ID',
		'HTTP_X_MOBNOTES_PLUGIN',
		'HTTP_X_TD',
		'HTTP_X_APN_ID',
		'HTTP_X_PCS_MDN',
		'HTTP_X_PCS_SUBID',
		'HTTP_X_VIVO_MIN',
		
		'HTTP_X_DEVICE_TYPE',
		'HTTP_X_BROWSER_VERSION',
		
		'HTTP_OKCOIE',
		'HTTP_CKIOOE2',
		'HTTP_CKIOOE',
		'HTTP_OKCOIE2',
		
		'HTTP_SP_VERSION',
		'HTTP_SP_CONVERT_PARAM',
		
		'HTTP_X_PS3_BROWSER',
		'HTTP_X_I_5_VERSION',
		'HTTP_X_PLATFORM_VERSION',
		
		'HTTP_X_ICM_A',
		'HTTP_X_UIDH',
		'HTTP_X_MSP_APN',
		'HTTP_X_GATEWAY',
		'HTTP_X_NETWORK_TYPE',
		'HTTP_X_DEVICE_USER_AGENT',
		'HTTP_X_UCBROWSER_DEVICE_UA',
		
		'HTTP_X_ORIGINAL_USER_AGENT',
		'HTTP_X_PUFFIN_UA',
		'HTTP_X_OPERAMINI_PHONE',
		'HTTP_X_OPERAMINI_PHONE_UA',
		'HTTP_X_OPERAMINI_FEATURES',
		
		'HTTP_X_HUAWEI_NETWORKTYPE',
		'HTTP_X_HUAWEI_APN',
		'HTTP_X_HUAWEI_CHARGINGID',
		'HTTP_X_HUAWEI_BEARER',
		'HTTP_X_HUAWEI_MSISDN',
		
		'HTTP_X_SWNSURLPROTOCOL',
		
		'HTTP_BEARER_TYPE',
		'HTTP_CUDA_CLIIP',
		'HTTP_LBS_ZONEID',
		'HTTP_VWC_IS_PARENT',
		'HTTP_MODEM',
		'HTTP_AFL',
		'HTTP_T_UA',
		'HTTP_TRAFFIC_USAGE_MESSAGE',
		
		'HTTP_X_CSPIRE_NASIP',
		'HTTP_X_CSPIRE_MDN',
		'HTTP_X_CSPIRE_MIN',
		
		'HTTP_X_PALM_CARRIER',
		
		'HTTP_X_ATT_DEVICEID',
		'HTTP_X_VODAFONE_ROAMINGIND',
		'HTTP_X_VODAFONE_3GPDPCONTEXT',
		'HTTP_X_HUAWEI_USERID',
		'HTTP_X_WAP_3GPP_RAT_TYPE',
		
		'HTTP_X_IMEI',
		'HTTP_X_GETZIP',
		'HTTP_X_MSISDN',
		'HTTP_X_SGSNIP',
		'HTTP_X_GGSNIP',
		
		'HTTP_X_APPLICATION',
		'HTTP_X_ORANGE_ID',
		'HTTP_X_BLUECOAT_VIA',
		'HTTP_X_MOBILE_GATEWAY',
		'HTTP_X_ROAMING',
		
		'HTTP_X_OA',
		'HTTP_X_OS_PREFS',
		'HTTP_X_VFPROVIDER',
		'HTTP_X_VFSTATUS',
		'HTTP_X_NB_CONTENT',
		
		'HTTP_X_UP_SUBNO',
		'HTTP_X_UP_SUBSCRIBER_COS',
		'HTTP_X_UP_SUBSCRIBER_COI',
		'HTTP_X_UP_CALLING_LINE_ID',
		'HTTP_X_UP_UPLINK',
		
		'HTTP_X_UP_DEVCAP_ISCOLOR',
		'HTTP_X_UP_DEVCAP_SCREENDEPTH',
		'HTTP_X_UP_DEVCAP_CHARSET',
		'HTTP_X_UP_DEVCAP_MAX_PDU',
		'HTTP_X_UP_DEVCAP_DRM',
		'HTTP_X_UP_DEVCAP_DRMMODE',
		'HTTP_X_UP_DEVCAP_ZONE',
		'HTTP_X_UP_DEVCAP_KZ',
		'HTTP_X_UP_SUB_ID',
		'HTTP_X_UP_DEVCAP_SMARTDIALING',
		'HTTP_X_UP_DEVCAP_ACCEPT_LANGUAGE',
		
		'HTTP_X_ACCEPT_ENCODING_WNPROXY',
		'HTTP_X_UP_WTLS_INFO',
		'HTTP_X_MMS_PREPAID_FLAG',
		'HTTP_X_WSB_CONTEXTID',
		'HTTP_X_ICAP_VERSION',
		
		'HTTP_X_MSP_AG',
		'HTTP_X_MSP_CLID',
		'HTTP_X_MSP_SESSION_ID',
		'HTTP_X_MSP_WAP_CLIENT_ID',
		
		'HTTP_XAFBVQWW',

		'HTTP_X_NOKIA_IMEI',
		'HTTP_X_NOKIA_MSISDN',
		'HTTP_X_NOKIA_GID',
		'HTTP_X_NOKIA_PREPAIDIND',
		'HTTP_X_NOKIA_LOCALSOCKET',
		'HTTP_X_NOKIA_REMOTESOCKET',
		'HTTP_X_NOKIABROWSER_FEATURES',
		
		'HTTP_USER_IDENTITY_FORWARD_MSISDN',
		
		'HTTP_X_OPWV_DDM_HTTPMISCDD',
		'HTTP_X_OPWV_DDM_IDENTITY',
		'HTTP_X_OPWV_DDM_SUBSCRIBER'
	);
	
	public function __construct() {
		$sent	= array_intersect_key( 
				$_SERVER, 
				array_flip( $this->markers )
			);
		$out	= implode( ' ', array_values( $sent ) );
		$this->rawsig	= $out;
		$this->sig	= hash( self::SIGNATURE_HASH, $out );
	}
	
	public function getSignature( $raw = false ) {
		if ( $raw ) {
			return $this->rawsig;
		}
		
		return $this->sig;
	}
	
	/**
	 * Best effort browser detection
	 * 
	 * @link http://php.net/manual/en/function.get-browser.php#101125
	 * @link http://php.net/manual/en/function.get-browser.php#115036
	 * @link https://en.wikipedia.org/wiki/Windows_NT
	 * 
	 * @return array
	 */
	public function browser() {
		if ( isset( $this->browser ) ) {
			return $this->browser;
		}
		
		$ua		= $_SERVER['HTTP_USER_AGENT'];
		$vars		= array();
		
		$vars['OS']	= self::matchIn( $ua, array(
				'Windows'	=> 'windows|win32',
				'Android'	=> 'android',
				'iOS'		=> 'ios',
				'Mac'		=> 'macintosh|mac os x',
				'Linux'		=> 'linux'
			), 'unknown' );
		
		if ( 'Windows' == $vars['OS'][0] ) {
			$v = self::matchIn( $ua, array(
				'8.1'		=> 'NT 6.3',
				'8'		=> 'NT 6.2',
				'7'		=> 'NT 6.1',
				'Vista'		=> 'NT 6.0',
				'XP'		=> 'NT 5.1',
				'Server/XP Pro'	=> 'NT 5.2',
				'2000'		=> 'NT 5.0'
			), '' );
			
			//switch( $v ) {
			//	case 'Server/XP Pro':
			//		break;
			//}
			
			$vars['OS'][0] .= empty( $v )? '' : " $v";
			
			if (
				preg_match( '/WOW64/i', $ua ) || 
				preg_match( '/x64/i', $ua )
			) {
				$vars['OS'][0] .= ' (x64)';
			}
		}
		
		$vars['Browser'][0]	= self::matchIn( $ua, array(
				'Opera'			=> 'Opera',
				'Chrome'		=> 'Chrome',
				'Internet Explorer'	=> 'MSIE|trident',
				'Firefox'		=> 'Firefox',
				'Safari'		=> 'Safari',
				'Netscape'		=> 'Netscape'
			), 'other' );
		
		$vars['Version']	= 'unknown';
		
		if ( preg_match_all( 
			'#(?<browser>Version|' . 
				$vars['Browser'][1] . 
				')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#', 
			$ua, 
			$m 
		) ) {
			$i = count( $matches );
			if ( $i != 1 ) {
				if ( strripos( $ua, "Version" ) < 
					strripos($ua, $vars['Browser'] ) ) {
					$vars['Version'] = $matches['version'][1];
				} else {
					$vars['Version'] = $matches['version'][0];
				}
			} else {
				$vars['Version']= $matches['version'][0];
			}
			if ( null == $vars['Version'] || "" == $vars['Version'] ) {
				$vars['Version'] = "unknown"; 
			}
		}
		
		$this->browser = array();
		// Clean up match info
		foreach ( $vars as $k => $v ) {
			if ( is_array( $v ) ) {
				$this->browser[$k] = $v[0];
				continue;
			}
			$this->browser[$k] = $v;
		}
		
		return $this->browser;
	}
}