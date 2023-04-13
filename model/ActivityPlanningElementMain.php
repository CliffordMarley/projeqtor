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

/* ============================================================================
 * Planning element is an object included in all objects that can be planned.
 */  
require_once('_securityCheck.php');
class ActivityPlanningElementMain extends PlanningElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $_separator_sectionDateAndDuration;
  public $_tab_5_3_smallLabel = array('validated', 'planned', 'real', '', 'requested', 'startDate', 'endDate', 'duration');
  public $validatedStartDate;
  public $plannedStartDate;
  public $realStartDate;
  public $latestStartDate;
  public $initialStartDate;
  public $validatedEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $latestEndDate;
  public $initialEndDate;
  public $validatedDuration;
  public $plannedDuration;
  public $realDuration;
  public $_void_4;
  public $initialDuration;
  public $_spe_isOnCriticalPath;
  public $_separator_sectionCostWork_marginTop;
  public $_tab_5_2_smallLabel_1 = array('validated', 'assigned', 'real', 'left', 'reassessed', 'work', 'cost');
  public $validatedWork;
  public $assignedWork;
  public $realWork;
  public $leftWork;
  public $plannedWork;
  public $validatedCost;
  public $assignedCost;
  public $realCost;
  public $leftCost;
  public $plannedCost;
  public $_separator_menuTechnicalProgress_marginTop;
  public $_tab_4_1_smallLabel_2 = array('toDeliver', 'toRealise', 'realised', 'left','workUnit');
  public $unitToDeliver;
  public $unitToRealise;
  public $unitRealised;
  public $unitLeft;
  public $_tab_5_1_smallLabel_8 = array('', '','','','','progress');
  public $unitProgress;
  public $idProgressMode;
  public $_label_weight;
  public $unitWeight;
  public $idWeightMode;
  public $_separator_sectionRevenue_marginTop;
  public $revenue;
  public $_spe_idWorkUnits;
  public $_separator_menuReview_marginTop;
  public $_tab_5_2_smallLabel_3 = array('', '', '', '', '', 'progress','priority');
  public $progress;
  public $_label_expected;
  public $expectedProgress;
  public $_label_wbs;
  public $wbs;
  public $priority;
  public $_label_planning;
  public $idActivityPlanningMode;
  public $_tab_1_1_smallLabel_1 = array('', 'color');
  public $color;
  public $_tab_3_1_3 = array('', '', '', 'minimumThreshold');
  public $minimumThreshold;
  public $_label_indivisibility;
  public $indivisibility;
  public $fixPlanning;
  public $_lib_helpFixPlanning;
  public $paused;
  public $_lib_helpPaused;
  public $_tab_5_1_smallLabel = array('workElementCount', 'estimated', 'real', 'left', '', 'ticket');
  public $workElementCount;
  public $workElementEstimatedWork;
  public $workElementRealWork;
  public $workElementLeftWork;
  public $_button_showTickets;
  //public $_label_wbs;
  //public $_label_progress;
  //public $_label_expected;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $idle;
  public $hasWorkUnit;
  
  private static $_fieldsAttributes=array(
    "plannedStartDate"=>"readonly,noImport",
    "realStartDate"=>"readonly,noImport",
    "plannedEndDate"=>"readonly,noImport",
    "realEndDate"=>"readonly,noImport",
    "plannedDuration"=>"readonly,noImport",
    "realDuration"=>"readonly,noImport",
    "initialWork"=>"hidden",
    "plannedWork"=>"readonly,noImport",
  	"notPlannedWork"=>"hidden",
    "realWork"=>"readonly,noImport",
    "leftWork"=>"readonly,noImport",
    "assignedWork"=>"readonly,noImport",
    "idActivityPlanningMode"=>"required,mediumWidth,colspan3",
    "idPlanningMode"=>"hidden,noImport",
    "indivisibility"=>"colspan3",
  	"workElementEstimatedWork"=>"readonly,noImport",
  	"workElementRealWork"=>"readonly,noImport",
  	"workElementLeftWork"=>"readonly,noImport",
  	"workElementCount"=>"display,noImport",
    "plannedStartFraction"=>"hidden",
    "plannedEndFraction"=>"hidden",
    "validatedStartFraction"=>"hidden",
    "validatedEndFraction"=>"hidden",
    "latestStartDate"=>"hidden",
    "latestEndDate"=>"hidden",
    "isOnCriticalPath"=>"hidden",
    "isManualProgress"=>"hidden",
    "_tab_5_1_smallLabel_4"=>"hidden",
    "_spe_isOnCriticalPath"=>"",
    "_label_indivisibility"=>"",
    "indivisibility"=>"",
    "minimumThreshold"=>"",
    "fixPlanning"=>"nobr",
     "paused"=>"nobr",
    "_separator_menuTechnicalProgress_marginTop"=>"hidden",
    "_separator_sectionRevenue_marginTop"=>"hidden",
    "hasWorkUnit"=>"hidden",
  );

  private static $_fieldsTooltip = array(
  		"minimumThreshold"=> "tooltipMinimumThreshold",
  		"indivisibility"=> "tooltipIndivisibility",
      "fixPlanning"=> "tooltipFixPlanningActivity",
      "expectedProgress"=> "tooltipFixPlanningActivity",
      "paused"=>"tooltipPausedActivity"
  );
  
  private static $_databaseTableName = 'planningelement';
  //private static $_databaseCriteria = array('refType'=>'Activity'); // Bad idea : sets a mess when moving projets and possibly elsewhere.
  
  private static $_databaseColumnName=array(
    "idActivityPlanningMode"=>"idPlanningMode"
  );
  
  private static $_colCaptionTransposition = array('initialStartDate'=>'requestedStartDate',
      'initialEndDate'=> 'requestedEndDate',
      'initialDuration'=>'requestedDuration'
  );
    
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }
  
  public function setAttributes() {
    global $contextForAttributes;
    $paramEnableWorkUnit = Parameter::getGlobalParameter('enableWorkCommandManagement');
    //if (Parameter::getGlobalParameter('PlanningActivity')=='YES') {
      $act=new Activity($this->refId,true);
      if ( ! $act->isPlanningActivity) {
        self::$_fieldsAttributes['workElementCount']='hidden';
        self::$_fieldsAttributes['workElementEstimatedWork']='hidden';
        self::$_fieldsAttributes['workElementRealWork']='hidden';
        self::$_fieldsAttributes['workElementLeftWork']='hidden';
        self::$_fieldsAttributes['_button_showTickets']='hidden';
      }
    //}
    if ($this->isAttributeSetToField('workElementCount', 'hidden')
    and $this->isAttributeSetToField('workElementEstimatedWork', 'hidden')
    and $this->isAttributeSetToField('workElementRealWork', 'hidden')
    and $this->isAttributeSetToField('workElementLeftWork', 'hidden')) {
      self::$_fieldsAttributes['_button_showTickets']='hidden';
    }
    $showLatest=Parameter::getGlobalParameter('showLatestDates');
    if ($showLatest) {
      self::$_fieldsAttributes['latestStartDate']="readonly";
      self::$_fieldsAttributes['latestEndDate']="readonly";
    }
    $user=getSessionUser();
    $priority=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$user->getProfile($this->idProject),'scope'=>'changeManualProgress'));
    if(!$this->isManualProgress or $priority and ($priority->rightAccess == 2 or ! $priority->id ) ){
      self::$_fieldsAttributes["progress"]='display';
    }else{
      self::$_fieldsAttributes["progress"]='';
    }
    $planningMode=new PlanningMode($this->idPlanningMode);
    $mode=$planningMode->code;
    if ($contextForAttributes!='multipleUpdate' and $mode!='ASAP' and $mode!='ALAP' and $mode!='START' and $mode!='GROUP') {
      $this->indivisibility=0;
      $this->minimumThreshold=0;
      self::$_fieldsAttributes["indivisibility"]='readonly';
      self::$_fieldsAttributes["minimumThreshold"]='readonly';
    } else {
      self::$_fieldsAttributes["indivisibility"]='';
      self::$_fieldsAttributes["minimumThreshold"]='';
    }
    if ($this->indivisibility){
      self::$_fieldsAttributes["minimumThreshold"]='required';
    }
    if($this->refId){
      $element=new $this->refType ($this->refId);
      if(property_exists($element, 'workOnRealTime') and $element->workOnRealTime==1){
        self::$_fieldsAttributes["validatedWork"]="readonly";
        self::$_fieldsAttributes["validatedCost"]="readonly";
        self::$_fieldsAttributes["_spe_idWorkUnits"]="hidden";
      }
    }
    if(Module::isModuleActive('moduleTechnicalProgress')){//Parameter::getGlobalParameter('technicalProgress')=='YES'
      self::$_fieldsAttributes['_separator_menuTechnicalProgress_marginTop']='';
      $asSon=$this->getSonItemsArray(true);
      if($asSon and count($asSon)>0){
        foreach ($asSon as $id=>$son ){
          if($son->refType!='Activity'){
            unset($asSon[$id]);
          }
        }
      }
      if(!$asSon or (!$this->id) or count($asSon)==0){
        if(!$this->id or $this->idProgressMode=='' or $this->idWeightMode=='' ){
          $this->idProgressMode=1;
          $this->idWeightMode=1;
          $this->unitToDeliver=0;
          $this->unitToRealise=0;
          $this->unitRealised=0;
          $this->unitLeft=0;
          $this->unitWeight=0;
          $this->unitProgress=0;
        }
        self::$_fieldsAttributes['unitToDeliver']='';
        self::$_fieldsAttributes['unitToRealise']='';
        self::$_fieldsAttributes['unitRealised']='';
        self::$_fieldsAttributes['unitLeft']='readonly';
        self::$_fieldsAttributes['unitProgress']='';
        self::$_fieldsAttributes['unitWeight']='';
        self::$_fieldsAttributes["_label_weight"]='';
        self::$_fieldsAttributes['idProgressMode']='size1/3,';
        self::$_fieldsAttributes['idWeightMode']='size1/3,';
      }else{
        if( $this->idProgressMode=='' or $this->idWeightMode=='' ){
          $this->idProgressMode=1;
          $this->idWeightMode=2;
        }
        if($this->unitProgress=='' or $this->unitWeight==''){
          $this->unitProgress=0;
          $this->unitWeight=0;
        }
        unset($this->_tab_4_1_smallLabel_2);
        self::$_fieldsAttributes['unitProgress']='';
        self::$_fieldsAttributes['unitWeight']='';
        self::$_fieldsAttributes['idProgressMode']='readonly,size1/3';
        self::$_fieldsAttributes['idWeightMode']='size1/3';
      }
      if($this->idProgressMode==1){
        self::$_fieldsAttributes['unitProgress']='readonly';
      }
      if($this->idWeightMode!=1){
        self::$_fieldsAttributes['unitWeight']='readonly';
      }
       self::$_fieldsAttributes['_tab_2_1_smallLabel_8']='nobr';
    }else{
      unset($this->_separator_menuTechnicalProgress_marginTop);
      unset($this->_tab_5_1_smallLabel_8);
      unset($this->_tab_4_1_smallLabel_2);
    }
    $project = new Project($this->idProject);
    if(Module::isModuleActive('moduleGestionCA')){
      $user=getSessionUser();
      $profile=$user->getProfile($this->idProject);
      $visibility1=PlanningElement::getCostVisibility($profile);
      $visibility2=PlanningElement::getWorkVisibility($profile);
      if($visibility1!='NO' and $visibility2 != 'NO'){
        self::$_fieldsAttributes['_separator_sectionRevenue_marginTop']='';
      }
      if (isset($contextForAttributes) and $contextForAttributes=='global'){
        self::$_fieldsAttributes['revenue']='';
      }
      if($project->ProjectPlanningElement->idRevenueMode == 2){
      	if($this->elementary){
      	  self::$_fieldsAttributes['revenue']='';
        	if($this->hasWorkUnit){
        	  if($this->validatedDuration){
        	   self::$_fieldsAttributes['validatedDuration']='readonly';
        	  }
        	  self::$_fieldsAttributes['revenue']='readonly';
        	  self::$_fieldsAttributes['validatedWork']='readonly';
        	  $CaReplaceValidCost= Parameter::getGlobalParameter('CaReplaceValidCost');
        	  if($CaReplaceValidCost=='YES'){
        	    self::$_fieldsAttributes['validatedCost']='readonly';
        	  }
        	}else{
        	  $CaReplaceValidCost= Parameter::getGlobalParameter('CaReplaceValidCost');
        	  if($CaReplaceValidCost=='YES' and $this->revenue > 0){
        	    self::$_fieldsAttributes['validatedCost']='readonly';
        	  }
        	}
      	}else{
      	  if ($this->refType=='Activity') {
      	    // Non elementary Activity : Revenue Readonly only if it has children activities (activity main contain only Meeting on Milestone) 
      	    $cpt=$this->countSqlElementsFromCriteria(array('refType'=>'Activity','topRefType'=>'Activity','topRefId'=>$this->refId)); 
      	    if ($cpt>0) {
      	      self::$_fieldsAttributes['revenue']='readonly';
      	    } else {
      	      self::$_fieldsAttributes['revenue']='';
      	    }
      	  }
      	}
      }else{
      	//unset($this->_separator_sectionRevenue_marginTop);
        self::$_fieldsAttributes['_separator_sectionRevenue_marginTop']='hidden';
      	unset($this->_tab_5_1_smallLabel_3);
      }
    }
    if($this->paused==1){
      self::$_fieldsAttributes["fixPlanning"]='readonly,nobr';
    }
    
    if($this->topRefId!=''){
      $parent=new $this->topRefType($this->topRefId);
      
    }
    if (isset($element) and SqlList::getFieldFromId("Status", $element->idStatus, "setPausedStatus")!=0 or (isset($parent) and $parent->paused==1) ){
      self::$_fieldsAttributes["paused"]="readonly,nobr";
    }
    
    if($this->id){
      $proj= new Project();
      $count=$proj->countSqlElementsFromCriteria(array("id"=>$this->idProject,'codeType'=>'ADM'));
      if($count!=0){
        if(self::$_fieldsAttributes["fixPlanning"]!="hidden")self::$_fieldsAttributes["fixPlanning"]="hidden";
        if(self::$_fieldsAttributes["paused"]!="hidden")self::$_fieldsAttributes["paused"]="hidden";
        if(isset(self::$_fieldsAttributes["revenue"])){
          if(self::$_fieldsAttributes["revenue"]!="hidden")self::$_fieldsAttributes["revenue"]="hidden";
        }
        self::$_fieldsAttributes["priority"]="hidden";
        self::$_fieldsAttributes["idActivityPlanningMode"]="hidden,mediumWidth,colspan3";
        self::$_fieldsAttributes["minimumThreshold"]="hidden";
        self::$_fieldsAttributes["indivisibility"]="hidden";
        unset($this->_tab_5_2_smallLabel_3);
        unset($this->_separator_sectionRevenue_marginTop);
        unset($this->_spe_idWorkUnits);
        self::$_fieldsAttributes["_label_expected"]="hidden";
        self::$_fieldsAttributes["expectedProgress"]="hidden";
        self::$_fieldsAttributes["_label_wbs"]="hidden";
        self::$_fieldsAttributes["progress"]="hidden";
        if(Module::isModuleActive('moduleTechnicalProgress')){
          unset($this->_separator_menuTechnicalProgress_marginTop);
          unset($this->_tab_5_1_smallLabel_8);
          unset($this->_tab_4_1_smallLabel_2);
          self::$_fieldsAttributes['unitToDeliver']="hidden";
          self::$_fieldsAttributes['unitToRealise']="hidden";
          self::$_fieldsAttributes['unitRealised']="hidden";
          self::$_fieldsAttributes['unitLeft']="hidden";
          self::$_fieldsAttributes['unitProgress']="hidden";
          self::$_fieldsAttributes['unitWeight']="hidden";
          self::$_fieldsAttributes["_label_weight"]="hidden";
          self::$_fieldsAttributes['idProgressMode']="hidden";
          self::$_fieldsAttributes['idWeightMode']="hidden";
        }
      }else{
        unset($this->_tab_5_1_smallLabel_4);
      }
    }else{
        unset($this->_tab_5_1_smallLabel_4);
    }
    if ($this->id and $this->elementary==0) {
      self::$_fieldsAttributes['idActivityPlanningMode']="hidden,mediumWidth,colspan3";
    }
  }
  /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
