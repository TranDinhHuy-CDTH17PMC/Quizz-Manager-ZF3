<?php
//authenticate
namespace Users\Service;

class AuthManager
{
    private $authenticationService;
    private $sessionManager;
    private $config;

    public function __construct($authenticationService, $sessionManager, $config)
    {
        $this->authenticationService = $authenticationService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }
    public function login($username, $password, $rememberMe)
    {
        //Kiểm tra đã đăng nhập chưa
        if ($this->authenticationService->hasIdentity()) {
            throw new \Exception('Bạn đã đăng nhập');
        }
        $authAdapter = $this->authenticationService->getAdapter();
        $authAdapter->setUsername($username);
        $authAdapter->setPassword($password);

        $result = $this->authenticationService->authenticate();
        if ($result->getCode() == $result::SUCCESS && $rememberMe) {
            $this->sessionManager->rememberMe(86400 * 30);
        }
        return $result;
    }
    public function logout()
    {
        if ($this->authenticationService->hasIdentity()) {
            $this->authenticationService->clearIdentity();
        } else {
            throw new \Exception('Bạn chưa đăng nhập');
        }
    }
    public function filterAccess($controllerName, $actionName)
    {
        if (isset($this->config['controllers'][$controllerName])) {

            $controllers = $this->config['controllers'][$controllerName];

            foreach ($controllers as $controller) {

                $actions  = $controller['actions'];
                $allow = $controller['allow'];

                if (in_array($actionName, $actions)) {
                    if ($allow == "all") {
                        return true;
                    } elseif ($allow = "limit" && $this->authenticationService->hasIdentity()) {
                        return true;
                    } else
                        return false;
                }
            }
        }
        return true;
    }
}
