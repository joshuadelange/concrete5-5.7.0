<?
namespace Concrete\Controller\Panel\Page;
use \Concrete\Controller\Backend\UserInterface\Page as BackendUIPageController;
use Permissions;
use PageTemplate;
use PageTheme;
use Request;
use PageEditResponse;
use Loader;
use Response;
use View;
class Design extends BackendUIPageController {

	protected $viewPath = '/panels/page/design';
	public function canAccess() {
		return $this->permissions->canEditPageTemplate() || $this->permissions->canEditPageTheme();
	}

	public function view() {
		$c = $this->page;
		$cp = $this->permissions;

		$pt = $c->getPageTypeObject();
		if (is_object($pt)) {
			$_templates = $pt->getPageTypePageTemplateObjects();
		} else {
			$_templates = PageTemplate::getList();
		}

		$pTemplateID = $c->getPageTemplateID();
		$templates = array();
		if ($pTemplateID) {
			$selectedTemplate = PageTemplate::getByID($pTemplateID);
			$templates[] = $selectedTemplate;
		}

		foreach($_templates as $tmp) {
			if (!in_array($tmp, $templates)) {
				$templates[] = $tmp;
			}
		}

		$tArrayTmp = array_merge(PageTheme::getGlobalList(), PageTheme::getLocalList());
		$_themes = array();
		foreach($tArrayTmp as $pt) {
			if ($cp->canEditPageTheme($pt)) {
				$_themes[] = $pt;
			}
		}

		$pThemeID = $c->getCollectionThemeID();
		if ($pThemeID) {
			$selectedTheme = PageTheme::getByID($pThemeID);
		} else {
			$selectedTheme = PageTheme::getSiteTheme();
		}

		$themes = array($selectedTheme);
		foreach($_themes as $t) {
			if (!in_array($t, $themes)) {
				$themes[] = $t;
			}
		}

		$templatesSelect = array();
		$themesSelect = array();
		foreach($_themes as $pt) {
			$themesSelect[$pt->getThemeID()] = $pt->getThemeDisplayName();
		}
		foreach($_templates as $pt) {
			$templatesSelect[$pt->getPageTemplateID()] = $pt->getPageTemplateDisplayName();
		}

		$this->set('templatesSelect', $templatesSelect);
		$this->set('themesSelect', $themesSelect);
		$this->set('themes', $themes);
		$this->set('templates', $templates);
		$this->set('selectedTheme', $selectedTheme);
		$this->set('selectedTemplate', $selectedTemplate);
	}

	public function preview() {
		$this->setViewObject(new View('/panels/details/page/preview'));
	}

	public function preview_contents() {
		$req = Request::getInstance();
		$req->setCurrentPage($this->page);
		$controller = $this->page->getPageController();
		$view = $controller->getViewObject();
		if ($_REQUEST['pTemplateID']) {
			$pt = PageTemplate::getByID(Loader::helper('security')->sanitizeInt($_REQUEST['pTemplateID']));
			if (is_object($pt)) {
				$view->setCustomPageTemplate($pt);
			}
		}
		if ($_REQUEST['pThemeID']) {
			$pt = PageTheme::getByID(Loader::helper('security')->sanitizeInt($_REQUEST['pThemeID']));
			if (is_object($pt)) {
				$view->setCustomPageTheme($pt);
			}
		}
		$req->setCustomRequestUser(-1);
		$response = new Response();
		$content = $view->render();
		$response->setContent($content);
		return $response;
	}

	public function submit() {
		if ($this->validateAction()) {
			$cp = $this->permissions;
			$c = $this->page;

			$pl = false;
			if ($_POST['pThemeID']) { 
				$pl = PageTheme::getByID($_POST['pThemeID']);
			}
			$nvc = $c->getVersionToModify();				
			$data = array();
			if (is_object($pl)) { 
				$nvc->setTheme($pl);
			}

			if (!$c->isGeneratedCollection()) {
			
				if ($_POST['pTemplateID'] && $cp->canEditPageTemplate()) {
					// now we have to check to see if you're allowed to update this page to this page type.
					// We do this by checking to see whether the PARENT page allows you to add this page type here.
					// if this is the home page then we assume you are good
					
					$template = PageTemplate::getByID($_POST['pTemplateID']);
					$proceed = true;
					$pagetype = $c->getPageTypeObject();
					if (is_object($pagetype)) {
						$templates = $pagetype->getPageTypePageTemplateObjects();
						if (!in_array($template, $templates)) {
							$proceed = false;
						}
					}
					if ($proceed) {
						$data['pTemplateID'] = $_POST['pTemplateID'];
						$nvc->update($data);
					}						
				}				
			}

			$r = new PageEditResponse();
			$r->setPage($c);
			$r->setRedirectURL(BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=' . $c->getCollectionID());
			$r->outputJSON();
		}
	}
}