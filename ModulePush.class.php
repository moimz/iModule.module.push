<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 홈페이지 내 각종 알림기능과 관련된 전반적인 기능을 관리한다.
 * 
 * @file /modules/keyword/ModulePush.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 23.
 */
class ModulePush {
	/**
	 * iModule 및 Module 코어클래스
	 */
	private $IM;
	private $Module;
	
	/**
	 * DB 관련 변수정의
	 *
	 * @private object $DB DB접속객체
	 * @private string[] $table DB 테이블 별칭 및 원 테이블명을 정의하기 위한 변수
	 */
	private $DB;
	private $table;
	
	/**
	 * 언어셋을 정의한다.
	 * 
	 * @private object $lang 현재 사이트주소에서 설정된 언어셋
	 * @private object $oLang package.json 에 의해 정의된 기본 언어셋
	 */
	private $lang = null;
	private $oLang = null;
	
	/**
	 * class 선언
	 *
	 * @param iModule $IM iModule 코어클래스
	 * @param Module $Module Module 코어클래스
	 * @see /classes/iModule.class.php
	 * @see /classes/Module.class.php
	 */
	function __construct($IM,$Module) {
		/**
		 * iModule 및 Module 코어 선언
		 */
		$this->IM = $IM;
		$this->Module = $Module;
		
		/**
		 * 모듈에서 사용하는 DB 테이블 별칭 정의
		 * @see 모듈폴더의 package.json 의 databases 참고
		 */
		$this->table = new stdClass();
		$this->table->push = 'push_table';
		$this->table->config = 'push_config_table';

		/**
		 * 알림서비스 수신하기 위한 자바스크립트를 로딩한다.
		 * 알림모듈은 글로벌모듈이기 때문에 모듈클래스 선언부에서 선언해주어야 사이트 레이아웃에 반영된다.
		 */
		$this->IM->addHeadResource('script',$this->Module->getDir().'/scripts/push.js');
	}
	
	/**
	 * 모듈 코어 클래스를 반환한다.
	 * 현재 모듈의 각종 설정값이나 모듈의 package.json 설정값을 모듈 코어 클래스를 통해 확인할 수 있다.
	 *
	 * @return Module $Module
	 */
	function getModule() {
		return $this->Module;
	}
	
	/**
	 * 모듈 설치시 정의된 DB코드를 사용하여 모듈에서 사용할 전용 DB클래스를 반환한다.
	 *
	 * @return DB $DB
	 */
	function db() {
		if ($this->DB == null || $this->DB->ping() === false) $this->DB = $this->IM->db($this->getModule()->getInstalled()->database);
		return $this->DB;
	}
	
	/**
	 * 모듈에서 사용중인 DB테이블 별칭을 이용하여 실제 DB테이블 명을 반환한다.
	 *
	 * @param string $table DB테이블 별칭
	 * @return string $table 실제 DB테이블 명
	 */
	function getTable($table) {
		return empty($this->table->$table) == true ? null : $this->table->$table;
	}
	
	/**
	 * URL 을 가져온다.
	 *
	 * @param string $view
	 * @param string $idx
	 * @return string $url
	 */
	function getUrl($view=null,$idx=null) {
		return $this->IM->getUrl(null,null,$view,$idx);
	}
	
	/**
	 * view 값을 가져온다.
	 *
	 * @return string $view
	 */
	function getView() {
		return $this->IM->getView();
	}
	
	/**
	 * idx 값을 가져온다.
	 *
	 * @return string $idx
	 */
	function getIdx() {
		return $this->IM->getIdx();
	}
	
	/**
	 * [코어] 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $protocol API 호출 프로토콜 (get, post, put, delete)
	 * @param string $api API명
	 * @param any $idx API 호출대상 고유값
	 * @param object $params API 호출시 전달된 파라메터
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 */
	function getApi($protocol,$api,$idx=null,$params=null) {
		$data = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeGetApi',$this->getModule()->getName(),$api,$values);
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/api/'.$api.'.'.$protocol.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/api/'.$api.'.'.$protocol.'.php';
		}
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterGetApi',$this->getModule()->getName(),$api,$values,$data);
		
