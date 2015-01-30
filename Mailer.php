<?php
/**
 * @author Bryan Jayson Tan <bryantan16@gmail.com>
 * @link http://bryantan.info
 */

namespace bryglen\sendgrid;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\mail\BaseMailer;
use Yii;

/**
 * Mailer implements a mailer based on SendGrid.
 *
 * To use Mailer, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => [
 *     ...
 *     'sendGrid' => [
 *         'class' => 'bryglen\sendgrid\Mailer',
 *         'username' => 'your_user_name',
 *         'password' => 'your password here',
 *         //'viewPath' => '@app/views/mail', // your view path here
 *     ],
 *     ...
 * ],
 * ~~~
 *
 * To send an email, you may use the following code:
 *
 * ~~~
 * $sendGrid = Yii::$app->sendGrid;
 * $message = $sendGrid->compose('contact/html', ['contactForm' => $form])
 * $message->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send($sendGrid);
 * ~~~
 *
 * Note: you need to pass a parameter in the send() if your component is not `mail`
 *
 * @see http://sendgrid.com/
 * @package bryglen\sendgrid
 */
class Mailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = 'bryglen\sendgrid\Message';
    /**
     * @var string the username for the sendgrid api
     */
    public $username;
    /**
     *
     * @var string the password for the sendgrid api
     */
    public $password;
    /**
     * @var string a json string of the raw response from the sendgrid
     */
    private $_rawResponse;
    /**
     * @var array a list of errors
     */
    private $_errors = [];
    /**
     * @var string Send grid mailer instance
     */
    private $_sendGridMailer;

    /**
     * @return \SendGrid Send grid mailer instance
     */
    public function getSendGridMailer()
    {
        if (!is_object($this->_sendGridMailer)) {
            $this->_sendGridMailer = $this->createSendGridMailer($this->username, $this->password);
        }

        return $this->_sendGridMailer;
    }

    /**
     * Create send grid mail instance
     * @param string $username the username for the sendgrid api
     * @param string $password the password for the sendgrid api
     * @return \SendGrid
     * @throws \yii\base\InvalidConfigException
     */
    public function createSendGridMailer($username, $password)
    {
        if (!$username) {
            throw new InvalidConfigException("Username cannot be empty.");
        }
        if (!$password) {
            throw new InvalidConfigException("Password cannot be empty.");
        }
        $sendgrid = new \SendGrid($username, $password);

        return $sendgrid;
    }

    /**
     * @inheritdoc
     */
    public function sendMessage($message)
    {
        $address = $message->getTo();
        if (is_array($address)) {
            $address = implode(', ', array_keys($address));
        }

        $this->setRawResponse($this->getSendGridMailer()->send($message->getSendGridMessage()));
        $responseArray = Json::decode($this->getRawResponse());

        if (!isset($responseArray['message'])) {
            throw new \Exception('Invalid SendGrid response format');
        } elseif ($responseArray['message'] === "success") {
            // reset the error if success
            $this->setErrors(array());
            return true;
        } elseif (isset($responseArray['errors'])) {
            // reset the error if success
            $this->setErrors($responseArray['errors']);
            return false;
        }
    }

    /**
     * @return string get the raw response, this can be a json string or empty string
     */
    public function getRawResponse()
    {
        return $this->_rawResponse;
    }

    /**
     * @param string $value set a raw response, the response get from [[sendMessage()]] is an object, convert it to json
     */
    public function setRawResponse($value)
    {
        $this->_rawResponse = Json::encode($value);
    }

    /**
     * @return array a list of errors, the response get [[sendMessage()]]
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param array $errors a array of errors
     */
    public function setErrors($errors)
    {
        $this->_errors = $errors;
    }
} 