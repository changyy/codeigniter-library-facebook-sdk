<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['facebook']['api_id'] = 'Your Facebook App ID';
$config['facebook']['app_secret'] = 'Your Facebook App Secret';
$config['facebook']['redirect_url'] = 'Your Facebook App Redriect Url';
$config['facebook']['permissions'] = array(
	'email',
	'public_profile',
	'user_friends'
);
