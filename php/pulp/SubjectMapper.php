<?php

	require_once APP_ROOT.'bin/_sections_a.php';

	class SubjectMapper {
		
		static private $instance;
		private $main;
		private $navigation;
		private $subject;
		private $section;
		private $package;
		private $subjectIdent;
		private $sectionIdent;
		
		static public function inst() {
			if (!self::$instance) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		private function __construct(){}
		
		public function getSubject() {
			
			if (!isset($this->subject)) {
				if (Auth_f::inst()->getFromSession('role') == ROLE_ADMIN || 
					Auth_f::inst()->getFromSession('role') == ROLE_EDITOR
				){
					if (isset($_GET['subjectId'])) {
						$this->subject = $_GET['subjectId'];
					} else {
						$this->subject = Subjects_a::inst()->getFirstSubjectId();
					}
				} else {
					$packageId = $this->getPackage();
					require_once APP_ROOT.'bin/_packages.php';
					$packages = new Packages(DBs::inst());
					$this->subject = $packages->getSubjectIdByPackageId($packageId);
				}
			}
			return $this->subject;
			
		}
		
		public function getPackage() {
			
			if (!isset($this->package)) {
				if (isset($_GET['packageId'])) {
					$this->package = $_GET['packageId'];
				} else {
					$packages = Auth_f::inst()->getFromSession('packages');
					if (!count($packages)) return null;
					$this->package = $packages[0];
				}
			}
			return $this->package;
			
		}
		
		public function getSection() {
			
			if (!isset($this->section)) {
				if (isset($_GET['sectionId'])) {
					$this->section = $_GET['sectionId'];
				} else if ($this->getSubject()) {
					$this->section = Sections_a::inst()->getDefaultSectionIdBySubjectId($this->getSubject());
				} else {
					return false;
				}
			}
			return $this->section;
			
		}

		public function getSectionIdent() {
			
			if (!isset($this->sectionIdent)) {
				$this->sectionIdent = Sections_a::inst()->getSectionIdentBySectionId($this->getSection());
			}
			return $this->sectionIdent;
			
		}
		
		public function getSubjectIdent() {
			
			if (!isset($this->subjectIdent)) {
				$this->subjectIdent = Subjects_a::inst()->getSubjectIdentBySubjectId($this->getSubject());
			}
			return $this->subjectIdent;
			
		}
		
		public function getMain() {
			
			if (!isset($this->main)) {
				if ($this->getSubject()) {
					$file = $this->buildFileName('main');
					if (file_exists($file)) {
						require_once $file;
						$class = $this->buildClassName('main');
						$this->main = new $class();
					} else {
						trigger_error ("Wrong subject/section combination ($file)", E_USER_ERROR);
					}
				} else {
					trigger_error ('Subject is not defined', E_USER_ERROR);
				}
			}
			return $this->main;
						
		}
		
		public function getNavigation() {
			
			if (!isset($this->navigation)) {
				if ($this->getSubject()) {
					$file = $this->buildFileName('navigation');
					if (file_exists($file)) {
						require_once $file;
						$class = $this->buildClassName('navigation');
						$this->navigation = new $class();
					} else {
						trigger_error ("Wrong subject/section combination ($file)", E_USER_ERROR);
					}
				} else {
					trigger_error ('Subject is not defined', E_USER_ERROR);
				}
			}
			return $this->navigation;
						
		}
		
		private function buildFileName($prefix) {
			
			return "./bin/$prefix/" . $prefix . '_' . $this->getSubjectIdent() . '_' . $this->getSectionIdent() . '.php';
			
		}
		
		private function buildClassName($prefix) {
			
			return ucfirst($prefix) . '_' . $this->getSubjectIdent() . '_' . $this->getSectionIdent();
			
		}
		
	}