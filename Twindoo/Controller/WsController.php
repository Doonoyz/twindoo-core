<?php
/**
 * Controller for login/register/setting user preference on the site
 *
 * @package    Core
 * @subpackage controller
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
class Twindoo_Controller_WsController extends Zend_Controller_Action {
	/**
	 * Service that we're using
	 *
	 * @var string
	 */
	protected $service = "doonoyz";
	/**
	 * Information displayed on the login
	 *
	 * @var array
	 */
	protected $moreInfo = array();

	/**
	 * Initilization
	 */
	public function init() {
		$this->view->setCacheLife(0);
	}
	
	/**
	 * Action to show captcha protection
	 *
	 */
	public function showcaptchaAction() {
		$this->_helper->csrf->setPreviousToken();
        $this->getHelper('viewRenderer')->setNoRender();
		$aFonts = array (ROOT_DIR . '../Core/Captcha/CROOBIE.ttf', ROOT_DIR . '../Core/Captcha/LITTLELO.ttf', ROOT_DIR . '../Core/Captcha/TUFFY.ttf' );
		$oPhpCaptcha = new Captcha ( $aFonts, 200, 50 );
		$oPhpCaptcha->UseColour ( true );
		if (!$oPhpCaptcha->Create ()) {
			throw new Exception('Unable to create file due to GD miss...');
		}
	}

	/**
	 * Action protected that redirect to the site root
	 *
	 */
	public function indexAction() {
		$this->_redirect ( '/' );
	}

	/**
	 * Action to display settings interface. User is able to change his informations
	 *
	 */
	public function settingsAction() {
		$error = 'none';
		if (Twindoo_User::getCurrentUserId ()) {
			$user = new Twindoo_User ( Twindoo_User::getCurrentUserId () );
			if ($this->_request->getPost('password') != "" && 
				$this->_request->getPost('password') == $this->_request->getPost('password2')) {
				$user->setPassword ( $this->_request->getPost('password') );
				$user->commit ();
			} else if ( $this->_request->getPost('password') ) {
				$error = t_( "Passwords do not match." );
			}
		} else {
			$this->_redirect ( "/ws/login" );
		}

		$this->view->settings = (t_ ( "Settings" ));
		$this->view->addLayoutVar ( 'title', t_( "Settings" ) );
		$this->view->mail = ($user->getMail ());
		$this->view->name = ($user->getName ());
		$this->view->firstname = ($user->getFirstName ());
		$this->view->password = (t_ ( "Password" ));
		$this->view->changePassword = (t_ ( "Click here to change password" ));
		$this->view->confirmPassword = (t_ ( 'Confirm password' ));
		$this->view->save = (t_ ( 'Save' ));
		$this->view->errorMsg = ($error);
		$this->view->unregister = t_('I want to unregister my account');
	}

	/**
	 * Action called when a user is invited
	 * this interface permit account activating if invited but need invitation code
	 *
	 */
	public function invitationAction() {
		$secret = $this->_getParam ( 'secret' ) ? $this->_getParam ( 'secret' ) : 0;
		$mail = "";
		$error = "none";
		$success = "none";

		if ($secret !== 0) {
			$id = Twindoo_User::getIdBySecret ( $secret );
			if ($id) {
				$user = new Twindoo_User ( $id );
				$mail = $user->getMail ();
				if ($this->getRequest()->isPost()) {
					if ($this->_request->getPost('name') != '' && $this->_request->getPost('firstname') != '') {
						if ($user->setLogin ( $this->_request->getPost('login') )) {
							if (($this->_request->getPost('password') == $this->_request->getPost('password2')) 
								 && $this->_request->getPost('password') != "") {
								$user->setPassword ( $this->_request->getPost('password') );
								$user->setName ( $this->_request->getPost('name') );
								$user->setFirstName ( $this->_request->getPost('firstname') );
								$user->setActive ();
								$user->commit ();
								$user->getConnected ();
								$success = t_( "Your account has been created" );
							} else
								$error = t_( "Passwords do not match." );
						} else {
							$success = "none";
							$error = t_( "Login seems invalid or you are already in our database!" );
						}
					} else {
						$success = "none";
						$error = t_( "Your last name and first name are required!" );
					}
				}
			} else {
				$success = "else";
				$error = t_( "This URL does not exist" );
			}
		} else {
			$success = "else";
			$error = t_( "This URL does not exist" );
		}

		$this->view->error = $error;
		$this->view->success = $success;
		$this->view->invitation = t_( "Invitation" );
		$this->view->addLayoutVar ( "title", t_( "Invitation" ) );
		$this->view->submit = t_( "Register" );
		$this->view->mail = t_( "Mail" );
		$this->view->login = t_( "Login" );
		$this->view->name = t_( "Last Name" );
		$this->view->firstName = t_( "First name" );
		$this->view->password = t_( "Password" );
		$this->view->confirmPassword = t_( "Confirm Password" );
		$this->view->mailValue = $mail;
		$this->view->loginValue = $this->_request->getPost('login');
		$this->view->nameValue = strip_tags($this->_request->getPost('name'));
		$this->view->firstNameValue = strip_tags($this->_request->getPost('firstname'));
		$this->view->passwordValue = strip_tags($this->_request->getPost('password'));
		$this->view->passwordValue2 = strip_tags($this->_request->getPost('password2'));
		$this->view->moreInfo = isset($this->moreInfo['invitation']) ? $this->moreInfo['invitation'] : '';
	}

	/**
	 * Action for user to register an account
	 *
	 */
	public function registerAction() {
		$secret = $this->_getParam ( 'secret' ) ? $this->_getParam ( 'secret' ) : 0;

		$error = "none";
		$success = "none";
		if ($secret !== 0) {
			$id = Twindoo_User::getIdBySecret ( $secret );
			if ($id) {
				$user = new Twindoo_User ( $id );
				$user->setActive ();
				$user->commit ();
				$user->getConnected ();
				$success = t_( "Your account have been activated!" );
			} else {
				$success = "else";
				$error = t_( "This URL does not exist" );
			}
		} else {
			if ($this->getRequest()->isPost()) {
				if ($this->_request->getPost('password') == $this->_request->getPost('password2')) {
					if (Captcha::Validate ( $this->_request->getPost('captcha'), true )) {
						if ($this->_request->getPost('name') != "" && $this->_request->getPost('firstname') != "") {
							$user = new Twindoo_User ( );
							$user->setName($this->_request->getPost('name'));
							$user->setFirstName($this->_request->getPost('firstname'));
							if ($user->setLogin ( $this->_request->getPost('login') )) {
								if ($user->create ( $this->_request->getPost('mail'), $this->_request->getPost('password') )) {
									$success = t_( "A mail has been sent. You have to accept your subscription by clicking on the link." );
								} else {
									$error = t_( "Mail seems invalid or you are already in our database!" );
								}
							} else {
								$error = t_( "Login seems invalid or you are already in our database!" );
							}
						} else {
							$error = t_( "Your last name and first name are required!" );
						}
					} else {
						$error = t_( "Security code isn't valid" );
					}
				} else {
					$error = t_( "Passwords do not match." );
				}
			}
		}

		$this->view->error = ($error);
		$this->view->success = ($success);
		$this->view->register = (t_ ( "Subscription" ));
		$this->view->addLayoutVar ( "title", t_( "Subscription" ) );
		$this->view->registerSubmit = (t_ ( "Register" ));
		$this->view->mail = (t_ ( "Mail" ));
		$this->view->login = (t_ ( "Login" ));
		$this->view->password = (t_ ( "Password" ));
		$this->view->confirmPassword = (t_ ( "Confirm Password" ));
		$this->view->securityCode = t_( "Security Code" );
		$this->view->name = t_( "Last Name" );
		$this->view->firstName = t_( "First name" );
		
		$this->view->nameValue = strip_tags($this->_request->getPost('name'));
		$this->view->firstNameValue = strip_tags($this->_request->getPost('firstname'));
		$this->view->mailValue = strip_tags($this->_request->getPost('mail'));
		$this->view->loginValue = strip_tags($this->_request->getPost('login'));
		$this->view->passwordValue = strip_tags($this->_request->getPost('password'));
		$this->view->passwordValue2 = strip_tags($this->_request->getPost('password2'));
		$this->view->moreInfo = isset($this->moreInfo['register']) ? $this->moreInfo['register'] : '';
	}

	/**
	 * Action that send a mail to a user whom forgot his password with an acces code to new password interface
	 *
	 */
	public function forgotAction() {
		$error = "none";
		$success = "none";

		if ($this->getRequest()->isPost()) {
			$user = new Twindoo_User ( $this->_request->getPost('mail') );
			if ($user->getId () != 0) {
				$user->setRandom ( 32 );
				$secret = $user->getRandom ();
				$user->commit ();
				
				$values = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
                $values->forgot = t_('Forgotten Password');
                $values->reinit = t_('Click on this link to reinit your password:');
                $values->thanks = t_('You\'ll be able to create a new one. See you soon on Doonoyz.');
                $values->link = 'http://' . $_SERVER ['SERVER_NAME'] . '/ws/newpassword/secret/' . $secret;
                    
                $user->sendMail(t_( 'Forgot Password?' ), $values, 'forgot');

				$success = t_( "An email was sent, please read it to change your password" );
			} else {
				$error = t_( "This e-mail address is not valid" );
			}
		}

		$this->view->forgot = t_( "Forgot Password?" );
		$this->view->addLayoutVar ( "title", t_( "Subscription" ) );
		$this->view->introforgot = t_( "Please enter your e-mail address to change your password" ) . "<br/>";
		$this->view->submit = t_( "Submit" );
		$this->view->mail = t_( "Mail" ) . ":";
		$this->view->successMsg = $success;
		$this->view->errorMsg = $error;
	}

	/**
	 * Action to create a new password. Only accessible if user has the passkey (sent in mail)
	 *
	 */
	public function newpasswordAction() {
		$secret = $this->_getParam ( 'secret' );
		$success = 'none';
		$error = 'none';

		$user = Twindoo_User::getIdBySecret ( $secret );
		if ($user) {
            if ($this->getRequest()->isPost()) {
				$password = $this->_request->getPost('password');
				$checkpassword = $this->_request->getPost('checkpassword');

				if (($password != $checkpassword) || $password == "") {
					$error = t_( "Check your passwords, they do not match." );
				} else {
					$userObj = new Twindoo_User ( $user );
					$userObj->setPassword ( $password );
					$userObj->commit ();
					$success = t_( "Your password has been changed successfully" );
				}
			}
		} else {
			$success = 'notnone';
			$error = t_( "This URL does not exist" );
		}

		$this->view->forgot = t_( "Forgot Password?" );
		$this->view->addLayoutVar ( "title", t_( "Forgot Password?" ) );
		$this->view->intropassword = t_( "Please enter a new password" ) . "<br/>";
		$this->view->password = t_( "New password" ) . ":";
		$this->view->checkpassword = t_( "Confirm password" ) . ":";
		$this->view->submit = t_( "Change" );
		$this->view->errorMsg = $error;
		$this->view->successMsg = $success;
	}

	/**
	 * Action to log the user in
	 *
	 */
	public function loginAction() {
		if (Twindoo_User::getCurrentUserId ()) {
			$this->_redirect ( "/" );
		}
		$session = new Zend_Session_Namespace(__CLASS__);
		$error = "none";
		if ($this->getRequest()->isPost()) {
			if ($this->_request->getPost('loginType') == 'openId') {
				$consumer = new Zend_OpenId_Consumer();
				if (!$consumer->login($this->_request->getPost('openid_id'))) {
					$error = t_( "OpenID login failed" );
				}
			} else {
				$writer = new Zend_Log_Writer_Stream(realpath(dirname(__FILE__)).'/../../Log/auth.log');
				$logger = new Zend_Log($writer);
				
				$requestedMail = $this->_request->getPost('mail');
				$user = new Twindoo_User ( $requestedMail );
				if ($user->getActive ()) {
					if (($user->getPassword () == sha1 ( sha1 ( $this->_request->getPost('password') , true ), true ))) {
						$user->getConnected ( isset ( $_POST ['rememberme'] ) );
						$this->_redirect ( $session->redirectPage );
					} else {
						$error = t_( "Login failed, check your login/password." );
					}
				} else {
					$error = t_( "Your account isn't active." );
				}
				if ($error != "none") {
					$logger->warn("Auth error for user '$requestedMail' @ " . Twindoo_Utile::getIp());
				}
			}
		} elseif ($this->_request->getParam('openid_mode') != "") {
			if ($this->_request->getParam('openid_mode') == "id_res") {
				$sreg = new Zend_OpenId_Extension_Sreg(array(
														'nickname'=> true,
														'email'=> true), null, 1.1);
				$consumer = new Zend_OpenId_Consumer();
				if (!$consumer->verify($_GET, $id, $profile)) {
					$error = t_( "OpenID login failed" );
				} else {
					$props = $profile->getProperties();
					$user = new Twindoo_User($props['email']);
					if (!$user->getId()) {
						if (!$user->setLogin($props['nickname'])) {
							if (!$user->setLogin($props['nickname'] . 'OpenId')) {
								$error = t_( "OpenID login failed" );
							}
						}
					}
					if ($error == "none") {
						if (!$user->getId()) {
							$user->create($props['email']);
						} else {
							//save here openId profile updates
							//$user->commit();
						}
						if (!$user->getActive() && !$this->getRandom()) {
							//if not active and not invited
							$error = t_( "Login failed, check your login/password." );
						} else {
							$user->getConnected ( false );
							$this->_redirect ( $session->redirectPage );
						}
					}
				}
			} elseif ($this->_request->getParam('openid_mode') == "cancel") {
				$error = t_( "OpenID login cancelled" );
			}
		} else {
			$session->redirectPage = (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '/');
		}

		$this->view->connect = t_( "Connect" );
		$this->view->login = t_( "Login" );
		$this->view->addLayoutVar ( "title", t_( "Connection" ) );
		$this->view->mail = t_( "Doonoyz Account" );
		$this->view->mailSub = t_( "Mail or Login" );
		$this->view->password = t_( "Password" );
		$this->view->openIdLogin = t_( "Login with OpenID!" );
		$this->view->rememberMe = t_( "Remember Me?" );
		$this->view->forgotPassword = t_( "Forgotten password?" );
		$this->view->registerAccount = t_( "No account? Click here to register." );
		$this->view->remembered = (isset ( $_COOKIE ['settings'] ) && $_COOKIE ['settings'] != '0') ? 'true' : 'false';

		$loginValue = "";
		$passwordValue = "";

		/*if (isset ( $_COOKIE ['settings'] ) && $_COOKIE ['settings'] != '0') {
			$values = explode ( ':', $_COOKIE ['settings'] );
			$loginValue = $values [0];
			unset ( $values [0] );
			$passwordValue = implode ( ':', $values );
		}*/

		$this->view->loginValue = strip_tags($this->_request->getPost('mail'));
		$this->view->passwordValue = strip_tags($this->_request->getPost('password'));
		$this->view->errorMsg = $error;
		$this->view->moreInfo = isset($this->moreInfo['login']) ? $this->moreInfo['login'] : '';
	}

	/**
	 * Action to disconnect the user
	 *
	 */
	public function disconnectAction() {
		$this->_helper->viewRenderer->setNoRender ( true );
		if ($this->_request->getParam('token') == Twindoo_Token::getToken ( 'user' ) ) {
			Twindoo_User::disconnect();
			if (! isset ( $_POST ['noRedirect'] ))
				$this->_redirect ( (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '/') );
		}
		$this->_redirect('/');
	}
	
	/**
	 * Unregister page
	 */
	public function unregisterAction() {
		$message = t_("Are you sure you want to leave us and close your account?");
		$success = false;
		$error = false;
		if ($this->_request->getPost('no') || $this->_request->getPost('no_x')) {
			$this->_redirect('/');
		} else if ($this->_request->getPost('yes') || $this->_request->getPost('yes_x')) {
			$message = t_("A mail has been sent to you, you have to click the link inside to unsubscribe.");
			$user = new Twindoo_User(Twindoo_User::getCurrentUserId());
			$user->setRandom ( 32 );
			$secret = $user->getRandom ();

			$values = new ArrayObject(Array(), ArrayObject::ARRAY_AS_PROPS);
			$values->thanks = t_('Thanks for your participation to Doonoyz');
			$values->accountDeleted = t_('You received this mail because you asked us to delete your account. Click this link to confirm this request:');
			$values->sorry = t_('Sorry to see you go. See you soon on Doonoyz.');
			$values->link = 'http://' . $_SERVER ['SERVER_NAME'] . '/ws/unregister/secret/' . $secret;
						
			$user->sendMail(t_('Unregistration'), $values, 'unregister');
			$user->commit();
			$success = true;
		} else if ($this->_request->getParam('secret')) {
			$user = new Twindoo_User(Twindoo_User::getIdBySecret($this->_request->getParam('secret')));
			if ($user->getId()) {
				$user->delete();
				$message = t_("Your account has been deleted successfully, see you later!");
				$success = true;
				Twindoo_User::disconnect();
			} else {
				$message = t_( "This URL does not exist" );
				$error = true;
			}
		}
		
		$this->view->text = array(
			'title' => t_('Unregister'),
			'yes' => t_('Yes, close this account'),
			'no' => t_('No, it was just a mistake, forget that'),
		);
		$this->view->success = $success;
		$this->view->error = $error;
		$this->view->message = $message;
		$this->view->addLayoutVar ( 'title', t_( "Unregister" ) );
	}
}