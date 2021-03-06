<?php

/**
 * A Magento 2 module named Experius/EmailCatcher
 * Copyright (C) 2016 Derrick Heesbeen
 *
 * This file included in Experius/EmailCatcher is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Experius\EmailCatcher\Mail;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;

class Transport implements TransportInterface
{

    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    private $transport;

    private $message;

    private $scopeConfig;

    public function __construct(
        \Zend_Mail_Transport_Sendmail $transport,
        MessageInterface $message,
        ScopeConfigInterface $scopeConfig
    ) {
        if (!$message instanceof \Zend_Mail) {
            throw new \InvalidArgumentException('The message should be an instance of \Zend_Mail');
        }
        $this->transport = $transport;
        $this->message = $message;
        $this->scopeConfig = $scopeConfig;
    }

    public function setMessage(\Magento\Framework\Mail\Message $message)
    {
        $this->message = $message;
    }

    public function sendMessage()
    {
        try {

            $isSetReturnPath = $this->scopeConfig->getValue(
                self::XML_PATH_SENDING_SET_RETURN_PATH,
                ScopeInterface::SCOPE_STORE
            );
            $returnPathValue = $this->scopeConfig->getValue(
                self::XML_PATH_SENDING_RETURN_PATH_EMAIL,
                ScopeInterface::SCOPE_STORE
            );

            if ($isSetReturnPath == '1') {
                $this->message->setReturnPath($this->message->getFrom());
            } elseif ($isSetReturnPath == '2' && $returnPathValue !== null) {
                $this->message->setReturnPath($returnPathValue);
            }
            $this->transport->send($this->message);
        } catch (\Exception $e) {
            throw new MailException(__($e->getMessage()), $e);
        }
    }

    public function getMessage()
    {
        return $this->message;
    }
}

