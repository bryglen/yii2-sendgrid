<?php
/**
 * @author Bryan Jayson Tan <bryantan16@gmail.com>
 * @link http://bryantan.info
 * @date 3/24/14
 * @time 6:39 PM
 */

namespace bryglen\sendgrid;

use yii\mail\BaseMailer;
use Yii;

class Mailer extends BaseMailer
{
    public $username;
    public $password;
    public $api;

    private $_sendGridMailer;

    /**
     * @return \SendGrid
     */
    public function getSendGridMailer()
    {
        if (is_object($this->_sendGridMailer)) {
            $this->_sendGridMailer = $this->createSendGridMailer();
        }

        return $this->_sendGridMailer;
    }

    /**
     * @return \SendGrid
     */
    public function createSendGridMailer()
    {
        $sendgrid = new \SendGrid($this->username, $this->password);

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
        Yii::info('Sending email "' . $message->getSubject() . '" to "' . $address . '"', __METHOD__);

        return $this->getSendGridMailer()->send($message->getSendGridMessage()) > 0;
    }
} 