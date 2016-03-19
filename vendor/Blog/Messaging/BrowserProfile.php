<?php

namespace Blog\Messaging;

class BrowserProfile {
	
	private $browser;
	private $header_hash;
	private $lang;
	private $rawsig;
	private $sig;
	
	protected $sent_headers;
	protected $m_fields;
	
	const SIGNATURE_HASH	= 'tiger192,4';
	
	private $markers = 
	array(
		'HTTP_ACCEPT_CHARSET',
		'HTTP_ACCEPT_LANGUAGE',
		'HTTP_USER_AGENT',
		'HTTP_VIA',
		
		'HTTP_DNT',
		'HTTP_X_DO_NOT_TRACK',
		
		'HTTP_UPGRADE_INSECURE_REQUESTS',
		'HTTP_PROXY_AUTHORIZATION',
		'HTTP_HOST',
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
		
		'HTTP_MAX_FORWARDS',
		'HTTP_PROFILE',
		'HTTP_DRM_VERSION',
		'HTTP_WAP_CONNECTION',
		'HTTP_DEVICE_STOCK_UA',
		'HTTP_PROXY_AGENT',
		
		'HTTP_IDENT',
		'HTTP_IDENT_USER',
		'HTTP_CONTRACTID',
		'HTTP_OPERATORID',
		'HTTP_USERID',
		
		'HTTP_E',
		'HTTP_ET',
		'HTTP_PE',
		
		'HTTP_X_D_FORWARDER',
		'HTTP_SERVICECONTROLINFO',
		'HTTP_QPR_LOOP',
		'HTTP_MAC',
		'HTTP_PNP',
		
		'HTTP_OPT',
		
		'HTTP_APN',
		'HTTP_X_HTS_APN',
		'HTTP_X_HTS_USER',
		'HTTP_X_HTS_CLID',
		
		'HTTP_X_IMFORWARDS',
		'HTTP_X_NAI_ID',
		'HTTP_X_MOBNOTES_PLUGIN',
		'HTTP_X_TD',
		'HTTP_X_APN_ID',
		'HTTP_X_PCS_MDN',
		'HTTP_X_PCS_SUBID',
		'HTTP_X_VIVO_MIN',
		
		'HTTP_X_OS_PREFS',
		
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
		'HTTP_X_NETWORK_INFO',
		'HTTP_X_DEVICE_USER_AGENT',
		'HTTP_X_UCBROWSER_DEVICE_UA',
		
		'HTTP_X_ORIGINAL_USER_AGENT',
		'HTTP_X_PUFFIN_UA',
		
		'HTTP_X_OB',
		'HTTP_X_OPERA_ID',
		'HTTP_X_OPERA_INFO',
		'HTTP_X_OPERAMINI_PHONE',
		'HTTP_X_OPERAMINI_PHONE_UA',
		'HTTP_X_OPERAMINI_FEATURES',
		
		'HTTP_X_HUAWEI_NETWORKTYPE',
		'HTTP_X_HUAWEI_STACKTYPE',
		'HTTP_X_HUAWEI_APN',
		'HTTP_X_HUAWEI_CHARGINGID',
		'HTTP_X_HUAWEI_BEARER',
		'HTTP_X_HUAWEI_MSISDN',
		'HTTP_X_HUAWEI_USERID',
		'HTTP_X_HUAWEI_AUTHMETHOD',
		
		'HTTP_X_SWNSURLPROTOCOL',
		
		'HTTP_BEARER_TYPE',
		'HTTP_CUDA_CLIIP',
		'HTTP_LBS_ZONEID',
		'HTTP_VWC_IS_PARENT',
		'HTTP_MODEM',
		'HTTP_AFL',
		'HTTP_T_UA',
		'HTTP_TRAFFIC_USAGE_MESSAGE',
		
		'HTTP_HNAME1',
		'HTTP_HNAME2',
		'HTTP_HNAME3',
		
		'HTTP_X_CSPIRE_NASIP',
		'HTTP_X_CSPIRE_MDN',
		'HTTP_X_CSPIRE_MIN',
		
		'HTTP_X_PALM_CARRIER',
		
		'HTTP_X_ATT_DEVICEID',
		'HTTP_X_VODAFONE_ROAMINGIND',
		'HTTP_X_VODAFONE_3GPDPCONTEXT',
		'HTTP_X_WAP_3GPP_RAT_TYPE',
		
		'HTTP_X_IMEI',
		'HTTP_X_GETZIP',
		'HTTP_X_MSISDN',
		'HTTP_X_SGSNIP',
		'HTTP_X_GGSNIP',
		
		'HTTP_PDP_IP',
		'HTTP_X_THU_IPADDRESS',
		
		'HTTP_UE_APPLICATION_TYPE',
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
		
		'HTTP_X_UP_SUB_ID',
		'HTTP_X_UP_DEVCAP_ISCOLOR',
		'HTTP_X_UP_DEVCAP_SCREENDEPTH',
		'HTTP_X_UP_DEVCAP_CHARSET',
		'HTTP_X_UP_DEVCAP_MAX_PDU',
		'HTTP_X_UP_DEVCAP_DRM',
		'HTTP_X_UP_DEVCAP_DRMMODE',
		'HTTP_X_UP_DEVCAP_ZONE',
		'HTTP_X_UP_DEVCAP_KZ',
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
		'HTTP_X_MSP_MSISDN_ENC',
		'HTTP_X_MSP_NODE_NAME',
		
		'HTTP_X_MNC',
		'HTTP_X_MCC',
		
		'HTTP_X_ACCESS_SUBNYM',
		
		'HTTP_XAFBVQWW',
		
		'HTTP_X_NOKIA_BEARER',
		'HTTP_X_NOKIA_CONNECTION_MODE',
		'HTTP_X_NOKIA_GATEWAY_ID',
		'HTTP_X_NOKIA_WTLS',
		'HTTP_X_NOKIA_IMEI',
		'HTTP_X_NOKIA_MSISDN',
		'HTTP_X_NOKIA_GID',
		'HTTP_X_NOKIA_PREPAIDIND',
		'HTTP_X_NOKIA_LOCALSOCKET',
		'HTTP_X_NOKIA_REMOTESOCKET',
		'HTTP_X_NOKIABROWSER_FEATURES',
		'HTTP_X_NOKIA_MUSICSHOP_BEARER',
		'HTTP_X_NOKIA_MUSICSHOP_VERSION',
		
		'HTTP_USER_IDENTITY_FORWARD_MSISDN',
		'HTTP_MSCOPE_MSISDN',
		
		'HTTP_X_OPWV_DDM_HTTPMISCDD',
		'HTTP_X_OPWV_DDM_IDENTITY',
		'HTTP_X_OPWV_DDM_SUBSCRIBER',
		
		'HTTP_X_RIM_ACCEPT_ENCODING',
		'HTTP_X_RIM_DEFAULT_CHARSET',
		'HTTP_X_RIM_IMG_SETTING',
		'HTTP_X_RIM_REQUEST_PRIORITY',
		
		'HTTP_J_ES_US',
		'HTTP_BANDWIDTH',
		'HTTP_UPLINKBANDWIDTH',
		'HTTP_DOWNLINKBANDWIDTH',
		'HTTP_ROAMINGOUTFLAG'
	);
	