//   /** ========================================================================
//    * Return the specific database criteria
//    * @return the databaseTableName
//    */
//   protected function getStaticDatabaseCriteria() {
//     return self::$_databaseCriteria;
//   }  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  
  /** ========================================================================
   * Return the generic databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  protected function getStaticFieldsTooltip() {
  	return self::$_fieldsTooltip;
  }
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    if (! PlanningElement::$_noDispatch) $this->updateWorkElementSummary(true);
    if($this->idActivityPlanningMode){
      $this->idPlanningMode = $this->idActivityPlanningMode;
    }
    $old = $this->getOld();
    if($this->minimumThreshold){
      if($old->minimumThreshold != $this->minimumThreshold){
        $this->minimumThreshold = Work::convertWork($this->minimumThreshold);
      }
    }
    
    if($this->paused==1){
      if($this->fixPlanning!=1)$this->fixPlanning=1;
      $this->plannedStartDate=null;
      $this->plannedEndDate=null;
    }

    //florent
    if(($this->idPlanningMode=='23' and $old->idPlanningMode!='23')or($this->idPlanningMode!='23' and $old->idPlanningMode=='23') ){
      $pw= new PlannedWork();
      $ass=new Assignment();
      if($old->idPlanningMode=='23'){
        $pw= new PlannedWorkManual();
      }
      $clause= "idProject=".$this->idProject." and refType='".$this->refType."' and refId=".$this->refId;
      $pw->purge($clause);
      if($old->idPlanningMode!='23'){
        //$ass->plannedWork;
        $lstAss=$ass->getSqlElementsFromCriteria(null, null,$clause);
        if($lstAss){
          foreach ( $lstAss as $assign){
            if($assign->isResourceTeam==1){
              $assign->delete();
            }
            //$newLeft=$assign->leftWork-$assign->plannedWork;
            $assign->assignedWork=0;
            $assign->leftWork=0;
            $assign->plannedWork=$assign->realWork;
            $assign->assignedCost=0;
            $assign->leftCost=0;
            $assign->plannedCost=$assign->realCost;
            $assign->notPlannedWork=0;
            $assign->save();
          }
        }
      }
       //$this->updateSynthesis($this->refType, $this->refId);
    }
    if($this->plannedWork!=$old->plannedWork and $this->validatedWork!=$this->plannedWork){
      $act= new Activity($this->refId);
      if($act->workOnRealTime==1 ){
        $this->validatedWork=$this->plannedWork;
        $this->validatedCost=$this->plannedCost;
        $this->assignedCost=$this->plannedCost;
      }
    }
    if($this->elementary and $this->revenue > 0){
      $paramCA = Parameter::getGlobalParameter('CaReplaceValidCost');
      if($paramCA == 'YES' ) {
        $project = new Project($this->idProject);
        if (is_object($project->ProjectPlanningElement) and $project->ProjectPlanningElement->idRevenueMode == 2){
          $this->validatedCost = $this->revenue;
        }
      }
    }
    
    return parent::save();
  }
  
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $mode=null;
    if ($this->idActivityPlanningMode) {
      $mode=new ActivityPlanningMode($this->idActivityPlanningMode);
    }   
//     if ($mode) {
//       if ($mode->mandatoryStartDate and ! $this->validatedStartDate) {
//         $result.='<br/>' . i18n('errorMandatoryValidatedStartDate');
//       }
//       if ($mode->mandatoryEndDate and ! $this->validatedEndDate) {
//         $result.='<br/>' . i18n('errorMandatoryValidatedEndDate');
//       }
//       if ($mode->mandatoryDuration and ! $this->validatedDuration) {
//         $result.='<br/>' . i18n('errorMandatoryValidatedDuration');
//       }
   
//     }

    $old = $this->getOld();
    if($this->idActivityPlanningMode!='23' and $old->idPlanningMode=='23' and $this->plannedWork!='' and !SqlElement::isSaveConfirmed()){
      if(Parameter::getGlobalParameter('plannedWorkManualType')=="real" ){
        $result.='<br/>' . i18n('errorPlannedWorkManualType');
      }else{
        $result.='<br/>' . i18n('changePlanMan');
        $result.='<input type="hidden" name="confirmControl" id="confirmControl" value="save" />';
      }
    }else if($this->idActivityPlanningMode=='23' and $old->idPlanningMode!='23'){
      //gautier #4719
      $isPlannedWork = Parameter::getGlobalParameter('plannedWorkManualType');
      if($isPlannedWork =='planned'){
        $listAdmProj = Project::getAdminitrativeProjectList(true);
        if(in_array($this->idProject, $listAdmProj)){
          $result.='<br/>' . i18n('noPlannedWorkOnAdmProject');
        }
      }
      $ass=new Assignment();
      $critArray=array("idProject"=>$this->idProject,"refType"=>$this->refType,"refId"=>$this->refId);
      $assLst=$ass->getSqlElementsFromCriteria($critArray);
      $lstRes=array();
      foreach ($assLst as $ass){
         if(in_array($ass->idResource, $lstRes)){
           $result.='<br/>' . i18n('errorPlanWorkManDuplicate');
           break;
         }
         if($ass->isResourceTeam==1 and !SqlElement::isSaveConfirmed()){
           $result.='<br/>' . i18n('removePoolIntervention');
           $result.='<input type="hidden" name="confirmControl" id="confirmControl" value="save" />';
         }
        $lstRes["Assignement".$ass->id]=$ass->idResource;
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
    
  }
  
  /** =========================================================================
   * Update the synthesis Data (work) from workElement (tipically Tickets)
   * Called by workElement
   * @return void
   */
  public function updateWorkElementSummary($noSave=false) {
    $we=new WorkElement();  	
  	$weList=$we->getSqlElementsFromCriteria(array('idActivity'=>$this->refId));
  	$this->workElementEstimatedWork=0;
  	$this->workElementRealWork=0;
  	$this->workElementLeftWork=0;
  	$this->workElementCount=0;
  	foreach ($weList as $we) {
  		$this->workElementEstimatedWork+=$we->plannedWork;
  		$this->workElementRealWork+=$we->realWork;
  		$this->workElementLeftWork+=$we->leftWork;
  		$this->workElementCount+=1;
  	}
  	if (! $noSave) {
  	  $this->simpleSave();
  	}
  	$top=new Activity($this->refId);
  	$param=Parameter::getGlobalParameter('limitPlanningActivity');
  	if($param != "YES"){
  	  if ($this->workElementCount==0 and $top->isPlanningActivity) {
  	    $top->isPlanningActivity=0;
  	    $top->saveForced();
  	  } else if ($this->workElementCount>0 and !$top->isPlanningActivity) {
  	     $top->isPlanningActivity=1;
  	     $top->saveForced();
  	  }
  	}
  }
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript ( $colName );
    if ($colName == "fixPlanning") {
      if(Parameter::getUserParameter('paramLayoutObjectDetail')=="tab"){
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= ' dijit.byId("fixPlanning").set("value",dijit.byId("ActivityPlanningElement_fixPlanning").get("value"));';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
      }
//     }else if ($colName=="idWorkUnit") {
//       $colScript .= '<script type="dojo/connect" event="onChange" >';
//       $colScript .= '  var idComplexity=dijit.byId("ActivityPlanningElement_idComplexity").get("value");';
//       $colScript .= '  var idWorkUnit=dijit.byId("ActivityPlanningElement_idWorkUnit").get("value");';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idWorkCommand").set("value","");';
//       $colScript .= '  if(idWorkUnit == " "){';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idComplexity").set("value","");';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_quantity").set("value","");';
//       $colScript .= '   dojo.removeClass(dijit.byId("ActivityPlanningElement_idComplexity").domNode, "required");';
//       $colScript .= '   dojo.removeClass(dijit.byId("ActivityPlanningElement_quantity").domNode, "required");';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idComplexity").set("readOnly",true);';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_quantity").set("readOnly",true);';
//       $colScript .= '  }else{';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idComplexity").set("value","");';
//       $colScript .= '   dojo.addClass(dijit.byId("ActivityPlanningElement_idComplexity").domNode, "required");';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idComplexity").set("readOnly",false);';
//       $colScript .= '   dojo.addClass(dijit.byId("ActivityPlanningElement_quantity").domNode, "required");';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_quantity").set("readOnly",false);';
//       $colScript .= '   refreshListSpecific("idWorkUnit", "ActivityPlanningElement_idComplexity", "idWorkUnit",idWorkUnit);';
//       $colScript .= '  }';
//       $colScript .= '  formChanged();';
//       $colScript .= '</script>';
//     }else if ($colName=="idComplexity") {
//       $colScript .= '<script type="dojo/connect" event="onChange" >';
//       $colScript .= '  var idComplexity=dijit.byId("ActivityPlanningElement_idComplexity").get("value");';
//       $colScript .= '  var idWorkUnit=dijit.byId("ActivityPlanningElement_idWorkUnit").get("value");';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idWorkCommand").set("value","");';
//       $colScript .= '  if(idComplexity != " "){';
//       $colScript .= '   refreshListSpecific("idWorkCommand", "ActivityPlanningElement_idWorkCommand", "idWorkCommand",idWorkUnit+"separator"+idComplexity);';
//       $colScript .= '   dijit.byId("ActivityPlanningElement_idWorkCommand").set("readOnly",false);';
//       $colScript .= '  }';
//       $colScript .= '  formChanged();';
//       $colScript .= '</script>';
    }else if($colName=="paused"){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(this.checked){';
      $colScript .= '   dijit.byId("ActivityPlanningElement_fixPlanning").set("readOnly",true);';
      $colScript .= '   dijit.byId("ActivityPlanningElement_fixPlanning").set("checked",true);';
      $colScript .= '   dijit.byId("ActivityPlanningElement_fixPlanning").set("value",1);';
      $colScript .= '   dijit.byId("fixPlanning").set("readOnly",true);';
      $colScript .= '  }else{';
      $colScript .= '   dijit.byId("ActivityPlanningElement_fixPlanning").set("readOnly",false);';
      $colScript .= '   dijit.byId("ActivityPlanningElement_fixPlanning").set("checked",false);';
      $colScript .= '   dijit.byId("ActivityPlanningElement_fixPlanning").set("value",0);';
      $colScript .= '   dijit.byId("fixPlanning").set("readOnly",false);';
      $colScript .= '  }';
      if(Parameter::getUserParameter('paramLayoutObjectDetail')=="tab"){
      $colScript .= ' dijit.byId("paused").set("value",dijit.byId("ActivityPlanningElement_fixPlanning").get("value"));';
      $colScript .= '  formChanged();';
      }
      $colScript .= '</script>';
    }
    return $colScript;
  }
  public function drawSpecificItem($item) {
    if ($item=='showTickets') {
      echo '<div id="' . $item . 'Button" ';
      echo ' title="' . i18n('showTickets') . '" style="float:right;margin-right:3px;"';
      echo ' class="roundedButton">';
      echo '<div class="iconView iconSize16 imageColorNewGui" ';
      $jsFunction="showTickets('Activity',$this->refId);";
      echo ' onclick="' . $jsFunction . '"';
      echo '></div>';
      echo '</div>';
    } else if ($item=='isOnCriticalPath') {
      if ($this->id and $this->isOnCriticalPath and RequestHandler::getValue('criticalPathPlanning')) {
        echo '<div style="position:relative;"><div style="color:#AA0000;margin:0px 10px;text-align:center;position:absolute;top:-55px;height:60px;">'.i18n('colIsOnCriticalPath').'</div></div>';
      }
    }elseif ($item=='idWorkUnits'){
      $user=getSessionUser();
      $profile=$user->getProfile($this->idProject);
      $visibility1=PlanningElement::getCostVisibility($profile);
      $visibility2=PlanningElement::getWorkVisibility($profile);
      if( $visibility2 != 'NO'){
        if(Module::isModuleActive('moduleGestionCA') and $this->id){
          $project = new Project($this->idProject);
          if($project->ProjectPlanningElement->idRevenueMode == 2){
            $act = new Activity();
            $isParent = $act->countSqlElementsFromCriteria(array('idActivity'=>$this->refId));
            if(!$isParent){
              $activityWU = new ActivityWorkUnit();
              $listActWU = $activityWU->getSqlElementsFromCriteria(array('refId'=>$this->refId,'refType'=>'Activity'));
              $obj = new Activity($this->refId);
              drawActivityWorkUnit($listActWU,$obj,false,$visibility1);
            }
          }
        }
      }
    }
  }
}
?>