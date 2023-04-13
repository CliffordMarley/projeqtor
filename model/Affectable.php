<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/*
 * ============================================================================ User is a resource that can connect to the application.
 */
require_once ('_securityCheck.php');

class Affectable extends SqlElement {
  
  // extends SqlElement, so has $id
  public $_sec_Description;

  public $id;
 // redefine $id to specify its visible place
  public $name;

  public $userName;

  public $capacity=1;

  public $idCalendarDefinition;

  public $idProfile;

  public $isResource;

  public $isUser;

  public $isContact;

  public $isResourceTeam;

  public $isMaterial;

  public $email;

  public $idTeam;

  public $idOrganization;

  public $idle;

  public $dontReceiveTeamMails;

  public $_sec_Asset;

  public $_spe_asset;

  public $_constructForName=true;

  public $_calculateForColumn=array(
      "name"=>"coalesce(fullName,concat(name,' #'))", 
      "userName"=>"coalesce(name,concat(fullName,' *'))");

  private static $_fieldsAttributes=array(
      "name"=>"required", 
      "isContact"=>"readonly", 
      "isUser"=>"readonly", 
      "isResource"=>"readonly", 
      "isResourceTeam"=>"readonly", 
      "isMaterial"=>"readonly", 
      "idle"=>"hidden");

  private static $_databaseTableName='resource';

  private static $_databaseColumnName=array('name'=>'fullName', 'userName'=>'name');

  private static $_databaseCriteria=array();

  private static $_visibilityScope=array();
  // ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY
  private static $_organizationVisibilityScope=array();
  // END ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY
  private static $_criticalResourceArray=null;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="25%">${realName}</th>
    <th field="userName" width="20%">${userName}</th>
    <th field="photo" formatter="thumb32" width="10%">${photo}</th>
    <th field="email" width="25%">${email}</th>  
    <th field="isUser" width="5%" formatter="booleanFormatter">${isUser}</th>
    <th field="isResource" width="5%" formatter="booleanFormatter">${isResource}</th>
    <th field="isContact" width="5%" formatter="booleanFormatter">${isContact}</th>
    ';

  /**
   * ==========================================================================
   * Constructor
   *
   * @param $id the
   *          id of the object in the database (null if not stored yet)
   * @return void
   */
  function __construct($id=NULL, $withoutDependentObjects=false) {
    parent::__construct($id, $withoutDependentObjects);
    $this->setName();
  }

  public function setName() {
    if ($this->id and !$this->name and $this->userName) {
      $this->name=$this->userName;
    }
  }

  /**
   * ==========================================================================
   * Destructor
   *
   * @return void
   */
  function __destruct() {
    parent::__destruct();
  }
  
  // ============================================================================**********
  // GET STATIC DATA FUNCTIONS
  // ============================================================================**********
  
