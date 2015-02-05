# Setup
```
$ cd /path/ci/project/application/libraries
$ git clone https://github.com/changyy/codeigniter-library-facebook-sdk.git facebook-library
$ cp facebook-library/facebook.php facebook.php
$ cp facebook-library/application_config_facebook.php ../config/facebook.php
$ edit ../config/facebook.php
Update your facebook app info:

$config['facebook']['api_id']
$config['facebook']['app_secret']
$config['facebook']['redirect_url']
$config['facebook']['permissions']
```

# Usage
```
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Welcome extends CI_Controller {
	public function index() {
		$output = array( 'status' => false );

		$token = $this->input->get('token');
		if ($token === false)
			$token = $this->input->post('token');

		if ($token !== false) {
			$this->load->library('facebook');
			if (!$this->facebook->token_validation($token)) {
				$output['status'] = true;
				$output['long-lived-token'] = $this->facebook->get_long_lived_token($token);
			}
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($output));
	}
}
```
