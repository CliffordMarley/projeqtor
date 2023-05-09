<?php
use SAML2\Request;
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

/** ===========================================================================
 * Move task (from before to)
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/moveLayoutColumn.php');

$list = RequestHandler::getValue('orderedList');
$arrayList=pq_explode("|", $list); // verifies valus are numeric in SqlElement base constructor.
$user=getSessionUser();
Sql::beginTransaction();
$cpt=0;
$result='';
foreach ($arrayList as $id) {
	if (pq_trim($id)) {
		$cpt++;	
	  $layout=new Layout($id);
	  $layout->sortOrder=$cpt;
		$result=$layout->save();
	}
}
displayLastOperationStatus($result);
?>