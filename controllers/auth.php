<?php

namespace controllers;

use libs\Session;
use system\Config;
use system\Controller;
use widgets\auth\AuthWidget;
use widgets\messages\MessagesWidget;

include_once(ROOT.DS.'helpers'.DS.'regexp.php');

class Auth extends Controller {
	public function __construct($data = []) {
		parent::__construct($data);
		$this->auth_model = new \models\Auth();
	}

	public function reg() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$email = $_POST['email'];
		$passwd = $_POST['passwd'];
		$passwd_conf = $_POST['passwd_conf'];
		$currentDate = (new \DateTime())->format('Y-m-d H:i:s');

		if (!regexp_email($email)) {
			$result['error'] = 1;
			$result['msg'] = 'Incorrect email address!'. PHP_EOL;
		} else {
			if (!regexp_passwd($passwd)) {
				$result['error'] = 1;
				$result['msg'] = 'Password must be 4-20 length!'. PHP_EOL;
				$result['msg'] .= 'Accepted symbols: letters,numbers,-,_'. PHP_EOL;
			} else {
				if ($passwd !== $passwd_conf) {
					$result['error'] = 1;
					$result['msg'] = 'Passwords do not match!'. PHP_EOL;
				} else { // insert data into db
					$createUser = $this->auth_model->createUser($email, $passwd, $currentDate);
					switch ($createUser) {
						case 0:
							$result['error'] = 1;
							$result['msg'] = 'Registration error! Try later!'. PHP_EOL;
							break;
						case 1:
							$confirm_url = 'http://'.$_SERVER['HTTP_HOST']
                                ."/auth/confirm/" .md5(Config::get('confirm_salt').$email.$currentDate);
							if (!$this->sendMail($email, $confirm_url)) {
								$result['error'] = 1;
								$result['msg'] = 'Registration error! Try later!'. PHP_EOL;
							}
							break;
						case 2:
							$result['error'] = 1;
							$result['msg'] = 'Email '.$_POST['email'].' is already register!'. PHP_EOL;
							break;
					}
				}
			}
		}

		if (!$result['error']) {
			$result['msg'] = 'Confirmation link is send to your email address!';
		}

		$messagesWidget = new MessagesWidget('message', [
			'msg' => $result['msg']
		]);
		$result['msg'] = $messagesWidget->getView();

		echo json_encode($result);
	}

	public function login() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$email = $_POST['email'];
		$passwd = $_POST['passwd'];

		if (regexp_email($email) && regexp_passwd($passwd)) {
			$getUser = $this->auth_model->getUser($email, $passwd);
			if ($getUser === false) {
				$result['error'] = 1;
				$result['msg'] = 'Login error! Try later'. PHP_EOL;
			} elseif (empty($getUser)) {
				$result['error'] = 1;
				$result['msg'] = 'No such email or password!'. PHP_EOL;
			} elseif (!$getUser[0]['confirm']) {
				$result['error'] = 1;
				$result['msg'] = 'Your account is not confirmed!'. PHP_EOL;
			} else {// authorization
				Session::set('id',$getUser[0]['id']);
				Session::set('email',$getUser[0]['email']);
				$result['email'] = $getUser[0]['email'];
			}
		} else {
			$result['error'] = 1;
			$result['msg'] = 'Incorrect data format!'. PHP_EOL;
		}

		echo json_encode($result);
	}

	public function logout() {
		$result['error'] = 0;
		Session::destroy();
		echo json_encode($result);
	}

	public function showSignup() {
		$authWidget = new AuthWidget('signup');
		echo $authWidget->getView();
	}

	public function showLogin() {
		$authWidget = new AuthWidget('login');
		echo $authWidget->getView();
	}

	public function goBack() {
		$authWidget = new AuthWidget();
		echo $authWidget->getView();
	}

	public function showAuthorized() {
		$view_name = 'authorized';
		$authWidget = new AuthWidget($view_name, [
			'email' => htmlspecialchars($_GET['email'])
		]);
		echo $authWidget->getView();
	}

	public function confirm() {
		$params = $this->params;
		$hash = $params[0];
		if (regexp_hash($hash)) {
			$conf_response = $this->auth_model->confirmUser($hash);
		} else {
			$conf_response = 4; // кто-то шаманит
		}
		header('Location: /?response='.$conf_response.'&hash='.$hash);
	}

	private function sendMail($email,$confirm_url) {
		// Create the SMTP Transport
		$transport = (new \Swift_SmtpTransport('smtp.gmail.com', 465,'ssl'))
			->setUsername('lsabot413@gmail.com')
			->setPassword('njgxdkytmoewjnzx');

		// Create the Mailer using your created Transport
		$mailer = new \Swift_Mailer($transport);

		// Create a message
		$message = new \Swift_Message();

		// Set a "subject"
		$message->setSubject('Confirm Registration.');

		// Set the "From address"
		$message->setFrom(['adel1ne4891@gmail.com' => '']);

		// Set the "To address" [Use setTo method for multiple recipients, argument should be array]
		$message->addTo($email,'');

		// Set the plain-text "Body"
		$authWidget = new AuthWidget('plain_email_conf_message', [
			'confirm_url' => $confirm_url
		]);
		$plainBody = $authWidget->getView();
		$message->setBody($plainBody);

		// Set a "Body"
		$authWidget = new AuthWidget('html_email_conf_message', [
			'confirm_url' => $confirm_url
		]);
		$htmlBody = $authWidget->getView();
		$message->addPart($htmlBody, 'text/html');

		// Send the message
		return $mailer->send($message);
	}
}