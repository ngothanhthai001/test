<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GoogleAccountLogin
 */


namespace Amasty\GoogleAccountLogin\Observer;

use Amasty\GoogleAccountLogin\Model\Response;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Message\ManagerInterface ;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\App\Request\DataPersistor;

class Login implements ObserverInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var \Amasty\GoogleAccountLogin\Model\ResourceModel\User
     */
    private $user;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $session;

    /**
     * @var \Magento\User\Model\User
     */
    private $userModel;

    /**
     * @var AdminSessionsManager
     */
    protected $adminSessionsManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var DataPersistor
     */
    protected $dataPersistor;

    /**
     * @var \Amasty\GoogleAccountLogin\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Response $response,
        \Amasty\GoogleAccountLogin\Model\ResourceModel\User $user,
        \Magento\Backend\Model\Auth\Session $session,
        \Magento\User\Model\User $userModel,
        AdminSessionsManager $adminSessionsManager,
        DateTime $dateTime,
        ManagerInterface $messageManager,
        DataPersistor $dataPersistor,
        EventManager $eventManager,
        \Amasty\GoogleAccountLogin\Model\ConfigProvider $configProvider
    ) {
        $this->response = $response;
        $this->user = $user;
        $this->session = $session;
        $this->userModel = $userModel;
        $this->adminSessionsManager = $adminSessionsManager;
        $this->dateTime = $dateTime;
        $this->messageManager = $messageManager;
        $this->eventManager = $eventManager;
        $this->dataPersistor = $dataPersistor;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            $request = $observer->getRequest();
            if (!$request->getParam('SAMLResponse')
                || $this->dataPersistor->get('am_observer_login')
                || !$this->configProvider->isEnabled()
            ) {
                $this->dataPersistor->clear('am_observer_login');
                return;
            }
            $this->dataPersistor->set('am_observer_login', true);

            $userData = $this->getUserData($request->getParam('SAMLResponse'));
            if ($this->isValidData($userData)) {
                if ($this->session->isLoggedIn()) {
                    $this->session->processLogout();
                }

                $this->login($userData);
                $this->prepareSessionInfo();
                $this->eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->userModel]
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('You did not sign in correctly or your account is temporarily disabled.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $param
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getUserData($param)
    {
        $userData = $this->response->getUserData($param);

        return $this->user->loadByEmail($userData['emailAddress'] ?? '');
    }

    /**
     * @param $userData
     * @return bool
     */
    private function isValidData($userData)
    {
        $isActive = $userData['is_active'] ?? false;

        return $isActive == '1';
    }

    /**
     * @param $userData
     */
    private function login($userData)
    {
        $this->userModel->setData($userData);
        $this->eventManager->dispatch(
            'admin_user_authenticate_after',
            [
                'username' => $this->userModel->getUserName(),
                'password' => '',
                'user' => $this->userModel,
                'result' => true
            ]
        );

        $this->session->setUser($this->userModel);
        $this->adminSessionsManager->processLogin();
        if ($this->adminSessionsManager->getCurrentSession()->isOtherSessionsTerminated()) {
            $this->messageManager->addWarningMessage(__('All other open sessions for this account were terminated.'));
        }

        $this->session->refreshAcl();
    }

    private function prepareSessionInfo()
    {
        $sessionInfo = $this->adminSessionsManager->getCurrentSession();
        $sessionInfo->setUpdatedAt($this->dateTime->gmtTimestamp());
        $sessionInfo->setStatus($sessionInfo::LOGGED_IN);
    }
}
