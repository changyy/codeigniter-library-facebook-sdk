<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/facebook-library/facebook/autoload.php';

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

class Facebook {
	var $ci;
	var $helper;
	var $session;
	var $permissions;

	public function __construct() {
		$this->ci =& get_instance();
		$this->ci->load->config('facebook');
		$this->permissions = $this->ci->config->item('permissions', 'facebook');
		FacebookSession::setDefaultApplication( $this->ci->config->item('api_id', 'facebook'), $this->ci->config->item('app_secret', 'facebook') );
        }

	public function token_validation($token) {
		try {
			$this->session = new FacebookSession( $token );
			if ( $this->session->validate() ) {
				return true;
			}
		} catch ( Exception $e ) {
		}
		$this->session = null;
		return false;
	}

	public function get_long_lived_token($token) {
		// https://developers.facebook.com/docs/php/FacebookSession/4.0.0?locale=zh_TW#getlonglivedsession
		if ($this->token_validation($token)) {
			$this->session = $this->session->getLongLivedSession();
			return $this->session->getToken();
		}
		return false;
	}

	public function get_user($token = NULL) {
		if ( $this->session || (!empty($token) && $this->token_validation($token)) ) {
			$request = ( new FacebookRequest( $this->session, 'GET', '/me' ) )->execute();
			$user = $request->getGraphObject()->asArray();
			return $user;
		}
		return false;
	}

	public function query($token, $method, $api, $array_mode = true) {
		if ( $this->session || (!empty($token) && $this->token_validation($token)) ) {
			$response = ( new FacebookRequest( $this->session, $method, $api ) )->execute();
			if ($array_mode)
				return $response->getGraphObject()->asArray();
			return $response->getGraphObject();
		}
		return false;
	}

	public function get_permission($token) {
		return $this->query($token, 'GET', '/me/permissions');
	}

	public function check_friend_relationship($token, $fid) {
		// only for friend installed app
		// https://developers.facebook.com/docs/graph-api/reference/v2.2/user/friends?locale=zh_TW
		$data = $this->query($token, 'GET', '/me/friends/'.$fid);
		if(isset($data['data']) && count($data['data']) > 0)
			return true;
		return false;
	}

	public function get_friend_installed_app_list($token, &$have_next_page, $page = 1, $item_per_page = 25) {
		//$item_per_page = (int)$item_per_page;
		//if ($item_per_page < 25)
		//	$item_per_page = 25;
		//if ($item_per_page > 100)
		//	$item_per_page = 100;
		//$page = (int)$page;
		//if ($page <= 0)
		//	$page = 1;
		$have_next_page = false;
		$data = $this->query($token, 'GET', '/me/friends?fields=installed&limit='.($item_per_page).'&offset='.(($page - 1) * $item_per_page));
		if(isset($data['data']) && ($cnt = count($data['data'])) > 0) {
			if (isset($data['paging']) && is_object($data['paging']) && property_exists($data['paging'], 'next')) {
				if (isset($data['summary']) && is_object($data['summary']) && property_exists($data['summary'], 'total_count')) {
					$total_count = $data['summary']->total_count;
					if ($total_count > $page * $item_per_page)
						$have_next_page = true;
				}
			}
			$output = array();
			foreach($data['data'] as $user)
				if (is_object($user) && property_exists($user, 'id'))
					array_push($output, $user->id);
			return $output;
		}
		return false;
	}

	public function get_friend_list($token, &$have_next_page, $page, $item_per_page) {
		return $this->get_friend_installed_app_list($token, $have_next_page, $page, $item_per_page);
	}
}
