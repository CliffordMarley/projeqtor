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
 * Presents the list of objects of a given class.
 *
 */
require_once "projeqtor.php";
require_once "formatter.php";
scriptLog('   ->/view/refreshCriticalResources.php'); 

$proj = RequestHandler::getValue('idProjectCriticalResources');
$scale = RequestHandler::getValue('scaleCriticalResources');
$calculDate = RequestHandler::getValue('startDateCalculPlanning');
$start = RequestHandler::getValue('startDateCriticalResources');
$end = RequestHandler::getValue('endDateCriticalResources');
$maxResources = RequestHandler::getValue('nbCriticalResourcesValue');

$refreshData = RequestHandler::getValue('refreshData');

if ($refreshData) {
  Affectable::unsetCriticalResourcePlanningResult();
}

$lastDayStored=Affectable::getCriticalResourcePlanningResult();
if (! $lastDayStored or $lastDayStored!=$calculDate) {
  PlannedWork::plan('*',$calculDate,false,true,true);
  Affectable::storeCriticalResourcePlanningResult($calculDate);
}

$displayData = true;
if ($end < $start) {
  $displayData = false;
}

 if ($end != null && $start !=null && $scale !=null && $displayData) { ?>      
      <div style="height:220px;">
        <div style="margin-left:2%;width:46%; display:inline-block;">
          <span class="title" style="margin:5px;display:block;"><?php echo i18n('listCriticalResources');?></span>
          <div style="height:100%;border:0.1em solid grey; overflow-x:auto;">
          <?php 
              Affectable::drawCriticalResourceList($scale, $start, $end, $proj, $maxResources);
          ?>
          </div>
        </div>
        <div style="width:46%;display:inline-block;margin-left:2%;">
          <span class="title" style="margin:5px;display:block;"><?php  echo i18n('listCriticalResourcesByProject');?></span>
          <div style="height:100%; border:0.1em solid grey;  overflow-x:auto;">
            <?php Affectable::drawCriticalResourceProjectList($scale, $start, $end, $proj, $maxResources); ?>
          </div>
        </div>
      </div>
      <div style="margin-left:2%; height:305px; margin-top:50px; width:95%;">
        <span class="title" style="margin:5px;display:block;"><?php  echo i18n('listCriticalResourcesByScale');?></span>
        <div style="height:100%; border:0.1em solid grey; overflow-y:hidden; overflow-x:auto;">
          <?php Affectable::drawCriticalResourceActivityList($scale, $start, $end, $proj, $maxResources); ?>
        </div>
    </div>
    <div style="margin-left:2%; height:245px; margin-top:50px; width:45%;max-width:740px;">
        <span class="title" style="margin:5px;display:block;"><?php  echo i18n('listCriticalProjectResourcesList');?></span>
        <div style="height:100%; border:0.1em solid grey; overflow-y:hidden;">
          <?php Affectable::drawCriticalProjectResourceList($scale, $start, $end, $proj, $maxResources); ?>
        </div>
        <br/><br/>
    </div>
<?php } else { 
  echo '<div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noDataFound').'</div>';
  }?>