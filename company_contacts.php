<?php
/* Copyright (C) 2005 		Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2010 		Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2010-2011 	Regis Houssin        <regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/societe/commerciaux.php
 *  \ingroup    societe
 *  \brief      Page of links to sales representatives
 */

$res = '';
$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.formcompany.class.php");

require_once("./class/actions_companycontacts.class.php");

$langs->load("companies");
$langs->load("commercial");
$langs->load("customers");
$langs->load("suppliers");
$langs->load("banks");

// Security check
$socid = GETPOST('socid','int');
$fk_soc = GETPOST('fk_soc','int');
$action = GETPOST('action','alpha');

if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe','','');

$hookmanager->initHooks(array('companycontacts'));

$soc = new Societe($db);
$soc->fetch($socid);



/*
 *	Actions
 */
$parameters=array('socid'=>GETPOST('socid'));
$reshook=$hookmanager->executeHooks('doActions',$parameters,$soc,$action);    // Note that $action and $object may have been modified by some hooks
$error=$hookmanager->error; $errors=array_merge($errors, (array) $hookmanager->errors);


/*
 *	View
 */

$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('',$langs->trans("ThirdParty"),$help_url);

$form = new Form($db);
$formcompany = new FormCompany($db);

if ($socid > 0)
{
	$soc = new Societe($db);

	$result=$soc->fetch($socid);
	$head=societe_prepare_head($soc);

	dol_fiche_head($head, 'companycontacts', $langs->trans("ThirdParty"),0,'company');

	/*
	 * Fiche societe en mode visu
	 */

	print '<table class="border" width="100%">';

    print '<tr><td width="20%">'.$langs->trans('ThirdPartyName').'</td>';
    print '<td colspan="3">';
    print $form->showrefnav($soc,'socid','',($user->societe_id?0:1),'rowid','nom');
    print '</td></tr>';

	print '<tr>';
    print '<td>'.$langs->trans('CustomerCode').'</td><td'.(empty($conf->global->SOCIETE_USEPREFIX)?' colspan="3"':'').'>';
    print $soc->code_client;
    if ($soc->check_codeclient() <> 0) print ' '.$langs->trans("WrongCustomerCode");
    print '</td>';
    if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
    {
       print '<td>'.$langs->trans('Prefix').'</td><td>'.$soc->prefix_comm.'</td>';
    }
    print '</td>';
    print '</tr>';

	print "<tr><td valign=\"top\">".$langs->trans('Address')."</td><td colspan=\"3\">".nl2br($soc->address)."</td></tr>";

	print '<tr><td>'.$langs->trans('Zip').'</td><td width="20%">'.$soc->zip."</td>";
	print '<td>'.$langs->trans('Town').'</td><td>'.$soc->town."</td></tr>";

	print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">'.$soc->country.'</td>';

	print '<tr><td>'.$langs->trans('Phone').'</td><td>'.dol_print_phone($soc->phone,$soc->country_code,0,$soc->id,'AC_TEL').'</td>';
	print '<td>'.$langs->trans('Fax').'</td><td>'.dol_print_phone($soc->fax,$soc->country_code,0,$soc->id,'AC_FAX').'</td></tr>';

	print '<tr><td>'.$langs->trans('Web').'</td><td colspan="3">';
	if ($soc->url) { print '<a href="http://'.$soc->url.'">http://'.$soc->url.'</a>'; }
	print '</td></tr>';

	// Liste les commerciaux
	print '<tr><td valign="top">'.$langs->trans("SalesRepresentatives").'</td>';
	print '<td colspan="3">';

	$sql = "SELECT u.rowid, u.lastname, u.firstname";
	$sql .= " FROM ".MAIN_DB_PREFIX."user as u";
	$sql .= " , ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql .= " WHERE sc.fk_soc =".$soc->id;
	$sql .= " AND sc.fk_user = u.rowid";
	$sql .= " ORDER BY u.lastname ASC ";
	dol_syslog('societe/commerciaux.php::list salesman sql = '.$sql,LOG_DEBUG);
	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);

 			$parameters=array('socid'=>$soc->id);
        	$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$obj,$action);    // Note that $action and $object may have been modified by hook
      		if (empty($reshook)) {

				null; // actions in normal case
      		}

			print '<a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$obj->rowid.'">';
			print img_object($langs->trans("ShowUser"),"user").' ';
			print dolGetFirstLastname($obj->firstname, $obj->lastname)."\n";
			print '</a>&nbsp;';
			if ($user->rights->societe->creer)
			{
			    print '<a href="commerciaux.php?socid='.$_GET["socid"].'&amp;delcommid='.$obj->rowid.'">';
			    print img_delete();
			    print '</a>';
			}
			print '<br>';
			$i++;
		}

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
	if($i == 0) { print $langs->trans("NoSalesRepresentativeAffected"); }

	print "</td></tr>";

	print '</table>';
	print "</div>\n";


	if ($user->rights->societe->creer && $user->rights->societe->client->voir )
	{
		/*
		 * Liste
		 *
		 */

		$langs->load("contactfunction@contactfunction");

		if($action == 'link_contact' || $action == 'update_link')
		{

			$fk_soc = GETPOST('fk_soc','int');
			$fk_contact = GETPOST('fk_contact','int');
			$function_code = GETPOST('function_code','int');
			$department_code = GETPOST('department_code','int');
			$lineid = GETPOST('lineid');

			$link_contact = new Companycontacts($db);
			if($lineid)
			{
				$res = $link_contact->fetch(GETPOST('lineid'),'int');

			}
			$fk_soc = GETPOST('fk_soc','int') ? GETPOST('fk_soc','int') : ($link_contact->fk_soc ? $link_contact->fk_soc : '');
			$fk_contact = GETPOST('fk_contact','int') ? GETPOST('fk_contact','int') : ($link_contact->fk_contact ? $link_contact->fk_contact : '');
			$function_code = GETPOST('function_code','int') ? GETPOST('function_code','int') : ($link_contact->function_code ? $link_contact->function_code : '');
			$department_code = GETPOST('department_code','int') ? GETPOST('department_code','int') : ($link_contact->department_code ? $link_contact->department_code : '');


			$title=$langs->trans("LinkAcontact");
			print_titre($title);

			print "<form method=\"POST\" name=\"company_contacts\" enctype=\"multipart/form-data\" action=\"".$_SERVER['PHP_SELF']."\">\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<input type="hidden" name="socid" value="'.$socid.'">';
			print '<input type="hidden" name="lineid" value="'.$lineid.'">';

			if($action == 'link_contact')
			{
				print '<input type="hidden" name="action" value="add_contact_link">';

			}
			else if($action == 'update_link' ) {
				print '<input type="hidden" name="action" value="update_contact_link">';
			}

			print "<table class=\"border\" width=\"100%\">\n";

			// Company
			print '<tr><td>'.$langs->trans("SelectThirdParty").'</td><td >';
			$events_form=array();
			$events_form[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'fk_contact', 'params' => '');
			print $form->select_company($fk_soc,'fk_soc','','','','',$events_form);
			print '</td>';

			// Contact
			print "<td width=\"".$width."\">".$langs->trans("Contact")."</td>";
			print '<td>';
			$form->select_contacts($fk_soc,$fk_contact,'fk_contact',1);
			print '</td>';
			print "</tr>\n";

			// Contact Function & department
			print '<tr><td>'.$langs->trans("ContactFunctionOf").'</td><td>';
			print $formcompany->select_contact_functions($function_code,'function_code',1);
			print '</td>';
			print '<td>'.$langs->trans("ContactDepartmentOf").'</td><td>';
			print $formcompany->select_contact_departments($department_code,'department_code',1);
			print '</td>';
			print "</tr>\n";

			print "<tr><td width=\"".$width."\"></td>";
			print '<td colspan="3">';
			if($action == 'link_contact')
			{
				print ' <input type="submit" name="btn_add_link" class="button" value="'.$langs->trans('Add').'" />';
			}
			else if($action == 'update_link' ) {
				print ' <input type="submit" name="btn_update_link" class="button" value="'.$langs->trans('Update').'" />';
			}
			print '</td>';
			print "</tr>\n";

			print "</table>";

		}
		else {

			print '<div class="tabsAction">';
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] .'?action=link_contact' . ($socid ? "&socid=$socid" : '') . '">' . $langs->trans("LinkAContact") . '</a>';
			print '</div>';
		}

		$sql = "SELECT c.rowid,p.rowid as contact_id ";
		$sql.= ", p.firstname, p.lastname";
		$sql.= ", dep.label as department_label";
		$sql.= ", fun.label as function_label";
		$sql.= " FROM ".MAIN_DB_PREFIX."company_contacts as c";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as p ON p.rowid=c.fk_contact";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_contact_function as fun ON fun.code=c.function_code";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_contact_department as dep ON dep.code=c.department_code";
		$sql.= " WHERE c.entity IN (0,".$conf->entity.")";
		$sql.= " AND c.fk_soc_source='".$socid."'";
		if (! empty($conf->global->USER_HIDE_INACTIVE_IN_COMBOBOX)) $sql.= " AND u.statut<>0 ";
		$sql.= " ORDER BY c.rowid ASC ";

		$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;

			$title=$langs->trans("ListOfContactsLinked");
			print_titre($title);

			// Lignes des titres
			print '<table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Name").'</td>';
			print '<td>'.$langs->trans("Function").'</td>';
			print '<td>'.$langs->trans("Department").'</td>';
			print '<td>&nbsp;</td>';
			print "</tr>\n";

			$var=True;

			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				$var=!$var;

				print "<tr ".$bc[$var]."><td>";
				print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$obj->contact_id.'">';
				print img_object($langs->trans("ShowContact"),"contact").' ';
				print dolGetFirstLastname($obj->firstname, $obj->lastname)."\n";
				print '</a>';
				print '</td><td>'.$obj->function_label.'</td>';
				print '</td><td>'.$obj->department_label.'</td>';
				print '<td><a href="'.$_SERVER['PHP_SELF'].'?action=update_link&socid='.$socid.'&amp;lineid='.$obj->rowid.'">'.$langs->trans("Edit").'</a>';
				print ' <a href="'.$_SERVER['PHP_SELF'].'?action=delete_contact_link&socid='.$socid.'&amp;lineid='.$obj->rowid.'">'.$langs->trans("Delete").'</a>';
				print '</td>';
				print '</tr>'."\n";
				$i++;
			}

			print "</table>";
			$db->free($resql);
		}
		else
		{
			dol_print_error($db);
		}
	}

}


$db->close();

llxFooter();
