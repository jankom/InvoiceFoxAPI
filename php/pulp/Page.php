<?php

	class Page {
		
		public function setTitle($title) {
			
			global $LOCALS;
			$LOCALS['pageTitle'] = $title;
			
		}
		
		static public function showErrorReportingLink() {
			
			$info = array('ip'=>$_SERVER['REMOTE_ADDR'],
					   'timestamp'=>time(),
					   'user'=>Auth_f::inst()->getFromSession('id_users'),
					   'url'=>htmlspecialchars($_SERVER['REQUEST_URI'])
					   );
			
			return '<script type="text/javascript">errorReporting = ' . json_encode($info) . '</script>' . '<a onclick="toogleErrorReporting(this);">Javi napako na strani</a>';
			
			
		}
		
		static public function hint($name, $cols=false) {

			$languageArray = LanguageMapper::inst();
			
			$return = '<p class="toggle"><a href="javascript:;" onclick="toogleHint(this);" class="hint off"><span>Namig</span></a></p>';
			$cols = ($cols) ? ' cols' : '';
			$return .= '<div class="hint' . $cols . '">';
			$rows = explode("\n", $languageArray["Hint $name"]);

			foreach ($rows as $row) {
				$return .= '<p>' .  trim($row) . '</p>';
			}
			$return .= '</div>';
			
			return $return;
			
		}
		
		static public function tooltip($name) {

			$languageArray = LanguageMapper::inst();
			return $languageArray['Tooltip ' . $name];
			
		}
		
	}