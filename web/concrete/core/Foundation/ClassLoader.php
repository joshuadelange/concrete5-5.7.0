<?php

namespace Concrete\Core\Foundation;
use \Concrete\Core\Foundation\Object;
use \Concrete\Core\Package\Package;
use \Concrete\Core\Foundation\ModifiedPSR4ClassLoader as SymfonyClassloader;
use \Symfony\Component\ClassLoader\MapClassLoader as SymfonyMapClassloader;

class ClassLoader  {

	static $instance;
	protected $classAliases = array();

	public static function getInstance() {
		if (!isset(static::$instance)) {
			static::$instance = new Classloader();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->setupFileAutoloader();
		$this->setupAliasAutoloader();
		$this->setupMapClassAutoloader();
	}

	protected function setupMapClassAutoloader() {
		$mapping = array(
		    'Loader' => DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/Legacy/Loader.php',
		    'TaskPermission' => DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/Legacy/TaskPermission.php',
		    'FilePermissions' => DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/Legacy/FilePermissions.php'
		);

		$loader = new SymfonyMapClassloader($mapping);
		$loader->register();
	}

	protected function setupAliasAutoloader() {
		$loader = $this;
		spl_autoload_register(function($class) use ($loader) {
			$list = ClassAliasList::getInstance();
			if (array_key_exists($class, $aliases = $list->getRegisteredAliases())) {
				// we have an alias for it, but we don't have it yet loaded
				// (because after all, we're in the auto loader.)
				$fullClass = $aliases[$class];
				if (!class_exists($fullClass, false)) {
					spl_autoload_call($fullClass);
				}
				// finally, we set up a class alias for this list. We do this now because
				// we don't know earlier what namespace it'll be in
				class_alias($fullClass, $class);
			}
		});
	}

	public function registerPackage(Package $pkg) {
		$symfonyLoader = new SymfonyClassloader();
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Attribute', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_ATTRIBUTES);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Authentication', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_AUTHENTICATION);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Block', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_BLOCKS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Theme', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_THEMES);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Controller', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_CONTROLLERS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Job', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_JOBS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Package\\' . camelcase($pkg->getPackageHandle()) . '\\Core', DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/' . DIRNAME_CLASSES);
		$symfonyLoader->register();
	}

	protected function setupFileAutoloader() {
		$symfonyLoader = new SymfonyClassloader();
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Attribute', DIR_BASE_CORE . '/' . DIRNAME_ATTRIBUTES);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Authentication', DIR_BASE_CORE . '/' . DIRNAME_AUTHENTICATION);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Block', DIR_BASE_CORE . '/' . DIRNAME_BLOCKS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Theme', DIR_BASE_CORE . '/' . DIRNAME_THEMES);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Controller', DIR_BASE_CORE . '/' . DIRNAME_CONTROLLERS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Job', DIR_BASE_CORE . '/' . DIRNAME_JOBS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Core', DIR_BASE_CORE . '/' . DIRNAME_CLASSES);

		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Attribute', DIR_APPLICATION. '/' . DIRNAME_ATTRIBUTES);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Authentication', DIR_APPLICATION . '/' . DIRNAME_AUTHENTICATION);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Block', DIR_APPLICATION . '/' . DIRNAME_BLOCKS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Theme', DIR_APPLICATION . '/' . DIRNAME_THEMES);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Controller', DIR_APPLICATION . '/' . DIRNAME_CONTROLLERS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Job', DIR_APPLICATION . '/' . DIRNAME_JOBS);
		$symfonyLoader->addPrefix(NAMESPACE_SEGMENT_APPLICATION . '\\Core', DIR_APPLICATION . '/' . DIRNAME_CLASSES);

		$symfonyLoader->register();
	}



}