  /**
   * ==========================================================================
   * Return the specific layout
   * 
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }

  /**
   * ========================================================================
   * Return the specific databaseTableName
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix.self::$_databaseTableName;
  }

  /**
   * ========================================================================
   * Return the specific databaseTableName
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }

  /**
   * ========================================================================
   * Return the specific database criteria
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }

  /**
   * ==========================================================================
   * Return the specific fieldsAttributes
   *
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  // ============================================================================**********
  // THUMBS & IMAGES
  // ============================================================================**********
  
  /**
   *
   * @param unknown $classAffectable          
   * @param unknown $idAffectable          
   * @param string $fileFullName          
   */
  public static function generateThumbs($classAffectable, $idAffectable, $fileFullName=null) {
    $sizes=array(16, 22, 32, 48, 80); // sizes to generate, may be used somewhere
    $thumbLocation='../files/thumbs';
    $attLoc=Parameter::getGlobalParameter('paramAttachmentDirectory');
    if (!$fileFullName) {
      $image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>'Resource', 'refId'=>$idAffectable));
      if ($image->id) {
        $fileFullName=$image->subDirectory.$image->fileName;
      }
    }
    $fileFullName=pq_str_replace('${attachmentDirectory}', $attLoc, $fileFullName);
    $fileFullName=pq_str_replace('\\', '/', $fileFullName);
    if ($fileFullName and isThumbable($fileFullName)) {
      foreach ($sizes as $size) {
        $thumbFile=$thumbLocation."/Affectable_$idAffectable/thumb$size.png";
        createThumb($fileFullName, $size, $thumbFile, true);
      }
    }
  }

  public static function generateAllThumbs() {
    $affList=SqlList::getList('Affectable', 'name', null, true);
    foreach ($affList as $id=>$name) {
      self::generateThumbs('Affectable', $id, null);
    }
  }

  public static function deleteThumbs($classAffectable, $idAffectable, $fileFullName=null) {
    $thumbLocation='../files/thumbs/Affectable_'.$idAffectable;
    purgeFiles($thumbLocation, null);
  }

  public static function getThumbUrl($objectClass, $affId, $size, $nullIfEmpty=false, $withoutUrlExtra=false) {
    $thumbLocation='../files/thumbs';
    $file="$thumbLocation/Affectable_$affId/thumb$size.png";
    if (file_exists($file)) {
      if ($withoutUrlExtra) {
        return $file;
      } else {
        $cache=filemtime($file);
        return "$file?nocache=".$cache."#$affId#&nbsp;#Affectable";
      }
    } else {
      if ($nullIfEmpty) {
        return null;
      } else {
        return 'letter#'.$affId;
        // if ($withoutUrlExtra) {
        // return "../view/img/Affectable/thumb$size.png";
        // } else {
        // return "../view/img/Affectable/thumb$size.png#0#&nbsp;#Affectable";
        // }
      }
    }
  }

  public static function showBigImageEmpty($extraStylePosition, $canAdd=true) {
    $result=null;
    if (isNewGui()) {
      $result.='<div style="position: absolute;'.$extraStylePosition.';width:60px;height:60px;border-radius:40px; border: 1px solid grey;color: grey;font-size:80%; text-align:center;cursor: pointer;"';
      if ($canAdd) {
        $result.='onClick="addAttachment(\'file\');" title="'.i18n('addPhoto').'">';
        $result.='<div style="left: 19px;position:relative;top: 20px;height:22px;width: 22px;" class="iconAdd iconSize22 imageColorNewGui">&nbsp;</div>';
      } else {
        $result.='>';
      }
      $result.='</div>';
    } else {
      $result='<div style="position: absolute;'.$extraStylePosition.';'.'border-radius:40px;width:80px;height:80px;border: 1px solid grey;color: grey;font-size:80%;'.'text-align:center;';
      if ($canAdd) {
        $result.='cursor: pointer;"  onClick="addAttachment(\'file\');" title="'.i18n('addPhoto').'">';
        $result.='<br/><br/><br/>'.i18n('addPhoto').'</div>';
      } else {
        $result.='" ></div>';
      }
    }
    return $result;
  }

  public static function showBigImage($extraStylePosition, $affId, $filename, $attachmentId) {
    $result='<div style="position: absolute;'.$extraStylePosition.'; border-radius:40px;width:80px;height:80px;border: 1px solid grey;">'.'<img style="border-radius:40px;" src="'.Affectable::getThumbUrl('Resource', $affId, 80).'" '.' title="'.$filename.'" style="cursor:pointer"'.' onClick="showImage(\'Attachment\',\''.$attachmentId.'\',\''.htmlEncode($filename, 'protectQuotes').'\');" /></div>';
    return $result;
  }

  public static function drawSpecificImage($class, $id, $print, $outMode, $largeWidth) {
    $result="";
    $image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>'Resource', 'refId'=>$id));
    if ($image->id and $image->isThumbable()) {
      if (!$print) {
        // $result.='<tr style="height:20px;">';
        // $result.='<td class="label">'.i18n('colPhoto').'&nbsp;:&nbsp;</td>';
        // $result.='<td>&nbsp;&nbsp;';
        $result.='<span class="label" style="position: absolute;top:28px;right:105px;">';
        $result.=i18n('colPhoto').'&nbsp;:&nbsp;';
        $canUpdate=securityGetAccessRightYesNo('menu'.$class, 'update')=="YES";
        if ($id==getSessionUser()->id) $canUpdate=true;
        if ($canUpdate) {
          // $result.='<img src="css/images/smallButtonRemove.png" class="roundedButtonSmall" style="height:12px" '
          // .'onClick="removeAttachment('.htmlEncode($image->id).');" title="'.i18n('removePhoto').'" class="smallButton"/>';
          $result.='<span onClick="removeAttachment('.htmlEncode($image->id).');" title="'.i18n('removePhoto').'" >';
          $result.=formatSmallButton('Remove');
          $result.='</span>';
        }
        
        $horizontal='right:10px';
        $top='30px';
        $result.='</span>';
      } else {
        if ($outMode=='pdf') {
          $horizontal='left:450px';
          $top='100px';
        } else {
          $horizontal='left:400px';
          $top='70px';
        }
      }
      $extraStyle='top:30px;'.$horizontal;
      $result.=Affectable::showBigImage($extraStyle, $id, $image->fileName, $image->id);
      if (!$print) {
        // $result.='</td></tr>';
      }
    } else {
      if ($image->id) {
        $image->delete();
      }
      if (!$print) {
        $horizontal='right:10px';
        // $result.='<tr style="height:20px;">';
        // $result.='<td class="label">'.i18n('colPhoto').'&nbsp;:&nbsp;</td>';
        // $result.='<td>&nbsp;&nbsp;';
        $result.='<span class="label" style="position: absolute;top:28px;right:105px;">';
        $result.=i18n('colPhoto').'&nbsp;:&nbsp;';
        $canUpdate=securityGetAccessRightYesNo('menu'.$class, 'update')=="YES";
        if ($id==getSessionUser()->id) $canUpdate=true;
        if ($canUpdate and !isNewGui()) {
          // KEVIN
          $result.='<span onClick="addAttachment(\'file\');"title="'.i18n('addPhoto').'" >';
          $result.=formatSmallButton('Add');
          $result.='</span>';
        }
        $result.='</span>';
        $extraStyle='top:30px;'.$horizontal;
        $result.=Affectable::showBigImageEmpty($extraStyle, $canUpdate);
      }
    }
    return $result;
  }

  /**
   * =========================================================================
   * Draw a specific item for the current class.
   * 
   * @param $item the
   *          item. Correct values are :
   *          - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *         must be redefined in the inherited class
   */
  public function drawSpecificItem($item) {
    global $print, $outMode, $largeWidth;
    $result="";
    if ($item=='asset') {
      $asset=new Asset();
      $critArray=array('idAffectable'=>(($this->id)?$this->id:'0'));
      $order=" idAssetType asc ";
      $assetList=$asset->getSqlElementsFromCriteria($critArray, false, null);
      drawAssetFromUser($assetList, $this);
    }
    return $result;
  }

  public static function isAffectable($objectClass=null) {
    if ($objectClass) {
      if ($objectClass=='Resource' or $objectClass=='ResourceTeam' or $objectClass=='User' or $objectClass=='Contact' or $objectClass=='Affectable' or $objectClass=='ResourceSelect' or $objectClass=='Accountable' or $objectClass=='Responsible' or $objectClass=='ResourceMaterial') {
        return true;
      }
    }
    return false;
  }
  
  // ADD BY Marc TABARY - 2017-02-20 ORGANIZATION VISIBILITY
  public static function getOrganizationVisibilityScope($scope='List') {
    if (isset(self::$_organizationVisibilityScope[$scope])) return self::$_organizationVisibilityScope[$scope];
    $orga='all';
    $crit=array('idProfile'=>getSessionUser()->idProfile, 'scope'=>'orgaVisibility'.$scope);
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    if ($habil and $habil->id) {
      $orga=SqlList::getFieldFromId('ListOrgaSubOrga', $habil->rightAccess, 'code', false);
    }
    self::$_organizationVisibilityScope[$scope]=$orga;
    return $orga;
  }
  // END ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY
  public static function getVisibilityScope($scope='List', $idProject=null) {
    $profile=getSessionUser()->getProfile($idProject);
    if (isset(self::$_visibilityScope[$scope][$profile])) return self::$_visibilityScope[$scope][$profile];
    $res='all';
    $crit=array('idProfile'=>$profile, 'scope'=>'resVisibility'.$scope);
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    if ($habil and $habil->id) {
      $res=SqlList::getFieldFromId('ListTeamOrga', $habil->rightAccess, 'code', false);
    }
    self::$_visibilityScope[$scope][$profile]=$res;
    return $res;
  }

  public static function sort($aff1, $aff2) {
    $name1=pq_strtolower(($aff1->name)?$aff1->name:$aff1->userName);
    $name2=pq_strtolower(($aff2->name)?$aff2->name:$aff2->userName);
    if ($name1<$name2) {
      return -1;
    } else if ($name1>$name2) {
      return 1;
    } else {
      return 0;
    }
  }

  public static function tranformPlanningResult($scale, $start, $end) {
    global $cronnedScript, $fullListPlan, $arrayPlannedWork, $arrayRealWork, $arrayAssignment;
    SqlElement::$_cachedQuery['ResourceAll']=array();
    SqlElement::$_cachedQuery['PlanningElement']=array();
    SqlElement::$_cachedQuery['ResourceTeamAffectation']=array();
    SqlElement::$_cachedQuery['Calendar']=array();
    SqlElement::$_cachedQuery['Project']=array();
    $res=array();
    $assignmentDate=array();
    $listCodeProject=array();
    foreach ($arrayAssignment as $ass) {
      // Don't show PRP & TMP
      if (!isset($listCodeProject[$ass->idProject])) {
        $type=SqlList::getFieldFromId('Project', $ass->idProject, 'idProjectType');
        $code=SqlList::getFieldFromId('ProjectType', $type, 'code');
        $listCodeProject[$ass->idProject]=$code;
      }
      $code=$listCodeProject[$ass->idProject];
      if ($code=='PRP' or $code=='TMP') continue;
      // ============== real planned date =======================//
      $date=SqlList::getFieldFromId('Assignment', $ass->id, 'plannedEndDate');
      $monthPeriod=date('Ym', pq_strtotime($date));
      $weekPeriod=getWeekNumberFromDate($date);
      $year=date('Y', pq_strtotime($date));
      $month=date('m', pq_strtotime($date));
      $quarter=1+intval(($month-1)/3);
      $quarterPeriod=$year.'-Q'.$quarter;
      $assignmentDate['real'][$ass->id]['month']['end']=$monthPeriod;
      $assignmentDate['real'][$ass->id]['week']['end']=$weekPeriod;
      $assignmentDate['real'][$ass->id]['quarter']['end']=$quarterPeriod;
      $assignmentDate['real'][$ass->id]['date']['end']=$date;
      $date=SqlList::getFieldFromId('Assignment', $ass->id, 'plannedStartDate');
      $monthPeriod=date('Ym', pq_strtotime($date));
      $weekPeriod=getWeekNumberFromDate($date);
      $year=date('Y', pq_strtotime($date));
      $month=date('m', pq_strtotime($date));
      $quarter=1+intval(($month-1)/3);
      $quarterPeriod=$year.'-Q'.$quarter;
      $assignmentDate['real'][$ass->id]['month']['start']=$monthPeriod;
      $assignmentDate['real'][$ass->id]['week']['start']=$weekPeriod;
      $assignmentDate['real'][$ass->id]['quarter']['start']=$quarterPeriod;
      $assignmentDate['real'][$ass->id]['date']['start']=$date;
      // ============== ideal planned date =======================//
      $date=$ass->plannedEndDate;
      $monthPeriod=date('Ym', pq_strtotime($date));
      $weekPeriod=getWeekNumberFromDate($date);
      $year=date('Y', pq_strtotime($date));
      $month=date('m', pq_strtotime($date));
      $quarter=1+intval(($month-1)/3);
      $quarterPeriod=$year.'-Q'.$quarter;
      $assignmentDate['ideal'][$ass->id]['month']['end']=$monthPeriod;
      $assignmentDate['ideal'][$ass->id]['week']['end']=$weekPeriod;
      $assignmentDate['ideal'][$ass->id]['quarter']['end']=$quarterPeriod;
      $assignmentDate['ideal'][$ass->id]['date']['end']=$date;
      $date=$ass->plannedStartDate;
      $monthPeriod=date('Ym', pq_strtotime($date));
      $weekPeriod=getWeekNumberFromDate($date);
      $year=date('Y', pq_strtotime($date));
      $month=date('m', pq_strtotime($date));
      $quarter=1+intval(($month-1)/3);
      $quarterPeriod=$year.'-Q'.$quarter;
      $assignmentDate['ideal'][$ass->id]['month']['start']=$monthPeriod;
      $assignmentDate['ideal'][$ass->id]['week']['start']=$weekPeriod;
      $assignmentDate['ideal'][$ass->id]['quarter']['start']=$quarterPeriod;
      $assignmentDate['ideal'][$ass->id]['date']['start']=$date;
    }
    $resourceTeamAffection = array();
    $resourceTeamAffRate=array();
    $idResourceTeam=array();
    $planWork=new PlannedWork();
    $critPlannedWork="idProject not in ".Project::getAdminitrativeProjectList(false);
    $critPlannedWork.=" and workDate>='$start' and workDate<='$end'";
    $lstPlanWork=$planWork->getSqlElementsFromCriteria(null, false, $critPlannedWork,null,null,true);
    $loop['ideal']=array("real"=>$arrayRealWork, "planned"=>$arrayPlannedWork);
    $loop['real']=array("real"=>$arrayRealWork, "planned"=>$lstPlanWork); // PBER : Not sure, possibly to remove back to "real"=>array()
    foreach ($loop as $planType=>$arrayPlan) {
      foreach ($arrayPlan as $type=>$array) {
        foreach ($array as $w) {
          if (!isset($listCodeProject[$w->idProject])) {
            $type=SqlList::getFieldFromId('Project', $w->idProject, 'idProjectType');
            $code=SqlList::getFieldFromId('ProjectType', $type, 'code');
            $listCodeProject[$w->idProject]=$code;
          }
          if (!isset($assignmentDate[$planType][$w->idAssignment])) continue;
          if (!isset($listCodeProject[$w->idProject])) {
            $type=SqlList::getFieldFromId('Project', $w->idProject, 'idProjectType');
            $code=SqlList::getFieldFromId('ProjectType', $type, 'code');
            $listCodeProject[$w->idProject]=$code;
          }
          $code=$listCodeProject[$w->idProject];
          if ($code=='PRP' or $code=='TMP') continue;
        	$idR=$w->idResource;
        	$idP=$w->idProject;
        	$idO=$w->refId;
        	$date = $w->workDate;
        	$monthPeriod=pq_substr($date,0,4).pq_substr($date,5,2);
        	$weekPeriod=getWeekNumberFromDate($date);
        	$year=date('Y',pq_strtotime($date));
        	$month=date('m',pq_strtotime($date));
        	$quarter=1+intval(($month-1)/3);
        	$quarterPeriod=$year.'-Q'.$quarter;
        	$refId=$w->refId;
        	$refType = $w->refType;
        	if ($w->workDate<$start or $w->workDate>$end) {continue;}
        	$r=new ResourceAll($idR, true);
        	$capacity = $r->getCapacityPeriod($w->workDate);
        	$isResourceTeam = ($r->isResourceTeam)?true:false;
        	if (!isset($res[$idR])) {
        		$res[$idR]=array('object'=>$r, 'name'=>$r->name,
        				'totalSurbooked'=>0,'totalWork'=>0, 'totalAvailable'=>0,
        				'capacity'=>0, 'totalCapacity'=>0,'isResourceTeam'=>$isResourceTeam, 'resourceTeamMarginSub'=>0,
        				'dates'=>array('month'=>array(),'week'=>array(),'quarter'=>array()),'projects'=>array());
        	}
        	if (!isset($res[$idR]['projects'][$idP])) {
        		$wbs = SqlList::getFieldFromId('Project', $idP, 'sortOrder');
        		$strategicValue=SqlList::getFieldFromId('Project', $idP, 'strategicValue');
        		//$projectPlan = new PlanningElement();
        		$projectPlan = SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refId'=>$idP,'refType'=>'Project'));
        		$validatedEndDate=$projectPlan->validatedEndDate;
        		$plannedEndDate=$projectPlan->plannedEndDate;
        		$priority=$projectPlan->priority;
        		$res[$idR]['projects'][$idP]=array('name'=>SqlList::getNameFromId('Project',$idP),'wbs'=>$wbs, 'priority'=>$priority,'strategicValue'=>$strategicValue,
        		    'validatedEndDate'=>$validatedEndDate,'plannedEndDate'=>$plannedEndDate ,'totalSurbooked'=>0,'totalWork'=>0, 'object'=>array());
        	}
            if($planType == 'real' and $w->work != 0 and !$isResourceTeam){
        	  if(!isset($resourceTeamAffRate[$idR])){
        	    $resTeamAffectation = new ResourceTeamAffectation();
        	    $resTeamAff = $resTeamAffectation->getSingleSqlElementFromCriteria('ResourceTeamAffectation',array('idResource'=>$idR));
        	    $affCapacity = $capacity;
        	    if($resTeamAff->id){
        	      $idResourceTeam[$idR]=$resTeamAff->idResourceTeam;
        	      $affCapacity = ($resTeamAff->rate/100)*$capacity;
        	      $resourceTeamAffRate[$idR]=$resTeamAff;
        	    }
        	  }else{
        	    $affCapacity = ($resourceTeamAffRate[$idR]->rate/100)*$capacity;
        	  }
        	  $workCapacity = ($affCapacity < $capacity)?$w->work-$affCapacity:$w->work;
        	  $workCapacity = ($workCapacity < 0)?0:$workCapacity;
        	  if(!isset($resourceTeamAffection[$resTeamAff->idResourceTeam])){
        	    $resourceTeamAffection[$resTeamAff->idResourceTeam]=$workCapacity;
        	  }else{
        	    $resourceTeamAffection[$resTeamAff->idResourceTeam]+=$workCapacity;
        	  }
        	}
            if($isResourceTeam){
        	  if(isset($resourceTeamAffection[$idR])){
        	    $res[$idR]['resourceTeamMarginSub']=$resourceTeamAffection[$idR];
        	  }
        	}else {
        	  if(isset($idResourceTeam[$idR]) and isset($res[$idResourceTeam[$idR]]) and isset($resourceTeamAffection[$idResourceTeam[$idR]])) {
        	    $res[$idResourceTeam[$idR]]['resourceTeamMarginSub']=$resourceTeamAffection[$idResourceTeam[$idR]];
        	  }
        	}
        	if($planType == 'real')$res[$idR]['totalWork']+=$w->work;
        	if($planType == 'real')$res[$idR]['projects'][$idP]['totalWork']+=$w->work;
        	if (!isset($res[$idR]['projects'][$idP]['object'][$idO])) {
        		$res[$idR]['projects'][$idP]['object'][$idO]=array('name'=>SqlList::getNameFromId($refType,$refId), 'refType'=>$refType, 'refId'=>$refId, 'plan'=>array('ideal'=>array('endDate'=>null, 'startDate'=>null), 'real'=>array('endDate'=>null, 'startDate'=>null)),
        		     'totalSurbooked'=>0,'totalWork'=>0, 'dates'=>array('month'=>array(),'week'=>array(),'quarter'=>array()));
        	}
        	$res[$idR]['projects'][$idP]['object'][$idO]['refType']=$refType;
        	$res[$idR]['projects'][$idP]['object'][$idO]['refId']=$refId;
        	$res[$idR]['projects'][$idP]['object'][$idO]['plan'][$planType]['endDate']=$assignmentDate[$planType][$w->idAssignment]['date']['end'];
        	$res[$idR]['projects'][$idP]['object'][$idO]['plan'][$planType]['startDate']=$assignmentDate[$planType][$w->idAssignment]['date']['start'];
        	if($planType == 'real')$res[$idR]['projects'][$idP]['object'][$idO]['totalWork']+=$w->work;
        	if(!isset($res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod]['ideal'])){
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod]['ideal']=array('endDate'=>null, 'startDate'=>null, 'totalSurbooked'=>0,'totalWork'=>0);
        	}
        	if(!isset($res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod]['real'])){
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod]['real']=array('endDate'=>null, 'startDate'=>null, 'totalSurbooked'=>0,'totalWork'=>0);
        	}
        	if($planType == 'real')$res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod][$planType]['totalWork']+=$w->work;
        	if ($type=='planned') {
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod][$planType]['totalSurbooked']+=$w->surbookedWork;
        	}
        	$res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod][$planType]['endDate']=$assignmentDate[$planType][$w->idAssignment]['month']['end'];
        	$res[$idR]['projects'][$idP]['object'][$idO]['dates']['month'][$monthPeriod][$planType]['startDate']=$assignmentDate[$planType][$w->idAssignment]['month']['start'];
        	if(!isset($res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod]['ideal'])){
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod]['ideal']=array('endDate'=>null, 'startDate'=>null, 'totalSurbooked'=>0,'totalWork'=>0);
        	}
        	if(!isset($res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod]['real'])){
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod]['real']=array('endDate'=>null, 'startDate'=>null, 'totalSurbooked'=>0,'totalWork'=>0);
        	}
        	if($planType == 'real')$res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod][$planType]['totalWork']+=$w->work;
        	if ($type=='planned') {
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod][$planType]['totalSurbooked']+=$w->surbookedWork;
        	}
        	$res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod][$planType]['endDate']=$assignmentDate[$planType][$w->idAssignment]['week']['end'];
        	$res[$idR]['projects'][$idP]['object'][$idO]['dates']['week'][$weekPeriod][$planType]['startDate']=$assignmentDate[$planType][$w->idAssignment]['week']['start'];
        	if(!isset($res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod]['ideal'])){
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod]['ideal']=array('endDate'=>null, 'startDate'=>null, 'totalSurbooked'=>0,'totalWork'=>0);
        	}
        	if(!isset($res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod]['real'])){
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod]['real']=array('endDate'=>null, 'startDate'=>null, 'totalSurbooked'=>0,'totalWork'=>0);
        	}
        	if($planType == 'real')$res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod][$planType]['totalWork']+=$w->work;
        	if ($type=='planned') {
        		$res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod][$planType]['totalSurbooked']+=$w->surbookedWork;
        	}
        	$res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod][$planType]['endDate']=$assignmentDate[$planType][$w->idAssignment]['quarter']['end'];
        	$res[$idR]['projects'][$idP]['object'][$idO]['dates']['quarter'][$quarterPeriod][$planType]['startDate']=$assignmentDate[$planType][$w->idAssignment]['quarter']['start'];
        	if ($type=='planned' and $planType=='ideal') {
         		$res[$idR]['totalSurbooked']+=$w->surbookedWork;
         		$res[$idR]['projects'][$idP]['totalSurbooked']+=$w->surbookedWork;
         		$res[$idR]['projects'][$idP]['object'][$idO]['totalSurbooked']+=$w->surbookedWork;
        	}
        }
      }
    }
    $nbDays=0;
    for ($date=$start; $date<=$end; $date=addDaysToDate($date, 1)) {
      $nbDays++;
      foreach ($res as $idR=>$r) {
        $capa=$r['object']->getCapacityPeriod($date);
        if (!isOffDay($date, $r['object']->idCalendarDefinition)) {
          $res[$idR]['totalAvailable']+=$capa;
        }
        $res[$idR]['totalCapacity']+=$capa;
        $res[$idR]['capacity']=round($res[$idR]['totalCapacity']/$nbDays, 2);
      }
    }
    uasort($res, function ($rA, $rB) {
      $indA=($rA['capacity'])?$rA['totalSurbooked']/$rA['capacity']:0;
      $indB=($rB['capacity'])?$rB['totalSurbooked']/$rB['capacity']:0;
      if ($indA==$indB) {
        return 0;
      } else if ($indA<$indB) {
        return 1;
      } else {
        return -1;
      }
    });
    self::$_criticalResourceArray=$res;
  }

  public static function drawCriticalResourceList($scale, $start, $end, $idProject=null, $limitedRow=null) {
    global $arrayProject;
    if (!isset(self::$_criticalResourceArray)) self::tranformPlanningResult($scale, $start, $end);
    echo '<table>';
    echo '<thead>';
    echo '<tr style="display:block;">';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:40px">'.i18n("colSortOrderShort").'</div></td>';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:156px">'.i18n("colIdResource").'</div></td>';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:75px" title="'.i18n('helpCriticalResourceCapacity').'">'.i18n("colCapacityCriticalResource").'</div></td>';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:84px" title="'.i18n('helpCriticalResourceAvailable').'">'.lcfirst(i18n("titleAvailable")).'</div></td>';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:60px" title="'.i18n('helpCriticalResourceUsed').'">'.lcfirst(i18n("used")).'</div></td>';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:75px" title="'.i18n('helpCriticalResourceOverbooked').'">'.lcfirst(i18n("overbooked")).'</div></td>';
    echo '<td class="reportTableHeader"><div class="dataContent" style="width:60px" title="'.i18n('helpCriticalResourceIndice').'">'.i18n("indicatorValue").'</div></td>';
    echo '<td class="reportTableHeader" colspan="2"><div class="dataContent" style="width:125px" title="'.i18n('helpCriticalResourceMargin').'">'.i18n("colMarginWork").'</div></td>';
    echo '<tr>';
    echo '</thead>';
    echo '<tbody style="display:block;overflow-y:scroll;height:160px;width:100%;">';
    $isColorBlind = (Parameter::getUserParameter('colorBlindPlanning') == 'YES')?true:false;
    $redColorA = 'linear-gradient(45deg, #63226b 6.25%, #9a3ec9 6.25%, #9a3ec9 43.75%, #63226b 43.75%, #63226b 56.25%, #9a3ec9 56.25%, #9a3ec9 93.75%, #63226b 93.75%);background-size: 8px 8px;';
    $cpt=0;
    $totalArray=count(self::$_criticalResourceArray);
    if (pq_trim($idProject[0])=='') unset($idProject[0]);
    if (! is_array($arrayProject)) $arrayProject=array();
    $sortArray = array();
    foreach (self::$_criticalResourceArray as $res) {
      if ($idProject) {
        $inArray=false;
        foreach ($idProject as $idProj) {
            if (!pq_trim($idProj)) continue;
            if (isset($arrayProject[$idProj])) continue;
            $proj=new Project($idProj, true);
            $arrayProject[$idProj]=$proj->name;
            $subList=$proj->getSubProjectsList(true);
            if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
        }
        foreach ($arrayProject as $idProj=>$val) {
          if (isset($res['projects'][$idProj])) {
            $inArray=true;
            break;
          } else {
            continue;
          }
        }
        if (!$inArray) continue;
      }
      $cpt++;
      if ($limitedRow and $cpt>$limitedRow) continue;
      $total=$res['totalWork'];
      $surbooked=$res['totalSurbooked'];
      $available=$res['totalAvailable'];
      $margin=$available-$total;
      if($res['isResourceTeam']){
        $margin -= $res['resourceTeamMarginSub'];
      }
      $marginPct=($available!=0)?round($margin/$available*100, 0):0;
      $indice = ($available!=0)?round((($margin-$surbooked)/$available)*100)*-1:0;
      $indiceRed = (getSessionValue('CriticalResourceIndicatorRed'))?getSessionValue('CriticalResourceIndicatorRed'):Parameter::getGlobalParameter('CriticalResourceIndicatorRed');
      $indiceOrange = (getSessionValue('CriticalResourceIndicatorOrange'))?getSessionValue('CriticalResourceIndicatorOrange'):Parameter::getGlobalParameter('CriticalResourceIndicatorOrange');
      $indiceColor = "";
      if($indiceRed > $indiceOrange){
        if($indice >= $indiceOrange and $indice < $indiceRed)$indiceColor = "background:".(($isColorBlind)?'#bfbfbf':'#FFC000').";color:white;";
        if($indice >= $indiceRed and $indice > $indiceOrange)$indiceColor = "background:".(($isColorBlind)?$redColorA:'#BB5050;')."color:white;";
      }else {
        if($indice >= $indiceOrange and $indice > $indiceRed)$indiceColor = "background:".(($isColorBlind)?'#bfbfbf':'#FFC000').";color:white;";
        if($indice >= $indiceRed and $indice < $indiceOrange)$indiceColor = "background:".(($isColorBlind)?$redColorA:'#BB5050;')."color:white;";
      }
      $result='';
      $result .= '<tr style="height:20px;">';
      $result .= '<td class="reportTableData"><div class="dataContent" style="width:48px">'.$cpt.'</div></td>';
      $result .= '<td class="reportTableData dataParentContent" style="text-align:left;"><div class="dataContent" style="width:164px"><div class="dataExtend" style="min-width:160px">'.$res['name'].'</div></div></td>';
      $result .= '<td class="reportTableData"><div class="dataContent" style="width:83px">'.htmlDisplayNumericWithoutTrailingZeros($res['capacity']).'</div></td>';
      $result .= '<td class="reportTableData"><div class="dataContent" style="width:92px">'.Work::displayWorkWithUnit($available).'</div></td>';
      $result .= '<td class="reportTableData"><div class="dataContent" style="width:68px">'.Work::displayWorkWithUnit($total).'</div></td>';
      $result .= '<td class="reportTableData" style="'.(($surbooked>0)?'color:red;':'').'"><div class="dataContent" style="width:83px">'.Work::displayWorkWithUnit($surbooked).'</div></td>';
      $result .= '<td class="reportTableData"><div class="dataContent" style="width:68px;'.$indiceColor.'">'.numericFormatter(round($indice, 0)).'</div></td>';
      $result .= '<td class="reportTableData" style="color:'.(($margin>=0)?'green':'red').';"><div class="dataContent" style="width:64px">'.Work::displayWorkWithUnit($margin).'</div></td>';
      $result .= '<td class="reportTableData" style="color:'.(($margin>=0)?'green':'red').';"><div class="dataContent" style="width:58px">'.percentFormatter($marginPct).'</div></td>';
      $result .= '</tr>';
      $sortArray[$indice]=$result;
    }
    krsort($sortArray);
    foreach ($sortArray as $line){
      echo $line;
    }
    echo '</tbody>';
    echo '</table>';
  }

  public static function drawCriticalResourceProjectList($scale, $start, $end, $idProject=null, $limitedRow=null) {
    global $arrayProject;
    if (!isset(self::$_criticalResourceArray)) self::tranformPlanningResult($scale, $start, $end);
    echo '<table>';
    echo '  <thead>';
    echo '    <tr style="display:block;">';
    echo '      <td class="reportTableHeader"><div style="width:110px">'.i18n("colIdResource").'</div></td>';
    echo '      <td class="reportTableHeader" title="'.i18n('helpCriticalResourceCapacity').'"><div style="width:105px">'.i18n("colCapacityCriticalResource").'</div></td>';
    echo '      <td class="reportTableHeader" title="'.i18n('helpCriticalResourceAvailable').'"><div style="width:90px">'.lcfirst(i18n("titleAvailable")).'</div></td>';
    echo '      <td class="reportTableHeader"><div style="width:231px">'.i18n("colIdProject").'</div></td>';
    echo '      <td class="reportTableHeader" title="'.i18n('helpCriticalResourceUsed').'"><div style="width:80px">'.lcfirst(i18n("used")).'</div></td>';
    echo '      <td class="reportTableHeader"title="'.i18n('helpCriticalResourceOverbooked').'"><div style="width:80px">'.lcfirst(i18n("overbooked")).'</div></td>';
    echo '    </tr>';
    echo '  </thead>';
    echo '  <tbody style="display:block; overflow-y:scroll; height:160px; width:100%;">';
    $cpt=0;
    $totalArray=count(self::$_criticalResourceArray);
    if (pq_trim($idProject[0])=='') unset($idProject[0]);
    if (! is_array($arrayProject)) $arrayProject=array();
    foreach (self::$_criticalResourceArray as $res) {
      if ($idProject) {
        $inArray=false;
        foreach ($idProject as $idProj) {
          if (!trim($idProj)) continue;
          if (isset($arrayProject[$idProj])) continue;
          $proj=new Project($idProj, true);
          $arrayProject[$idProj]=$proj->name;
          $subList=$proj->getSubProjectsList(true);
          if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
        }
        foreach ($arrayProject as $idProj=>$val) {
          if (array_key_exists($idProj, $res['projects'])) {
            $inArray=true;
          } else {
            continue;
          }
        }
        if (!$inArray) continue;
      }
      $firstRow=true;
      $cpt++;
      if ($limitedRow and $cpt>$limitedRow) continue;
      uasort($res['projects'], function ($x, $y) {
      	return $x['priority'] <=> $y['priority'];
      });
      foreach ($res['projects'] as $id=>$project) {
        $total=$project['totalWork'];
        $surbooked=$project['totalSurbooked'];
        $available=$res['totalAvailable'];
        $hiddenClass='';
        if (count($res['projects'])>1) {
          $hiddenClass=($firstRow)?'resourceSkillFirstRow':'resourceSkillHiddenRow';
        }
        if (!$firstRow and $cpt==$limitedRow) {
          $hiddenClass='resourceSkillLastRow';
        }
        if ($idProject) {
          $arrayProject=array();
          foreach ($idProject as $idProj) {
            if (!trim($idProj)) continue;
            $proj=new Project($idProj, true);
            $arrayProject[$idProj]=$proj->name;
            $subList=$proj->getSubProjectsList(true);
            if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
          }
          if (!array_key_exists($id, $arrayProject)) continue;
        }
        echo '<tr style="height: 20px;position:relative">';
        if ($firstRow) {
          echo '<td class="reportTableData '.$hiddenClass.'" style="text-align:left;"><div class="dataContent" style="width:118px"><div class="dataExtend" style="min-width:114px">'.$res['name'].'</div></div></td>';
          echo '<td class="reportTableData '.$hiddenClass.'"><div style="width:113px;">'.htmlDisplayNumericWithoutTrailingZeros($res['capacity']).'</div></td>';
          echo '<td class="reportTableData '.$hiddenClass.'"><div style="width:98px;">'.Work::displayWorkWithUnit($available).'</div></td>';
        } else {
          echo '<td class="reportTableData '.$hiddenClass.'"><div style="width:118px;"></div></td>';
          echo '<td class="reportTableData '.$hiddenClass.'"><div style="width:113px;"></div></td>';
          echo '<td class="reportTableData '.$hiddenClass.'"><div style="width:98px;"></div></td>';
        }
        echo '<td class="reportTableData" style="text-align:left; width:225px; position:relative"><div class="dataContent" style="width:239px"><div class="dataExtend" style="min-width:235px">#'.$id.' '.$project['name'].'</div></div></td>';
        echo '<td class="reportTableData"><div style="width:88px;">'.Work::displayWorkWithUnit($total).'</div></td>';
        echo '<td class="reportTableData"><div style="width:77px;">'.Work::displayWorkWithUnit($surbooked).'</div></td>';
        echo '</tr>';
        $firstRow=false;
      }
    }
    echo '</tbody>';
    echo '</table>';
  }

  public static function drawCriticalResourceActivityList($scale, $start, $end, $idProject=null, $limitedRow=null) {
    global $arrayProject;
    if (!isset(self::$_criticalResourceArray)) self::tranformPlanningResult($scale, $start, $end);
    $dates=array();
    for ($date=$start; $date<=$end; $date=addDaysToDate($date, 1)) {
      if ($scale=='month') $period=date('Ym', strtotime($date)); // pq_substr($date,0,4).pq_substr($date,5,2)
      if ($scale=='week') $period=getWeekNumberFromDate($date);
      else if ($scale=='quarter') {
        $year=date('Y', pq_strtotime($date));
        $month=date('m', pq_strtotime($date));
        $quarter=1+intval(($month-1)/3);
        $period=$year.'-Q'.$quarter;
      }
      $dates[$period]=$period;
    }
    $isColorBlind = (Parameter::getUserParameter('colorBlindPlanning') == 'YES')?true:false;
    $redColorA = 'linear-gradient(45deg, #63226b 6.25%, #9a3ec9 6.25%, #9a3ec9 43.75%, #63226b 43.75%, #63226b 56.25%, #9a3ec9 56.25%, #9a3ec9 93.75%, #63226b 93.75%);background-size: 8px 8px;';
    $redColorB = 'linear-gradient(45deg, #9a3ec9 6.25%, #cb9ce3 6.25%, #cb9ce3 43.75%, #9a3ec9 43.75%, #9a3ec9 56.25%, #cb9ce3 56.25%, #cb9ce3 93.75%, #9a3ec9 93.75%);background-size: 8px 8px;';
    echo '<table>';
    echo '<thead style="display:block;">';
    echo '<tr>';
    echo '<td style="min-width:112px"></td>';
    echo '<td style="min-width:74px"></td>';
    echo '<td style="min-width:98px"></td>';
    echo '<td style="min-width:148px"></td>';
    echo '<td style="min-width:148px"></td>';
    echo '<td style="min-width:76px"></td>';
    echo '<td style="min-width:76px"></td>';
    echo '<td class="reportTableHeader" style="min-width:51px" colspan="'.count($dates).'">'.lcfirst(i18n("colPeriod")).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="reportTableHeader" style="min-width:112px" >'.i18n("colIdResource").'</td>';
    echo '<td class="reportTableHeader" style="min-width:74px" title="'.i18n('helpCriticalResourceCapacity').'">'.i18n("colCapacityCriticalResource").'</td>';
    echo '<td class="reportTableHeader" style="min-width:98px" title="'.i18n('helpCriticalResourceAvailable').'">'.lcfirst(i18n("titleAvailable")).'</td>';
    echo '<td class="reportTableHeader" style="min-width:148px">'.i18n("colIdProject").'</td>';
    echo '<td class="reportTableHeader" style="min-width:148px">'.i18n("colNotifiableItem").'</td>';
    echo '<td class="reportTableHeader" style="min-width:76px" title="'.i18n('helpCriticalResourceUsed').'">'.lcfirst(i18n("used")).'</td>';
    echo '<td class="reportTableHeader" style="min-width:76px" title="'.i18n('helpCriticalResourceOverbooked').'">'.lcfirst(i18n("overbooked")).'</td>';
    if (count($dates)==0) {
      echo '<td class="reportTableHeader" style="min-width:43px"><div>&nbsp;</div></td>';
    }
    foreach ($dates as $period) {
      $date=pq_substr($period, 4);
      if ($scale=='month') {
        $date=date('M', strtotime($period.'01'));
      }
      if ($scale=='quarter') {
        $date=pq_str_replace('-', '', $date);
      }
      echo '<td class="reportTableHeader" style="width:51px;min-width:51px;padding:unset !important"><div>'.$date.'</div><div>'.pq_substr($period, 0, 4).'</div></td>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody style="display:block; overflow-y:scroll; height:235px; width:100%;">';
    
    $cpt=0;
    if (! is_array($arrayProject)) $arrayProject=array();
    if (pq_trim($idProject[0])=='') unset($idProject[0]);
    foreach (self::$_criticalResourceArray as $res) {
      if ($idProject) {
        $inArray=false;
        foreach ($idProject as $idProj) {
          if (!trim($idProj)) continue;
          if (isset($arrayProject[$idProj])) continue;
          $proj=new Project($idProj, true);
          $arrayProject[$idProj]=$proj->name;
          $subList=$proj->getSubProjectsList(true);
          if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
        }
        foreach ($arrayProject as $idProj=>$val) {
          if (array_key_exists($idProj, $res['projects'])) {
            $inArray=true;
          } else {
            continue;
          }
        }
        if (!$inArray) continue;
      }
      $firstRow=true;
      $cpt++;
      if ($limitedRow and $cpt>$limitedRow) continue;
      uasort($res['projects'], function ($x, $y) {
          return $x['priority'] <=> $y['priority'];
      });
      $count=0;
      $cptProject=array_map("count", $res['projects']);
      $cptProject=array_sum($cptProject);
      foreach ($res['projects'] as $id=>$project) {
        foreach ($project['object'] as $idO=>$object) {
          $count++;
          $total=$object['totalWork'];
          $surbooked=$object['totalSurbooked'];
          $available=$res['totalAvailable'];
          $hiddenClass='';
          if (count($res['projects'])>1) {
            $hiddenClass=($firstRow)?'resourceSkillFirstRow':'resourceSkillHiddenRow';
          }
          if ($idProject) {
            $arrayProject=array();
            foreach ($idProject as $idProj) {
              if (!trim($idProj)) continue;
              $proj=new Project($idProj, true);
              $arrayProject[$idProj]=$proj->name;
              $subList=$proj->getSubProjectsList(true);
              if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
            }
            if (!array_key_exists($id, $arrayProject)) continue;
          }
          if (!$firstRow and $count==$cptProject) {
            $hiddenClass='resourceSkillLastRow';
          }
          echo '<tr style="height: 20px;">';
          if ($firstRow) {
            echo '<td class="reportTableData '.$hiddenClass.'" style="text-align:left;position:relative"><div class="dataContent" style="width:118px"><div class="dataExtend" style="min-width:114px">'.$res['name'].'</div></div></td>';
            echo '<td class="reportTableData '.$hiddenClass.'" style="min-width:82px">'.htmlDisplayNumericWithoutTrailingZeros($res['capacity']).'</td>';
            echo '<td class="reportTableData '.$hiddenClass.'" style="min-width:106px">'.Work::displayWorkWithUnit($available).'</td>';
          } else {
            echo '<td class="reportTableData '.$hiddenClass.'" style="min-width:118px"></td>';
            echo '<td class="reportTableData '.$hiddenClass.'" style="min-width:82px"></td>';
            echo '<td class="reportTableData '.$hiddenClass.'" style="min-width:106px"></td>';
          }
          echo '<td class="reportTableData" style="white-space:nowrap; position:relative; text-align:left"><div class="dataContent" style="width:156px"><div class="dataExtend" style="min-width:152px">#'.$id.' '.$project['name'].'</div></div></td>';
          echo '<td class="reportTableData" style="white-space:nowrap;position:relative; text-align:left"><div class="dataContent" style="width:156px"><div class="dataExtend" style="min-width:152px">#'.$object['refId'].' '.$object['name'].'</div></div></td>';
          echo '<td class="reportTableData" style="min-width:84px">'.Work::displayWorkWithUnit($total).'</td>';
          echo '<td class="reportTableData" style="min-width:84px">'.Work::displayWorkWithUnit($surbooked).'</td>';
          $previousObj=null;
          $realPlanStartDate=$object['plan']['real']['startDate'];
          $realPlanEndDate=$object['plan']['real']['endDate'];
          $idealPlanStartDate=$object['plan']['ideal']['startDate'];
          $idealPlanEndDate=$object['plan']['ideal']['endDate'];
          if ($scale=='month') {
            $realEndPeriod=date('Ym', pq_strtotime($realPlanEndDate));
            $idealEndPeriod=date('Ym', pq_strtotime($idealPlanEndDate));
            $realStartPeriod=date('Ym', pq_strtotime($realPlanStartDate));
            $idealStartPeriod=date('Ym', pq_strtotime($idealPlanStartDate));
          } else if ($scale=='week') {
            $realEndPeriod=getWeekNumberFromDate($realPlanEndDate);
            $idealEndPeriod=getWeekNumberFromDate($idealPlanEndDate);
            $realStartPeriod=getWeekNumberFromDate($realPlanStartDate);
            $idealStartPeriod=getWeekNumberFromDate($idealPlanStartDate);
          } else if ($scale=='quarter') {
            $date=$realPlanEndDate; // $realPlanEndDate
            $year=date('Y', pq_strtotime($date));
            $month=date('m', pq_strtotime($date));
            $quarter=1+intval(($month-1)/3);
            $realEndPeriod=$year.'-Q'.$quarter;
            $date=$idealPlanEndDate; // $idealPlanEndDate
            $year=date('Y', pq_strtotime($date));
            $month=date('m', pq_strtotime($date));
            $quarter=1+intval(($month-1)/3);
            $idealEndPeriod=$year.'-Q'.$quarter;
            $date=$realPlanStartDate; // $realPlanStartDate
            $year=date('Y', pq_strtotime($date));
            $month=date('m', pq_strtotime($date));
            $quarter=1+intval(($month-1)/3);
            $realStartPeriod=$year.'-Q'.$quarter;
            $date=$idealPlanStartDate; // $idealPlanStartDate
            $year=date('Y', pq_strtotime($date));
            $month=date('m', pq_strtotime($date));
            $quarter=1+intval(($month-1)/3);
            $idealStartPeriod=$year.'-Q'.$quarter;
          }
          foreach ($dates as $period) {
            $workPeriod='';
            echo '<td class="reportTableData" style="height:20px;padding:unset !important;"><table style="height:100%;width:100%">';
            if (isset($object['dates'][$scale][$period])) {
              $ideal=$object['dates'][$scale][$period]['ideal'];
              $real=$object['dates'][$scale][$period]['real'];
              foreach ($object['plan'] as $type=>$plan) {
                echo '<tr style="height:50%;">';
                // object plan date
                if ($type=='ideal') {
                  $bgColor='';
                  if ($period>=$idealStartPeriod and $period<=$idealEndPeriod) {
                    if ($ideal['totalSurbooked']==0) {
                      $bgColor='background:'.($isColorBlind)?'#67ff00':'#50BB50';
                    } else { // if ($ideal['totalSurbooked']!=0) {
                      $bgColor='background:'.($isColorBlind)?'#bfbfbf':'#FFC000';
                      $workPeriod=Work::displayWorkWithUnit(round($ideal['totalSurbooked'], 1));
                    }
                  }
                  echo '<td style="color:black;text-shadow: 1px 1px 2px white;'.$bgColor.';min-width:51px;position:relative;"><div style="position:absolute;top:1px;width:100%;height:100%;">'.$workPeriod.'</div></td>';
                } else if ($type=='real') {
                  $workPeriod='';
                  $bgColor='';
                  // may display blanks when no planned work XX_X__XXX
//                   if ($real['totalWork']!=0 and $period>=$idealStartPeriod and $period<=$idealEndPeriod and $realPlanStartDate<=$idealPlanEndDate) {
//                     $bgColor='background-color:#50BB50';
//                   } else if ($real['totalWork']!=0 and $realEndPeriod>$idealEndPeriod) {  // may display blanks when no planned work XX_X__XXX
//                     $bgColor='background-color:#BB5050';
//                   }
                  // Will display only one bar, without blanks XXXXXXXXX
                  if ($period>=$realStartPeriod and $period<=$realEndPeriod) {
                    if ($period<=$idealEndPeriod) {
                      $bgColor='background:'.(($real['totalWork']!=0)?(($isColorBlind)?'#67ff00':'#50BB50'):(($isColorBlind)?'#d1ffb3':'#AEC5AE'));
                      // } else if ($realStartPeriod<=$period and $realEndPeriod>$idealEndPeriod) { // Will display only one bar, without blanks XXXXXXXXX
                    } else { //if ($real['totalWork']!=0 and $realEndPeriod>$idealEndPeriod) {  // may display blanks when no planned work XX_X__XXX
                      $bgColor='background:'.(($real['totalWork']!=0)?(($isColorBlind)?$redColorA:'#BB5050'):(($isColorBlind)?$redColorB:'#BB9099;'));
                    }
                  }
                  echo '<td style="color:black;text-shadow: 1px 1px 2px white;'.$bgColor.';min-width:51px;">'.$workPeriod.'</td>';
                }
                echo '</tr>';
              }
            } else {
              $bgColor='';
              if ($period>=$idealStartPeriod and $period<=$idealEndPeriod) {
                $bgColor='background:'.($isColorBlind)?'#d1ffb3':'#AEC5AE';
              }
              echo '<tr><td style="color:black;'.$bgColor.';min-width:51px;"></td></tr>';
              $bgColor='';
              if ($period>=$realStartPeriod and $period<=$realEndPeriod) {
                if ($period<=$idealEndPeriod) {
                  $bgColor='background:'.($isColorBlind)?'#d1ffb3':'#AEC5AE';
                } else { 
                  $bgColor='background:'.($isColorBlind)?redColorB:'#BB9099';
                }
              }          
              echo '<tr><td style="color:black;'.$bgColor.';min-width:51px;"></td></tr>';
            }
            echo '</table>';
          }
          echo '</td></tr>';
          $firstRow=false;
        }
      }
    }
    echo '</tbody>';
    echo '</table>';
  }

  public static function drawCriticalProjectResourceList($scale, $start, $end, $idProject=null, $limitedRow=null) {
    global $arrayProject;
    if (!isset(self::$_criticalResourceArray)) self::tranformPlanningResult($scale, $start, $end);
    echo '<table>';
    echo '  <thead>';
    echo '    <tr style="display:block;">';
    echo '      <td class="reportTableHeader" style="min-width:231px">'.i18n("colIdProject").'</td>';
    echo '      <td class="reportTableHeader"><div style="width:80px" title="'.i18n('helpCriticalResourceLate').'">'.lcfirst(i18n("colLate")).'</div></td>';
    echo '      <td class="reportTableHeader"><div style="width:80px">'.lcfirst(i18n("colStrategicValue")).'</div></td>';
    echo '      <td class="reportTableHeader"><div style="width:80px">'.lcfirst(i18n("Priority")).'</div></td>';
    echo '      <td class="reportTableHeader"><div style="width:80px" title="'.i18n('helpCriticalResourceUsed').'">'.lcfirst(i18n("used")).'</div></td>';
    echo '      <td class="reportTableHeader" style="min-width:110px">'.i18n("colIdResource").'</td>';
    echo '    </tr>';
    echo '  </thead>';
    echo '  <tbody style="display:block; overflow-y:scroll; height:200px; width:100%;">';
    $cpt=0;
    $totalArray=count(self::$_criticalResourceArray);
    if (pq_trim($idProject[0])=='') unset($idProject[0]);
    $result=array();
    if (! is_array($arrayProject)) $arrayProject=array();
    foreach (self::$_criticalResourceArray as $idRes=>$res) {
      if ($idProject) {
        $inArray=false;
        foreach ($idProject as $idProj) {
          if (!trim($idProj)) continue;
          if (isset($arrayProject[$idProj])) continue;
          $proj=new Project($idProj, true);
          $arrayProject[$idProj]=$proj->name;
          $subList=$proj->getSubProjectsList(true);
          if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
        }
        foreach ($arrayProject as $idProj=>$val) {
          if (array_key_exists($idProj, $res['projects'])) {
            $inArray=true;
          } else {
            continue;
          }
        }
        if (!$inArray) continue;
      }
      $firstRow=true;
      $cpt++;
      if ($limitedRow and $cpt>$limitedRow) continue;
      uasort($res['projects'], function ($x, $y) {
  			return $x['priority'] <=> $y['priority'];
      });
      foreach ($res['projects'] as $id=>$project) {
        if ($idProject) {
          $arrayProject=array();
          foreach ($idProject as $idProj) {
            if (!trim($idProj)) continue;
            $proj=new Project($idProj, true);
            $arrayProject[$idProj]=$proj->name;
            $subList=$proj->getSubProjectsList(true);
            if (count($subList)>0) $arrayProject=array_merge_preserve_keys($subList, $arrayProject);
          }
          if (!array_key_exists($id, $arrayProject)) continue;
        }
        $wbs=$project['wbs'];
        $total=$project['totalWork'];
        $strategicValue=$project['strategicValue'];
        $validatedEndDate=$project['validatedEndDate'];
        $plannedEndDate=$project['plannedEndDate'];
        $priority=$project['priority'];
        $late='';
        if ($plannedEndDate!='' and $validatedEndDate!='') {
          $late=dayDiffDates($validatedEndDate, $plannedEndDate);
          $late='<div style="color:'.(($late>0)?'#DD0000':'#00AA00').';">'.$late;
          $late.=" ".i18n("shortDay");
          $late.='</div>';
        }
        if (!isset($result[$priority.$wbs][$idRes])) $result[$priority.$wbs][$idRes]='';
        $result[$priority.$wbs][$idRes].='<tr style="height: 20px;position:relative">';
        $result[$priority.$wbs][$idRes].='<td class="reportTableData" style="text-align:left; width:225px; position:relative"><div class="dataContent" style="width:239px"><div class="dataExtend" style="min-width:235px">#'.$id.' '.$project['name'].'</div></div></td>';
        $result[$priority.$wbs][$idRes].='<td class="reportTableData"><div style="width:88px;">'.$late.'</div></td>';
        $result[$priority.$wbs][$idRes].='<td class="reportTableData"><div style="width:88px;">'.$strategicValue.'</div></td>';
        $result[$priority.$wbs][$idRes].='<td class="reportTableData"><div style="width:88px;">'.$priority.'</div></td>';
        $result[$priority.$wbs][$idRes].='<td class="reportTableData"><div style="width:88px;">'.Work::displayWorkWithUnit($total).'</div></td>';
        $result[$priority.$wbs][$idRes].='<td class="reportTableData" style="text-align:left;"><div class="dataContent" style="width:118px;position:relative"><div class="dataExtend" style="min-width:114px">'.$res['name'].'</div></div></td>';
        $result[$priority.$wbs][$idRes].='</tr>';
      }
    }
    ksort($result);
    foreach ($result as $projectRowList) {
      foreach ($projectRowList as $row) {
        echo $row;
      }
    }
    echo '</tbody>';
    echo '</table>';
  }

  public static function storeCriticalResourcePlanningResult($startDay) {
    global $cronnedScript, $fullListPlan, $arrayPlannedWork, $arrayRealWork, $arrayAssignment;
    // setSessionValue("CriticalResourceTable", array($startDay, $fullListPlan, $arrayPlannedWork, $arrayRealWork, $arrayAssignment)); Sauvegarde des donnees en session
    
    $dirLog=Parameter::getGlobalParameter("logFile");
    $dir=getCurrentDir($dirLog);
    
    $allData=array($startDay, $fullListPlan, $arrayPlannedWork, $arrayRealWork, $arrayAssignment);
    
    $file=fopen($dir.'critRes_allData.log', "w");
    file_put_contents($dir.'critRes_allData.log', serialize($allData));
    fclose($file);
    
    /*
     * Sauvegarde des données dans plusieurs fichiers
     * $file = fopen($dir. 'critRes_fullListPlan.log', "w");
     * file_put_contents($dir. 'critRes_fullListPlan.log', serialize($fullListPlan));
     * fclose($file);
     *
     * $file = fopen($dir. 'critRes_arrayPlannedWork.log', "w");
     * file_put_contents($dir. 'critRes_arrayPlannedWork.log', serialize($arrayPlannedWork));
     * fclose($file);
     *
     * $file = fopen($dir. 'critRes_arrayRealWork.log', "w");
     * file_put_contents($dir. 'critRes_arrayRealWork.log', serialize($arrayRealWork));
     * fclose($file);
     *
     * $file = fopen($dir. 'critRes_arrayAssignement.log', "w");
     * file_put_contents($dir. 'critRes_arrayAssignement.log', serialize($arrayAssignment));
     * fclose($file);
     *
     * $file = fopen($dir. 'critRes_startDay.log', "w");
     * file_put_contents($dir. 'critRes_startDay.log', serialize($startDay));
     * fclose($file);
     */
  }

  public static function getCriticalResourcePlanningResult() {
    global $cronnedScript, $fullListPlan, $arrayPlannedWork, $arrayRealWork, $arrayAssignment;
    
    /*
     * Sauvegarde des donnees en session
     * if (! sessionValueExists("CriticalResourceTable")) { return null;}
     * $cr=getSessionValue("CriticalResourceTable");
     * if (!is_array($cr)) return null;
     * $startDay=$cr[0];
     * $fullListPlan=$cr[1];
     * $arrayPlannedWork=$cr[2];
     * $arrayRealWork=$cr[3];
     * $arrayAssignment=$cr[4];
     * return $startDay;
     */
    
    $dirLog=Parameter::getGlobalParameter("logFile");
    $dir=getCurrentDir($dirLog);
    
    if (!file_exists($dir.'critRes_allData.log')) {
      return null;
    }
    
    projeqtor_set_memory_limit(filesize($dir.'critRes_allData.log').'K');
    
    $array=unserialize(file_get_contents($dir.'critRes_allData.log'));
    
    $startDay=$array[0];
    $fullListPlan=$array[1];
    $arrayPlannedWork=$array[2];
    $arrayRealWork=$array[3];
    $arrayAssignment=$array[4];
    
    /*
     * Sauvegarde des données dans plusieurs fichiers
     * if (!file_exists($dir.'critRes_fullListPlan.log')) {return null;}
     * if (!file_exists($dir.'critRes_arrayPlannedWork.log')) {return null;}
     * if (!file_exists($dir.'critRes_arrayRealWork.log')) {return null;}
     * if (!file_exists($dir.'critRes_arrayAssignement.log')) {return null;}
     * if (!file_exists($dir.'critRes_startDay.log')) {return null;}
     *
     * $startDay= unserialize(file_get_contents($dir.'critRes_startDay.log'));
     * $fullListPlan= unserialize(file_get_contents($dir.'critRes_fullListPlan.log'));
     * $arrayPlannedWork= unserialize(file_get_contents($dir.'critRes_arrayPlannedWork.log'));
     * $arrayRealWork= unserialize(file_get_contents($dir.'critRes_arrayRealWork.log'));
     * $arrayAssignment= unserialize(file_get_contents($dir.'critRes_arrayAssignement.log'));
     */
    
    return $startDay;
  }

  public static function unsetCriticalResourcePlanningResult() {
    
    /*
     * Sauvegarde des donnees en session
     * if (! sessionValueExists("CriticalResourceTable")) { return false;}
     * unsetSessionValue("CriticalResourceTable");
     */
    $dirLog=Parameter::getGlobalParameter("logFile");
    $dir=getCurrentDir($dirLog);
    
    if (!file_exists($dir.'critRes_allData.log')) {
      return null;
    }
    
    unlink($dir.'critRes_allData.log');
    
    /*
     * Sauvegarde des donnees dans plusieurs fichiers
     * if (!file_exists($dir.'critRes_fullListPlan.log')) {return null;}
     * if (!file_exists($dir.'critRes_arrayPlannedWork.log')) {return null;}
     * if (!file_exists($dir.'critRes_arrayRealWork.log')) {return null;}
     * if (!file_exists($dir.'critRes_arrayAssignement.log')) {return null;}
     * if (!file_exists($dir.'critRes_startDay.log')) {return null;}
     *
     * unlink($dir.'critRes_fullListPlan.log');
     * unlink($dir.'critRes_arrayPlannedWork.log');
     * unlink($dir.'critRes_arrayRealWork.log');
     * unlink($dir.'critRes_arrayAssignement.log');
     * unlink($dir.'critRes_startDay.log');
     */
    
    return true;
  }

}
?>