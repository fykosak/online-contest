<?php

namespace App\FrontendModule\Presenters;

use Nette,
    Nette\Application\Responses\JsonResponse,
    App\Model\NotificationModel;

class NoticeboardPresenter extends BasePresenter {
    /** @var NotificationModel @inject*/
    public $notificationModel;
    
    /** @var  Nette\Http\Request @inject*/
    public $httpRequest;
    
    public function renderDefault() {
        $this->template->notifications = $this->notificationModel->findActive($this->lang);
        $this->setPageTitle(_("Důležitá oznámení"));
    }
    
    public function renderAdd() {
        if(!$this->user->isAllowed('noticeboard', 'add')) {
            $this->error('Nemáte oprávnění pro přidání notifikace.', Nette\Http\Response::S403_FORBIDDEN);
        }
        $this->setPageTitle(_("Přidat notifikaci"));
    }
    
    public function actionAjax() {
        if(!$this->isAjax()) {
            throw new Nette\NotSupportedException;
        }
        
        $lang = $this->lang;
        $lastAsked = (int) $this->httpRequest->getQuery('lastAsked');
        $pollInterval = $this->context->parameters['notifications']['pollInterval'];
        $now = time();
        
        if($lastAsked == NULL) {
            $notification = array(
                'message' => _('Sledujte prosím nástěnku.'),
                'created' => 0
            );
            $notifications[] = $notification;
        }
        else {
            $notifications = $this->notificationModel->findNew($lastAsked, $lang);
        }
        
        $this->template->setFile(__DIR__.'/../templates/Noticeboard/@notificationsContainer.latte');
        $this->template->notifications = $notifications;
            
        $payload = array(
            'html' => (string) $this->template,
            'lastAsked' => $now,
            'pollInterval' => $pollInterval
        );
        $this->sendResponse(new JsonResponse($payload));
    }
    
    protected function createComponentNotificationForm($name) {
        return new \NotificationFormComponent($this->notificationModel, $this, $name);
    }
}