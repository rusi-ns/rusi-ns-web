<?php
if (!defined('ABSPATH')) {
    exit;
}
$bools = array(
    0 => __('Off', 'http-headers'), 
    1 => __('On', 'http-headers'),
);

$categories = array(
	'security' => __('Security', 'http-headers'),
    'access-control' => __('Access control', 'http-headers'),
    'authentication' => __('Authentication', 'http-headers'),
    'compression' => __('Compression', 'http-headers'),
    'caching' => __('Caching', 'http-headers'),
    'misc' => __('Miscellaneous', 'http-headers'),
);

$headers = array(
	'x-frame-options' => array('X-Frame-Options', 'hh_x_frame_options', 'security'),
	'x-xss-protection' => array('X-XSS-Protection', 'hh_x_xxs_protection', 'security'),
	'x-content-type-options' => array('X-Content-Type-Options', 'hh_x_content_type_options', 'security'),
	'x-ua-compatible' => array('X-UA-Compatible', 'hh_x_ua_compatible', 'misc'),
	'strict-transport-security' => array('Strict-Transport-Security', 'hh_strict_transport_security', 'security'),
	'p3p' => array('P3P', 'hh_p3p', 'access-control'),
	'public-key-pins' => array('Public-Key-Pins', 'hh_public_key_pins', 'security'),
	'referrer-policy' => array('Referrer-Policy', 'hh_referrer_policy', 'security'),
	'content-security-policy' => array('Content-Security-Policy', 'hh_content_security_policy', 'security'),
	'access-control-allow-origin' => array('Access-Control-Allow-Origin', 'hh_access_control_allow_origin', 'access-control'),
	'access-control-allow-credentials' => array('Access-Control-Allow-Credentials', 'hh_access_control_allow_credentials', 'access-control'),
	'access-control-max-age' => array('Access-Control-Max-Age', 'hh_access_control_max_age', 'access-control'),
	'access-control-allow-methods' => array('Access-Control-Allow-Methods', 'hh_access_control_allow_methods', 'access-control'),
	'access-control-allow-headers' => array('Access-Control-Allow-Headers', 'hh_access_control_allow_headers', 'access-control'),
	'access-control-expose-headers' => array('Access-Control-Expose-Headers', 'hh_access_control_expose_headers', 'access-control'),
	'content-encoding' => array('Content-Encoding', 'hh_content_encoding', 'compression'),
	'vary' => array('Vary', 'hh_vary', 'compression'),
	'x-powered-by' => array('X-Powered-By', 'hh_x_powered_by', 'misc'),
	'www-authenticate' => array('WWW-Authenticate', 'hh_www_authenticate', 'authentication'),
	'cache-control' => array('Cache-Control', 'hh_cache_control', 'caching'),
	'expires' => array('Expires', 'hh_expires', 'caching'),
	'pragma' => array('Pragma', 'hh_pragma', 'caching'),
	'age' => array('Age', 'hh_age', 'caching'),
	'connection' => array('Connection', 'hh_connection', 'misc'),
	'cookie-security' => array('Cookie security', 'hh_cookie_security', 'security'),
	'expect-ct' => array('Expect-CT', 'hh_expect_ct', 'security'),
	'timing-allow-origin' => array('Timing-Allow-Origin', 'hh_timing_allow_origin', 'access-control'),
	'custom-headers' => array('Custom headers', 'hh_custom_headers', 'misc'),
    'x-dns-prefetch-control' => array('X-DNS-Prefetch-Control', 'hh_x_dns_prefetch_control', 'security'),
    'x-download-options' => array('X-Download-Options', 'hh_x_download_options', 'security'),
    'x-permitted-cross-domain-policies' => array('X-Permitted-Cross-Domain-Policies', 'hh_x_permitted_cross_domain_policies', 'security'),
    'report-to' => array('Report-To', 'hh_report_to', 'security'),
);

$options = array(
    'hh_method',
    'hh_x_frame_options',
    'hh_x_frame_options_value',
    'hh_x_frame_options_domain',
    'hh_x_xxs_protection',
    'hh_x_xxs_protection_value',
    'hh_x_xxs_protection_uri',
    'hh_x_content_type_options',
    'hh_x_content_type_options_value',
    'hh_strict_transport_security',
    'hh_strict_transport_security_value', //obsolete
    'hh_strict_transport_security_max_age',
    'hh_strict_transport_security_sub_domains',
    'hh_strict_transport_security_preload',
    'hh_public_key_pins',
    'hh_public_key_pins_sha256_1',
    'hh_public_key_pins_sha256_2',
    'hh_public_key_pins_max_age',
    'hh_public_key_pins_sub_domains',
    'hh_public_key_pins_report_uri',
    'hh_public_key_pins_report_only',
    'hh_x_ua_compatible',
    'hh_x_ua_compatible_value',
    'hh_p3p',
    'hh_p3p_value',
    'hh_referrer_policy',
    'hh_referrer_policy_value',
    'hh_content_security_policy',
    'hh_content_security_policy_value',
    'hh_content_security_policy_report_only',
    'hh_access_control_allow_origin',
    'hh_access_control_allow_origin_value',
    'hh_access_control_allow_origin_url',
    'hh_access_control_allow_credentials',
    'hh_access_control_allow_credentials_value',
    'hh_access_control_allow_methods',
    'hh_access_control_allow_methods_value',
    'hh_access_control_allow_headers',
    'hh_access_control_allow_headers_value',
    'hh_access_control_expose_headers',
    'hh_access_control_expose_headers_value',
    'hh_access_control_max_age',
    'hh_access_control_max_age_value',
    'hh_content_encoding',
    'hh_content_encoding_value',
    'hh_content_encoding_ext',
    'hh_vary',
    'hh_vary_value',
    'hh_x_powered_by',
    'hh_x_powered_by_option',
    'hh_x_powered_by_value',
    'hh_www_authenticate',
    'hh_www_authenticate_type',
    'hh_www_authenticate_realm',
    'hh_www_authenticate_user',
    'hh_www_authenticate_pswd',
    'hh_cache_control',
    'hh_cache_control_value',
    'hh_age',
    'hh_age_value',
    'hh_pragma',
    'hh_pragma_value',
    'hh_expires',
    'hh_expires_value',
    'hh_expires_type',
    'hh_connection',
    'hh_connection_value',
    'hh_cookie_security',
    'hh_cookie_security_value',
    'hh_expect_ct',
    'hh_expect_ct_max_age',
    'hh_expect_ct_report_uri',
    'hh_expect_ct_enforce',
    'hh_timing_allow_origin',
    'hh_timing_allow_origin_value',
    'hh_timing_allow_origin_url',
    'hh_x_permitted_cross_domain_policies',
    'hh_x_permitted_cross_domain_policies_value',
    'hh_x_download_options',
    'hh_x_download_options_value',
    'hh_x_dns_prefetch_control',
    'hh_x_dns_prefetch_control_value',
    'hh_custom_headers',
    'hh_custom_headers_value',
    'hh_report_to',
    'hh_report_to_value',
);