	public function __construct() {
		$this->m_fields	= 
		array_intersect_key( 
			$_SERVER, 
			array_flip( $this->markers )
		);
		$out		= 
			implode( ' ', array_values( $this->m_fields ) );
		
		$this->rawsig	= $out;
		$this->sig	= hash( self::SIGNATURE_HASH, $out );
	}
	
	public function getSignature( $raw = false ) {
		if ( $raw ) {
			return $this->rawsig;
		}
		
		return $this->sig;
	}
	
	public function getMarkers() {
		return $this->m_fields;
	}
	
	/**
	 * Header signature excluding frequently changing headers
	 */
	public function headerHash() {
		if ( isset( $this->header_hash ) ) {
			return $this->header_hash;
		}
		
		$headers	= $this->headers();
		$skip		= 
		array(
			'Accept-Datetime',
			'Accept-Encoding',
			'Content-Length',
			'Cache-Control',
			'Content-Type',
			'Content-Md5',
			'Referer',
			'Cookie',
			'Expect',
			'Date',
			'TE'
		);
		
		$search		= array_intersect_key( 
					array_keys( $headers ), 
					array_reverse( $skip ) 
				);
		$match		= '';
		
		foreach ( $headers as $k => $v ) {
			$match .= $v[0];
		}
		
		$this->header_hash = 
			hash( self::SIGNATURE_HASH, $match );
	}
	
	/**
	 * Sent HTTP headers
	 */
	public function headers( $key = null ) {
		if ( !isset( $this->sent_headers ) ) {
			$this->sent_headers = $this->httpHeaders();
		}
		
		if ( empty( $key ) ) {
			return $this->sent_headers;
		}
		
		return isset( $this->sent_headers[$key] )? 
				$this->sent_headers[$key] : null;
	}
	
	/**
	 * Best effort language detection by priority
	 */
	public function languages( array $supported ) {
		if ( isset( $this->lang ) ) {
			return $this->lang;
		}
		
		$raw	= $this->headers( 'Accept-Language' );
		if ( empty( $raw ) ) {
			$this->lang	= array( 'en' => '1.0' );
			return $this->lang;
		}
		
		$raw	= preg_replace( '/[^\w\-,.;=]/', '', $raw );
		
		$header	= strtolower( $raw );
		$langs	= array();
		
		preg_match_all(
			'~([\w-]+)(?:[^,\d]+([\d.]+))?~',
			$header, $matches, \PREG_SET_ORDER
		);
		
		foreach ( $matches as $match ) {
			list( $code, $region )		= 
				explode( '-', $match[1] ) + 
				array( '', '' );
			
			$priority			= 
			isset( $match[2] ) ? 
				( float ) $match[2] : 1.0;
			
			if ( isset( $supported[$match[1]] ) ) {
				$langs[$match[1]]	= $priority;
				continue;
			}
			
			if ( isset( $supported[$code] ) ) {
				$langs[$code]		= 
					$priority - 0.1;
			}
		}
		
		if ( empty( $langs ) ) {
			$langs	= array( 'en' => '1.0' );
		}
		
		arsort( $langs );
		$this->lang = $langs;
		return $langs;
	}
	
	/**
	 * Process HTTP_* variables
	 */
	private function httpHeaders() {
		$val = array();
		foreach ( $_SERVER as $k => $v ) {
			if ( 0 === strncasecmp( $k, 'HTTP_', 5 ) ) {
				$a = explode( '_' ,$k );
				array_shift( $a );
				array_walk( $a, function( &$r ) {
					$r = ucfirst( strtolower( $r ) );
				});
				$val[ implode( '-', $a ) ] = $v;
			}
		}
		
		return $val;
	}
}