		return $data;
	}
	
	/**
	 * [사이트관리자] 모듈 관리자패널 구성한다.
	 *
	 * @return string $panel 관리자패널 HTML
	 */
	function getAdminPanel() {
		/**
		 * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Module = $this;
		
		ob_start();
		INCLUDE $this->getModule()->getPath().'/admin/index.php';
		$panel = ob_get_contents();
		ob_end_clean();
		
		return $panel;
	}
	
	/**
	 * [사이트관리자] 모듈의 전체 컨텍스트 목록을 반환한다.
	 *
	 * @return object $lists 전체 컨텍스트 목록
	 */
	function getContexts() {
		$lists = array();
		foreach ($this->getText('context') as $context=>$title) {
			$lists[] = array('context'=>$context,'title'=>$title);
		}
		
		return $lists;
	}
	
	/**
	 * 특정 컨텍스트에 대한 제목을 반환한다.
	 *
	 * @param string $context 컨텍스트명
	 * @return string $title 컨텍스트 제목
	 */
	function getContextTitle($context) {
		return $this->getText('context/'.$context);
	}
	
	/**
	 * [사이트관리자] 모듈의 컨텍스트 환경설정을 구성한다.
	 *
	 * @param object $site 설정대상 사이트
	 * @param string $context 설정대상 컨텍스트명
	 * @return object[] $configs 환경설정
	 */
	function getContextConfigs($site,$context) {
		$configs = array();
		
		$templet = new stdClass();
		$templet->title = $this->IM->getText('text/templet');
		$templet->name = 'templet';
		$templet->type = 'select';
		$templet->data = array();
		
		$templet->data[] = array('#',$this->getText('admin/configs/form/default_setting'));
		
		$templets = $this->getModule()->getTemplets();
		for ($i=0, $loop=count($templets);$i<$loop;$i++) {
			$templet->data[] = array($templets[$i]->getName(),$templets[$i]->getTitle().' ('.$templets[$i]->getDir().')');
		}
		
		$templet->value = count($templet->data) > 0 ? $templet->data[0][0] : '#';
		$configs[] = $templet;
		
		return $configs;
	}
	
	/**
	 * 사이트맵에 나타날 뱃지데이터를 생성한다.
	 *
	 * @param string $context 컨텍스트종류
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return object $badge 뱃지데이터 ($badge->count : 뱃지숫자, $badge->latest : 뱃지업데이트 시각(UNIXTIME), $badge->text : 뱃지텍스트)
	 * @todo check count information
	 */
	function getContextBadge($context,$config) {
		/**
		 * null 일 경우 뱃지를 표시하지 않는다.
		 */
		return null;
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @param string $replacement 일치하는 언어코드가 없을 경우 반환될 메세지 (기본값 : null, $code 반환)
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getText($code,$replacement=null) {
		if ($this->lang == null) {
			if (is_file($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->getModule()->getPackage()->language && is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
					$this->oLang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				}
			} elseif (is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$returnString = null;
		$temp = explode('/',$code);
		
		$string = $this->lang;
		for ($i=0, $loop=count($temp);$i<$loop;$i++) {
			if (isset($string->{$temp[$i]}) == true) {
				$string = $string->{$temp[$i]};
			} else {
				$string = null;
				break;
			}
		}
		
		if ($string != null) {
			$returnString = $string;
		} elseif ($this->oLang != null) {
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) {
						$string = $string->{$temp[$i]};
					} else {
						$string = null;
						break;
					}
				}
			}
			
			if ($string != null) $returnString = $string;
		}
		
		$this->IM->fireEvent('afterGetText',$this->getModule()->getName(),$code,$returnString);
		
		/**
		 * 언어셋 텍스트가 없는경우 iModule 코어에서 불러온다.
		 */
		if ($returnString != null) return $returnString;
		elseif (in_array(reset($temp),array('text','button','action')) == true) return $this->IM->getText($code,$replacement);
		else return $replacement == null ? $code : $replacement;
	}
	
	/**
	 * 상황에 맞게 에러코드를 반환한다.
	 *
	 * @param string $code 에러코드
	 * @param object $value(옵션) 에러와 관련된 데이터
	 * @param boolean $isRawData(옵션) RAW 데이터 반환여부
	 * @return string $message 에러 메세지
	 */
	function getErrorText($code,$value=null,$isRawData=false) {
		$message = $this->getText('error/'.$code,$code);
		if ($message == $code) return $this->IM->getErrorText($code,$value,null,$isRawData);
		
		$description = null;
		switch ($code) {
			default :
				if (is_object($value) == false && $value) $description = $value;
		}
		
		$error = new stdClass();
		$error->message = $message;
		$error->description = $description;
		$error->type = 'BACK';
		
		if ($isRawData === true) return $error;
		else return $this->IM->getErrorText($error);
	}
	
	/**
	 * 템플릿 정보를 가져온다.
	 *
	 * @param string $this->getTemplet($configs) 템플릿명
	 * @return string $package 템플릿 정보
	 */
	function getTemplet($templet=null) {
		$templet = $templet == null ? '#' : $templet;
		
		/**
		 * 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정일 경우
		 */
		if (is_object($templet) == true) {
			$templet_configs = $templet !== null && isset($templet->templet_configs) == true ? $templet->templet_configs : null;
			$templet = $templet !== null && isset($templet->templet) == true ? $templet->templet : '#';
		} else {
			$templet_configs = null;
		}
		
		/**
		 * 템플릿명이 # 이면 모듈 기본설정에 설정된 템플릿을 사용한다.
		 */
		if ($templet == '#') {
			$templet = $this->getModule()->getConfig('templet');
			$templet_configs = $this->getModule()->getConfig('templet_configs');
		}
		
		return $this->getModule()->getTemplet($templet,$templet_configs);
	}
	
	/**
	 * 모듈 외부컨테이너를 가져온다.
	 *
	 * @param string $container 컨테이너명
	 * @return string $html 컨텍스트 HTML
	 */
	function getContainer($container) {
		$html = $this->getContext($container);
		
		$this->IM->addHeadResource('style',$this->getModule()->getDir().'/styles/container.css');
		
		$this->IM->removeTemplet();
		$footer = $this->IM->getFooter();
		$header = $this->IM->getHeader();
		
		return $header.$html.$footer;
	}
	
	/**
	 * 페이지 컨텍스트를 가져온다.
	 *
	 * @param string $qid 문의게시판 ID
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getContext($context,$config=null) {
		/**
		 * 컨텍스트 컨테이너를 설정한다.
		 */
		$html = PHP_EOL.'<!-- PUSH MODULE -->'.PHP_EOL.'<div data-role="context" data-type="module" data-module="'.$this->getModule()->getName().'" data-context="'.$context.'">'.PHP_EOL;
		
		/**
		 * 컨텍스트 헤더
		 */
		$html.= $this->getHeader($context,$configs);
		
		/**
		 * 컨테이너 종류에 따라 컨텍스트를 가져온다.
		 */
		switch ($context) {
			case 'list' :
				$html.= $this->getListContext($config);
				break;
		}
		
		/**
		 * 컨텍스트 푸터
		 */
		$html.= $this->getFooter($context,$configs);
		
		/**
		 * 컨텍스트 컨테이너를 설정한다.
		 */
		$html.= PHP_EOL.'</div>'.PHP_EOL.'<!--// PUSH MODULE -->'.PHP_EOL;
		
		return $html;
	}
	
	/**
	 * 컨텍스트 헤더를 가져온다.
	 *
	 * @param string $qid 문의게시판 ID
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getHeader($context,$configs=null) {
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getHeader(get_defined_vars());
	}
	
	/**
	 * 컨텍스트 푸터를 가져온다.
	 *
	 * @param string $context 컨테이너 종류
	 * @param object $configs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return string $html 컨텍스트 HTML
	 */
	function getFooter($context,$configs=null) {
		/**
		 * 템플릿파일을 호출한다.
		 */
		return $this->getTemplet($configs)->getFooter(get_defined_vars());
	}
	
	/**
	 * 에러메세지를 반환한다.
	 *
	 * @param string $code 에러코드 (에러코드는 iModule 코어에 의해 해석된다.)
	 * @param object $value 에러코드에 따른 에러값
	 * @return $html 에러메세지 HTML
	 */
	function getError($code,$value=null) {
		/**
		 * iModule 코어를 통해 에러메세지를 구성한다.
		 */
		$error = $this->getErrorText($code,$value,true);
		return $this->IM->getError($error);
	}
	
	/**
	 * 전체알림목록 컨텍스트를 가져온다.
	 *
	 * @param object $confgs 사이트맵 관리를 통해 설정된 페이지 컨텍스트 설정
	 * @return $html 컨텍스트 HTML
	 */
	function getListContext($configs=null) {
		/*
		ob_start();
		
		if (preg_match('/\.php$/',$config->templet) == true) {
			$temp = explode('/',$config->templet);
			$templetFile = array_pop($temp);
			$templetPath = implode('/',$temp);
			$templetDir = str_replace(__IM_PATH__,__IM_DIR__,$templetPath);
		} else {
			if (preg_match('/^@/',$config->templet) == true) {
				$templetPath = $this->IM->getTempletPath().'/templets/modules/push/templets/'.preg_replace('/^@/','',$config->templet);
				$templetDir = $this->IM->getTempletDir().'/templets/modules/push/templets/'.preg_replace('/^@/','',$config->templet);
			} else {
				$templetPath = $this->Module->getPath().'/templets/'.$config->templet;
				$templetDir = $this->Module->getDir().'/templets/'.$config->templet;
			}
		
			if (file_exists($templetPath.'/styles/style.css') == true) {
				$this->IM->addSiteHeader('style',$templetDir.'/styles/style.css');
			}
			
			$templetFile = 'templet.php';
		}
		
		$page = Request('p') ? Request('p') : 1;
		$start = ($page - 1) * 20;
		$lists = $this->db()->select($this->table->push)->where('midx',$this->IM->getModule('member')->getLogged());
		$total = $lists->copy()->count();
		$lists = $lists->limit($start,20)->orderBy('reg_date','desc')->get();
		for ($i=0, $loop=count($lists);$i<$loop;$i++) {
			if ($this->IM->Module->isInstalled($lists[$i]->module) == true) {
				$mModule = $this->IM->getModule($lists[$i]->module);
				if (method_exists($mModule,'getPush') == true) {
					$lists[$i]->push = $mModule->getPush($lists[$i]->code,$lists[$i]->fromcode,json_decode($lists[$i]->content));
				} else {
					$lists[$i]->content = $lists[$i]->module.'@'.$lists[$i]->code.'@'.$lists[$i]->content;
				}
			} else {
				$lists[$i]->content = $lists[$i]->module.'@'.$lists[$i]->code.'@'.$lists[$i]->content;
			}
			
			if ($lists[$i]->is_check == 'FALSE') {
				$this->db()->update($this->table->push,array('is_check'=>'TRUE'))->where('midx',$lists[$i]->midx)->where('module',$lists[$i]->module)->where('code',$lists[$i]->code)->where('fromcode',$lists[$i]->fromcode)->execute();
			}
		}
		
		$pagination = GetPagination($page,ceil($total/20),7,'LEFT',$this->IM->getUrl(null,null,false));
		
		$IM = $this->IM;
		$Module = $this;
		$Module->templetPath = $templetPath;
		$Module->templetDir = $templetDir;
		
		if (file_exists($templetPath.'/'.$templetFile) == true) {
			INCLUDE $templetPath.'/'.$templetFile;
		}
		
		$context = ob_get_contents();
		ob_end_clean();
		
		return $context;
		*/
	}
	
	function getPushCount($type='ALL') {
		$check = $this->db()->select($this->table->push)->where('midx',$this->IM->getModule('member')->getLogged());
		if ($type == 'UNCHECK') $check->where('is_check','FALSE');
		elseif ($type == 'UNREAD') $check->where('is_read','FALSE');
		
		return $check->count();
	}
	
	/**
	 * 알림메세지를 전송한다.
	 *
	 * @param int/string $target 알림을 받는사람 (회원고유번호 또는 이메일)
	 * @param string $module 알림메세지가 발생한 대상모듈
	 * @param string $type 알림메세지가 발생한 대상종류
	 * @param string $idx 알림메세지가 발생한 대상고유번호
	 * @param string $code 알림종류
	 * @param any[] $content 메세지 내용
	 * @return boolean $success
	 */
	function sendPush($target,$module,$type,$idx,$code,$content=array()) {
		if (is_numeric($target) == true) { // 알림메세지로 전달
			/**
			 * 동일한 종류의 알림메세지가 있는지 확인한다.
			 */
			$check = $this->db()->select($this->table->push)->where('midx',$target)->where('module',$module)->where('type',$type)->where('idx',$idx)->where('code',$code)->getOne();
			
			/**
			 * 동일한 종류의 알림메세지가 없거나, 알림메세지를 이미 확인한 경우 이전 알림내역을 덮어쓴고,
			 * 그렇지 않은 경우 내용을 병합한다.
			 */
			if ($check == null || $check->is_checked == 'TRUE') {
				$contents = array();
			} else {
				$contents = json_decode($check->contents);
			}
			
			$contents[] = $content;
			$this->db()->replace($this->table->push,array('midx'=>$target,'module'=>$module,'type'=>$type,'idx'=>$idx,'code'=>$code,'contents'=>json_encode($contents,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK),'reg_date'=>time(),'is_checked'=>'FALSE'))->execute();
			
			return true;
		} else { // 이메일로 전달
			/**
			 * @todo 알림메세지 이메일 전송
			 */
		}
	}
	
	function cancelPush($target,$module,$code,$fromcode,$content=array()) {
		if (is_numeric($target) == false) return;
		
		$check = $this->db()->select($this->table->push)->where('midx',$target)->where('module',$module)->where('code',$code)->where('fromcode',$fromcode)->where('is_check','FALSE')->getOne();
		if ($check != null) {
			$prevContents = json_decode($check->content,true);
			$contents = array();
			for ($i=0, $loop=count($prevContents);$i<$loop;$i++) {
				if (count(array_diff($prevContents[$i],$content)) > 0) {
					$contents[] = $prevContents[$i];
				}
			}
			
			if (count($contents) == 0) {
				$this->db()->delete($this->table->push)->where('midx',$target)->where('module',$module)->where('code',$code)->where('fromcode',$fromcode)->execute();
			} else {
				$this->db()->update($this->table->push,array('content'=>json_encode($contents,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)))->where('midx',$target)->where('module',$module)->where('code',$code)->where('fromcode',$fromcode)->execute();
			}
		}
	}
	/*
	function sendServer($channel,$data) {
		$ELEPHANTIO_PATH = $this->Module->getPath().'/classes/elephant.io/src';
		
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Client.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/AbstractPayload.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/EngineInterface.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Engine/AbstractSocketIO.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Engine/SocketIO/Session.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Engine/SocketIO/Version1X.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Exception/MalformedUrlException.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Exception/ServerConnectionFailureException.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Exception/SocketException.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Exception/UnsupportedActionException.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Exception/UnsupportedTransportException.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Payload/Decoder.php';
		REQUIRE_ONCE $ELEPHANTIO_PATH.'/Payload/Encoder.php';
		
		$EIO = new ElephantIO\Client(new ElephantIO\Engine\SocketIO\Version1X('http://127.0.0.1:3000',['timeout'=>5]));
		$EIO->initialize();
		$EIO->emit('push',array($channel,$data));
		$EIO->close();
	}*/
	
	/**
	 * 현재 모듈에서 처리해야하는 요청이 들어왔을 경우 처리하여 결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 * 작업코드가 '@' 로 시작할 경우 사이트관리자를 위한 작업으로 최고관리자 권한이 필요하다.
	 *
	 * @param string $action 작업코드
	 * @return object $results 수행결과
	 * @see /process/index.php
	 */
	function doProcess($action) {
		$results = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeDoProcess',$this->getModule()->getName(),$action,$values);
		
		/**
		 * 모듈의 process 폴더에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/process/'.$action.'.php';
		}
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterDoProcess',$this->getModule()->getName(),$action,$values,$results);
		
		return $results;
	}
	
	/*
	function doProcess($action) {
		$results = new stdClass();
		$values = new stdClass();
		
		if ($action == 'recently') {
			$count = Request('count');
			$lists = $this->db()->select($this->table->push)->where('midx',$this->IM->getModule('member')->getLogged())->orderBy('reg_date','desc')->limit($count)->get();
			
			for ($i=0, $loop=count($lists);$i<$loop;$i++) {
				$module = $this->IM->getModule($lists[$i]->module);
				$content = $lists[$i]->midx.'/'.$lists[$i]->module.'/'.$lists[$i]->code.'/'.$lists[$i]->fromcode.'/'.$lists[$i]->content;
				$lists[$i]->image = null;
				$lists[$i]->link = null;
				if (method_exists($module,'getPush') == true) {
					$push = $module->getPush($lists[$i]->code,$lists[$i]->fromcode,json_decode($lists[$i]->content));
					$lists[$i]->image = $push->image;
					$lists[$i]->link = $push->link;
					$lists[$i]->content = $push->link == null ? $content.'/'.$push->content : $push->content;
				} else {
					$lists[$i]->content = $content;
				}
				$lists[$i]->is_read = $lists[$i]->is_read == 'TRUE';
			}
			
			$results->success = true;
			$results->lists = $lists;
		}
		
		if ($action == 'read') {
			$target = Request('target');
			$code = Request('code');
			$fromcode = Request('fromcode');
			
			$check = $this->db()->select($this->table->push)->where('midx',$this->IM->getModule('member')->getLogged())->where('module',$target)->where('code',$code)->where('fromcode',$fromcode)->getOne();
			if ($check == null) {
				$results->success = false;
			} else {
				$results->success = true;
				if ($check->is_read == 'FALSE') {
					$this->db()->update($this->table->push,array('is_check'=>'TRUE','is_read'=>'TRUE'))->where('midx',$this->IM->getModule('member')->getLogged())->where('module',$target)->where('code',$code)->where('fromcode',$fromcode)->execute();
				}
			}
		}
		
		if ($action == 'readAll') {
			$this->db()->update($this->table->push,array('is_check'=>'TRUE','is_read'=>'TRUE'))->where('midx',$this->IM->getModule('member')->getLogged())->execute();
			$results->success = true;
		}
		
		$this->IM->fireEvent('afterDoProcess','push',$action,$values,$results);
		
		return $results;
	}
	*/
}
?>