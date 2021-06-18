<?php

namespace mk2\ui_form;

use Mk2\Libraries\UI;
use Mk2\Libraries\RequestCollectionStatic;

use mk2\backpack_token\TokenBackpack;

class FormUI extends UI{

	private $methodMode;
	private static $__errorValues=null;


	public function __construct(){
		parent::__construct();

		if(!empty($this->alternativeTokenBackpack)){
			$this->TokenBackpack=new $this->alternativeTokenBackpack();
		}
		else{
			$this->TokenBackpack=new TokenBackpack();
		}

	}

	/**
	 * start
	 * @param $option = null
	 */
	public function start($option=null){

		if(empty($option["method"])){
			$option["method"]="post";
		}

		$str='<form'.$this->_convertOptionString($option).'>';

		$this->methodMode=$option["method"];

		return $str;
	}

	/**
	 * end
	 */
	public function end(){
		$this->methodMode=null;
		echo '</form>';
	}

	/**
	 * setError
	 * @param $errorValue
	 */
	public function setError($errorValues){
		self::$__errorValues=$errorValues;
	}

	/**
	 * verify
	 */
	public function verify(){

		$requestData=$this->Request->data()->get();
		
		if(
			empty($requestData["_tname"]) ||
			empty($requestData["_token"])
		){
			return false;
		}

		$juge=$this->TokenBackpack->verify($requestData["_tname"],$requestData["_token"]);

		if(!$juge){
			return false;
		}

		$this->Request->data()->delete([
			"_tname",
			"_token"
		]);

		return true;
	}

	/**
	 * tagInput
	 * @param $name
	 * @param $option = null
	 */
	public function tagInput($name,$option=null){

		if($name){
			$option["name"]=$this->_convertName($name);
		}

		if(empty($option["type"])){
			$option["type"]="text";
		}

		if(!(
			$option["type"]=="radio" || 
			$option["type"]=="checkbox"
		)){
			if($this->_existRequest()){
				$getValue=$this->_getValue($name);
				if(isset($getValue)){
					$option["value"]=$getValue;
				}	
			}
		}

		$str='<input'.$this->_convertOptionString($option).'>';

		return $str;
	}

	/**
	 * tagHidden
	 * @param $name
	 * @param $value
	 * @param $option = null
	 */
	public function tagHidden($name,$value,$option = null){
		if(!$option){
			$option=[];
		}

		$option["type"]="hidden";
		$option["value"]=$value;

		return $this->tagInput($name,$option);
	}

	/**
	 * tagTextArea
	 * @param $name
	 * @param $option = null
	 */
	public function tagTextArea($name,$option=null){
		
		$option["name"]=$this->_convertName($name);

		$value=null;
		if(!empty($option["value"])){
			$value=$option["value"];
			unset($option["value"]);
		}

		if($this->_existRequest()){
			$getValue=$this->_getValue($name);
			if(isset($getValue)){
				$value=$getValue;
			}
		}

		$str='<textarea'.$this->_convertOptionString($option).'>'.$value.'</textarea>';

		return $str;
	}

