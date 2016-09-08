<?php
/**
 * 이 파일은 iModule 알림모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 홈페이지 내 각종 알림기능과 관련된 전반적인 기능을 관리한다.
 * 
 * @file /modules/keyword/ModulePush.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.160903
 */
class ModulePush {
	/**
	 * iModule core 와 Module core 클래스
	 */
	private $IM;
	private $Module;
	
	/**
	 * DB 관련 변수정의
	 *
	 * @private string[] $table DB 테이블 별칭 및 원 테이블명을 정의하기 위한 변수
	 */
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
	 * @param iModule $IM iModule core class
	 * @param Module $Module Module core class
	 * @see /classes/iModule.class.php
	 * @see /classes/Module.class.php
	 */
	function __construct($IM,$Module) {
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
		return $this->IM->db($this->Module->getInstalled()->database);
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
	 * 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $api API명
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 * @todo 외부에서 알림을 보낼 수 있는 API 제공
	 */
	function getApi($api) {
		$data = new stdClass();
		$values = new stdClass();
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->Module->getPath().'/api/'.$api.'.php') == true) {
			INCLUDE $this->Module->getPath().'/api/'.$api.'.php';
		}
		
		return $data;
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getLanguage($code) {
		if ($this->lang == null) {
			if (file_exists($this->Module->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->Module->getPackage()->language) {
					$this->oLang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->Module->getPackage()->language.'.json'));
				}
			} else {
				$this->lang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->Module->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$temp = explode('/',$code);
		if (count($temp) == 1) {
			return isset($this->lang->$code) == true ? $this->lang->$code : ($this->oLang != null && isset($this->oLang->$code) == true ? $this->oLang->$code : $code);
		} else {
			$string = $this->lang;
			for ($i=0, $loop=count($temp);$i<$loop;$i++) {
				if (isset($string->{$temp[$i]}) == true) {
					$string = $string->{$temp[$i]};
				} else {
					$string = null;
					break;
				}
			}
			
			if ($string != null) return $string;
			if ($this->oLang == null) return $code;
			
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) $string = $string->{$temp[$i]};
					return $code;
				}
			}
			return $string;
		}
	}
	
	/**
	 * Get page context
	 *
	 * @param string $container linked context code (signup, account, modify, ... etc)
	 * @return string $context context html code
	 */
	function getContext($container,$config=null) {
		$context = '';
		$values = new stdClass();
		
		switch ($container) {
			case 'list' :
				$context = $this->getListContext($config);
				break;
		}
		
		$this->IM->fireEvent('afterGetContext','push',$container,null,null,$context);
		
		return $context;
	}
	
	/**
	 * Get list context
	 *
	 * @param object $config context's config
	 * @return string $context context's html
	 */
	function getListContext($config) {
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
	}
	
	function getPushCount($type='ALL') {
		$check = $this->db()->select($this->table->push)->where('midx',$this->IM->getModule('member')->getLogged());
		if ($type == 'UNCHECK') $check->where('is_check','FALSE');
		elseif ($type == 'UNREAD') $check->where('is_read','FALSE');
		
		return $check->count();
	}
	
	function sendPush($target,$module,$code,$fromcode,$content=array()) {
		if (is_numeric($target) == true) { // for member
			$check = $this->db()->select($this->table->push)->where('midx',$target)->where('module',$module)->where('code',$code)->where('fromcode',$fromcode)->where('is_check','FALSE')->getOne();
			if ($check == null) {
				$this->db()->insert($this->table->push,array('midx'=>$target,'module'=>$module,'code'=>$code,'fromcode'=>$fromcode,'content'=>json_encode(array($content),JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK),'reg_date'=>time()))->execute();
			} else {
				$contents = json_decode($check->content);
				$contents[] = $content;
				$this->db()->update($this->table->push,array('content'=>json_encode($contents,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK),'reg_date'=>time()))->where('midx',$target)->where('module',$module)->where('code',$code)->where('fromcode',$fromcode)->execute();
			}
		} else { // for email
			
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
	}
	
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
}
?>