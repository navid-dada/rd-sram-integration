<?php
/**
 */
namespace OCA\FederatedGroups;

use OCP\Share\IProviderFactory;
use OC\Share20\ProviderFactory;

use OCA\FederatedFileSharing\AddressHandler;
use OCA\FederatedFileSharing\DiscoveryManager;
use OCA\FederatedFileSharing\Ocm\NotificationManager;
use OCA\FederatedFileSharing\Ocm\Permissions;
use OCA\FederatedFileSharing\Notifications;
use OCA\FederatedFileSharing\TokenHandler;


use OCP\IServerContainer;

class ShareProviderFactory extends ProviderFactory implements IProviderFactory {

	// These two variables exist in the parent class,
	// but need to be redeclared here at the child class
	// level because they're private:

	/** @var IServerContainer */
	private $serverContainer;

	/** @var DefaultShareProvider */
	private $defaultProvider = null;

	public function __construct(IServerContainer $serverContainer) {
		parent::__construct($serverContainer);
		$this->serverContainer = $serverContainer;
	}
	protected function defaultShareProvider() {
		error_log("child defaultShareProvider!");
		if ($this->defaultProvider === null) {
			$addressHandler = new \OCA\FederatedFileSharing\AddressHandler(
				\OC::$server->getURLGenerator(),
				\OC::$server->getL10N('federatedfilesharing')
			);
			$discoveryManager = new \OCA\FederatedFileSharing\DiscoveryManager(
				\OC::$server->getMemCacheFactory(),
				\OC::$server->getHTTPClientService()
			);
			$notificationManager = new \OCA\FederatedFileSharing\Ocm\NotificationManager(
				new \OCA\FederatedFileSharing\Ocm\Permissions()
			);
			$notifications = new \OCA\FederatedFileSharing\Notifications(
				$addressHandler,
				\OC::$server->getHTTPClientService(),
				$discoveryManager,
				$notificationManager,
				\OC::$server->getJobList(),
				\OC::$server->getConfig()
			);
			$tokenHandler = new \OCA\FederatedFileSharing\TokenHandler(
				\OC::$server->getSecureRandom()
			);

			$this->defaultProvider = new ShareProvider(
				$this->serverContainer->getDatabaseConnection(),
				$this->serverContainer->getUserManager(),
				$this->serverContainer->getGroupManager(),
				$addressHandler,
				$notifications,
				$tokenHandler,
				$this->serverContainer->getLazyRootFolder()
			);
		}

		return $this->defaultProvider;
	}
}