	/**
	 * tagSelect
	 * @param $name
	 * @param $select
	 * @param $option = null
	 */
	public function tagSelect($name,$select,$option=null){

		$option["name"]=$this->_convertName($name);

		$value=null;
		if(isset($option["value"])){
			$value=(string)$option["value"];
			unset($option["value"]);
		}

		if($this->_existRequest()){
			$getValue=$this->_getValue($name);
			if(isset($getValue)){
				$value=$getValue;
			}
		}

		$optionTagStr="";

		if(!empty($option["empty"])){
			$optionTagStr.='<option value="">'.$option["empty"].'</option>';
		}

		foreach($select as $key=>$val){
			if(is_array($val)){
				$optionTagStr.= '<optgroup label="'.$key.'">';
				foreach($val as $key2=>$val2){
					$selected="";
					if($value){
						if($value==$key2){
							$selected='selected';
						}	
					}
					else{
						if($value==="0" && (string)$key2==="0"){
							$selected='selected';
						}
						else if($value===null && $key2===null){
							$selected='selected';
						}
					}
					$optionTagStr.='<option value="'.$key2.'" '.$selected.'>'.$val2.'</option>';	
				}
				$optionTagStr.= '</optgroup>';
			}
			else{
				$selected="";
				if($value){
					if($value==$key){
						$selected='selected';
					}	
				}
				else{
					if($value==="0" && (string)$key==="0"){
						$selected='selected';
					}
					else if($value===null && $key===null){
						$selected='selected';
					}
				}
				$optionTagStr.='<option value="'.$key.'" '.$selected.'>'.$val.'</option>';	
			}
		}

		$str='<select'.$this->_convertOptionString($option).'>'.$optionTagStr.'</select>';

		return $str;
	}

	/**
	 * tagRadio
	 * @param $name
	 * @param $radio
	 * @param $option = null
	 */
	public function tagRadio($name,$radio,$option=null){

		$option["name"]=$this->_convertName($name);

		$value=null;
		if(isset($option["value"])){
			$value=$option["value"];
			unset($option["value"]);
		}

		if($this->_existRequest()){
			$getValue=$this->_getValue($name);
			if(isset($getValue)){
				$value=$getValue;
			}
		}
		
		$str="";
		$ind=0;
		foreach($radio as $key=>$val){

			$radioId='radio.'.$name.'.'.$ind;

			$radioOpt=[
				"type"=>"radio",
				"value"=>$key,
				"id"=>$radioId,
			];
			
			if($value){
				if($value==$key){
					$radioOpt['checked']="checked";
				}	
			}
			else{
				if($value===0 && intval($key)===0){
					$radioOpt['checked']="checked";
				}
				else if($value===null && intval($key)===null){
					$radioOpt['checked']="checked";
				}
			}	

			$str.=$this->tagInput($name,$radioOpt);

			$str.='<label for="'.$radioId.'">'.$val.'</label>';

			$ind++;
		}

		return $str;
	}

	/**
	 * tagAgree
	 * @param $name
	 * @param $option = null
	 */
	public function tagAgree($name,$option=null){
		
		$value=null;
		if(isset($option["value"])){
			$value=$option["value"];
			unset($option["value"]);
		}

		if($value){
			$option["checked"]="checked";			
		}

		if($this->_existRequest()){
			$getValue=$this->_getValue($name);
			if(isset($getValue)){
				$option["checked"]="checked";
			}
		}

		$option['type']="checkbox";
		$option['value']=1;

		return $this->tagInput($name,$option);

	}

	/**
	 * tagCheckbox
	 * @param $name
	 * @param $checkbox
	 * @param $option = null
	 */
	public function tagCheckbox($name,$checkbox,$option=null){

		if(!is_array($checkbox)){
			$checkbox=[$checkbox=>""];
		}

		$searchName=$this->_convertName($name);
		$name=$this->_convertName($name)."[]";

		$value=null;
		if(isset($option["value"])){
			$value=$option["value"];
			unset($option["value"]);
		}

		if($this->_existRequest()){
			$getValue=$this->_getValue($searchName);
			if($getValue){
				$value=$getValue;
			}
		}

		$str="";
		$ind=0;
		foreach($checkbox as $key=>$val){

			$checkboxId='checkbox.'.$name.'.'.$ind;

			$checkboxOpt=[
				"type"=>"checkbox",
				"value"=>$key,
				"id"=>$checkboxId,
			];

			if(!is_array($value)){
				$value=[$value];
			}

			foreach($value as $v_){
				if($v_){
					if($v_==$key){
						$checkboxOpt['checked']="checked";
					}
				}
				else{
					if($v_===0 && intval($key)===0){
						$checkboxOpt['checked']="checked";
					}
					else if($v_===null && intval($key)===null){
						$checkboxOpt['checked']="checked";
					}
				}
			}

			$str.=$this->tagInput($name,$checkboxOpt);

			$str.='<label for="'.$checkboxId.'">'.$val.'</label>';

			$ind++;
		}

		return $str;

	}

