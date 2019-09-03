<?php
require_once('modules/Contacts/Contacts.php');

class ContactsKp extends Contacts {
	
	var $list_fields = Array();

	var $list_fields_name = Array(
		'Last Name'=>'lastname',
		'First Name'=>'firstname',
		'Account Name'=>'account_id',
		'Stabilimento'=>'stabilimento',
        'Email'=>'email',
        'Unita Organizzativa'=>'unita_organizzativa' 
	);
	
	
	function ContactsKp(){
		global $table_prefix;
		parent::__construct();
		$this->list_fields = Array(
			'Last Name'=>Array($table_prefix.'_contactdetails'=>'lastname'),
            'First Name'=>Array($table_prefix.'_contactdetails'=>'firstname'),
            'Account Name'=>Array($table_prefix.'_contactdetails'=>'accountid'),
            'Stabilimento'=>Array($table_prefix.'_contactdetails'=>'stabilimento'),
            'Email'=>Array($table_prefix.'_contactdetails'=>'email'),
            'Unita Organizzativa'=>Array($table_prefix.'_contactdetails'=>'unita_organizzativa') 
        );

	}

	function save_module($module){

        global $table_prefix, $adb;

		parent::save_module($module);

		require_once(__DIR__.'/ContactsCheck.php');

	}

	/* kpro@tom030920190946 */
	//mycrmv@185572
	public function getFixedOrderBy($module,$order_by,$sorder){
		global $table_prefix;
		if($order_by != 'lastname'){
			return parent::getFixedOrderBy($module,$order_by,$sorder);
		}
		else{
			$tablename = getTableNameForField($module, $order_by);
			$tablename = ($tablename != '')? ($tablename . '.') : '';
			return  ' ORDER BY ' . $tablename . $order_by . ", {$table_prefix}_crmentity.crmid " . $sorder;
		}
	}
	//mycrmv@185572e
	/* kpro@tom030920190946 end */
	
}
?>