	/**
	 * tagFile
	 * @param $name
	 * @param $option = null
	 */
	public function tagFile($name,$option=null){

		$option["type"]="file";
		return $this->tagInput($name,$option);
	}
	
	/**
	 * tagButton
	 * @param $value
	 * @param $option = null
	 */
	public function tagButton($value,$option=null){

		$option["type"]="button";
		$option["value"]=$value;

		return $this->tagInput(null,$option);
	}

	/**
	 * tagSubmitBtn
	 * @param $value
	 * @param $option = null
	 */
	public function tagSubmitBtn($value,$option=null){

		$option["type"]="submit";
		$option["value"]=$value;

		return $this->tagInput(null,$option);
	}

	/**
	 * tagResetBtn
	 * @param $value
	 * @param $option = null
	 */
	public function tagResetBtn($value,$option=null){

		$option["type"]="reset";
		$option["value"]=$value;

		return $this->tagInput(null,$option);
	}

	/**
	 * tagError
	 * @param string $name
	 * @param $option = null
	 */
	public function tagError($name,$option=null){

		if(!$option){
			$option=[];
		}

		if(!empty(self::$__errorValues[$name])){

			$verror=self::$__errorValues[$name];

			$str='<div class="error">';
			if(!empty($option["allOutput"])){
				foreach($verror as $ind=>$v_){
					$str.=$v_;
					if($ind){
						$str.="<br>";
					}
				}	
			}
			else{
				$str.=$verror[0];
			}
			$str.="</div>";

			return $str;
		}

	}

	/**
	 * tagToken
	 * @param string $tokenName
	 * @param $option = null
	 */
	public function tagToken($tokenName,$option=null){

		if(!$option){
			$option=[];
		}

		$token=$this->TokenBackpack->set($tokenName);

		$option["type"]="hidden";
		$option["value"]=$tokenName;

		$str=$this->tagInput("_tname",$option);

		$option["value"]=$token;
		$str.=$this->tagInput("_token",$option);

		return $str;
	}

	private function _convertName($name){

		$names=explode(".",$name);

		if(count($names)==1){
			return $name;
		}
		else{
			$newName="";
			foreach($names as $ind=>$n_){
				if($ind>0){
					$newName.='['.$n_.']';
				}
				else{
					$newName.=$n_;
				}
			}

			return $newName;
		}

	}

	private function _convertOptionString($option=null){

		if(!$option){
			return;
		}

		$str="";
		foreach($option as $key=>$val){
			$str.=' '.$key.'="'.$val.'"';
		}

		return $str;
	}

	private function _existRequest(){

		if($this->_getRequestData()){
			return true;
		}
		
		return false;
	}

	private function _getValue($name){
		
		$getData=$this->_getRequestData();

		$names=explode(".",$name);

		$value=null;
		foreach($names as $n_){
			if(isset($getData[$n_])){
				$value=$getData[$n_];
				$getData=$getData[$n_];
			}
			else{
				$value=null;
			}
		}

		return $value;
	}

	private function _getRequestData(){

		$getData=null;
		if($this->methodMode==strtolower(RequestCollectionStatic::METHOD_QUERY)){
			$getData=$this->Request->query()->get();
		}
		else if($this->methodMode==strtolower(RequestCollectionStatic::METHOD_POST)){
			$getData=$this->Request->post()->get();
		}
		else if($this->methodMode==strtolower(RequestCollectionStatic::METHOD_PUT)){
			$getData=$this->Request->put()->get();
		}
		else if($this->methodMode==strtolower(RequestCollectionStatic::METHOD_DELETE)){
			$getData=$this->Request->delete()->get();
		}

		return $getData;
	}
}