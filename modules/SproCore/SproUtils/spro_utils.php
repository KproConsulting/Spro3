<?php

function recuperaTipiCorsoDallaMansione($risorsa_mansione_id){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom2412015 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2015, Kpro Consulting Srl
     * @package VteSicurezza
     * @version 1.0
     * 
     * Questa funzione recupera dalla mansione relazionata alla risorsa i relativi tipi corso
     */
    
    $delete = "DELETE FROM {$table_prefix}_crmentityrel
                WHERE crmid = ".$risorsa_mansione_id." AND module = 'MansioniRisorsa' AND relmodule = 'TipiCorso'";
    $adb->query($delete);
    
    $delete = "DELETE FROM {$table_prefix}_crmentityrel
                WHERE relcrmid = ".$risorsa_mansione_id." AND module = 'TipiCorso' AND relmodule = 'MansioniRisorsa'";
    $adb->query($delete);
    
    $record_scritti = 0;
    
    $q_mansione = "SELECT mansione FROM {$table_prefix}_mansionirisorsa 
                    WHERE mansionirisorsaid = ".$risorsa_mansione_id;
    $res_mansione = $adb->query($q_mansione);
    if($adb->num_rows($res_mansione)>0){
        
        $mansione_id = $adb->query_result($res_mansione,0,'mansione');
        $mansione_id = html_entity_decode(strip_tags($mansione_id), ENT_QUOTES,$default_charset);
                
        $q_tipi_corso = "(SELECT rel1.relcrmid tipo_corso FROM {$table_prefix}_crmentityrel rel1
                            INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                            WHERE ent1.deleted = 0 AND rel1.crmid = ".$mansione_id." AND rel1.relmodule = 'TipiCorso')
                            UNION
                            (SELECT rel2.crmid tipo_corso FROM {$table_prefix}_crmentityrel rel2
                            INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                            WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$mansione_id." AND rel2.module = 'TipiCorso')";
        
        $res_tipi_corso = $adb->query($q_tipi_corso);
        $num_tipi_corso = $adb->num_rows($res_tipi_corso);
        for($i=0; $i<$num_tipi_corso; $i++){	

            $tipo_corso = $adb->query_result($res_tipi_corso,$i,'tipo_corso');
            $tipo_corso = html_entity_decode(strip_tags($tipo_corso), ENT_QUOTES,$default_charset);
            
            $q_ver_esistenza = "(SELECT * FROM {$table_prefix}_crmentityrel rel1
                                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                                    WHERE ent1.deleted = 0 AND rel1.crmid = ".$risorsa_mansione_id." AND rel1.relcrmid = ".$tipo_corso.")
                                    UNION
                                    (SELECT * FROM {$table_prefix}_crmentityrel rel2
                                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                                    WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$risorsa_mansione_id." AND rel2.crmid = ".$tipo_corso.")";
            $res_ver_esistenza = $adb->query($q_ver_esistenza);
            if($adb->num_rows($res_ver_esistenza)==0){
                
                $insert_relazione = "INSERT INTO {$table_prefix}_crmentityrel
                                        (crmid, module, relcrmid, relmodule)
                                        VALUES (".$risorsa_mansione_id.", 'MansioniRisorsa', ".$tipo_corso.", 'TipiCorso')";
                $adb->query($insert_relazione);
                
                $record_scritti++;
                
            }
            
        }    
        
    }
    
    return $record_scritti;
    
}

function recuperaTipiVisiteMedicheDallaMansione($risorsa_mansione_id){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom2412015 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2015, Kpro Consulting Srl
     * @package VteSicurezza
     * @version 1.0
     * 
     * Questa funzione recupera dalla mansione relazionata alla risorsa i relativi tipi di visite mediche
     */
    
    $delete = "DELETE FROM {$table_prefix}_crmentityrel
                WHERE crmid = ".$risorsa_mansione_id." AND module = 'MansioniRisorsa' AND relmodule = 'TipiVisitaMed'";
    $adb->query($delete);
    
    $delete = "DELETE FROM {$table_prefix}_crmentityrel
                WHERE relcrmid = ".$risorsa_mansione_id." AND module = 'TipiVisitaMed' AND relmodule = 'MansioniRisorsa'";
    $adb->query($delete);
    
    $record_scritti = 0;
    
    $q_mansione = "SELECT mansione FROM {$table_prefix}_mansionirisorsa 
                    WHERE mansionirisorsaid = ".$risorsa_mansione_id;
    $res_mansione = $adb->query($q_mansione);
    if($adb->num_rows($res_mansione)>0){
        
        $mansione_id = $adb->query_result($res_mansione,0,'mansione');
        $mansione_id = html_entity_decode(strip_tags($mansione_id), ENT_QUOTES,$default_charset);
                
        $q_tipi_visite = "(SELECT rel1.relcrmid tipo_visita FROM {$table_prefix}_crmentityrel rel1
                            INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                            WHERE ent1.deleted = 0 AND rel1.crmid = ".$mansione_id." AND rel1.relmodule = 'TipiVisitaMed')
                            UNION
                            (SELECT rel2.crmid tipo_visita FROM {$table_prefix}_crmentityrel rel2
                            INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                            WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$mansione_id." AND rel2.module = 'TipiVisitaMed')";
        
        $res_tipi_visite = $adb->query($q_tipi_visite);
        $num_tipi_visite = $adb->num_rows($res_tipi_visite);
        for($i=0; $i<$num_tipi_visite; $i++){	

            $tipo_visita = $adb->query_result($res_tipi_visite,$i,'tipo_visita');
            $tipo_visita = html_entity_decode(strip_tags($tipo_visita), ENT_QUOTES,$default_charset);
            
            $q_ver_esistenza = "(SELECT * FROM {$table_prefix}_crmentityrel rel1
                                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                                    WHERE ent1.deleted = 0 AND rel1.crmid = ".$risorsa_mansione_id." AND rel1.relcrmid = ".$tipo_visita.")
                                    UNION
                                    (SELECT * FROM {$table_prefix}_crmentityrel rel2
                                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                                    WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$risorsa_mansione_id." AND rel2.crmid = ".$tipo_visita.")";
            $res_ver_esistenza = $adb->query($q_ver_esistenza);
            if($adb->num_rows($res_ver_esistenza)==0){
                
                $insert_relazione = "INSERT INTO {$table_prefix}_crmentityrel
                                        (crmid, module, relcrmid, relmodule)
                                        VALUES (".$risorsa_mansione_id.", 'MansioniRisorsa', ".$tipo_visita.", 'TipiVisitaMed')";
                $adb->query($insert_relazione);
                
                $record_scritti++;
                
            }
            
        }    
        
    }
    
    return $record_scritti;
    
}

function recuperaCategoriePrivacyDallaMansione($risorsa_mansione_id){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom01092017 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2017, Kpro Consulting Srl
     * 
     * Questa funzione recupera dalla mansione relazionata alla risorsa le relative categorie Privacy
     */
    
    $delete = "DELETE FROM {$table_prefix}_crmentityrel
                WHERE crmid = ".$risorsa_mansione_id." AND module = 'MansioniRisorsa' AND relmodule = 'KpCategoriePrivacy'";
    $adb->query($delete);
    
    $delete = "DELETE FROM {$table_prefix}_crmentityrel
                WHERE relcrmid = ".$risorsa_mansione_id." AND module = 'KpCategoriePrivacy' AND relmodule = 'MansioniRisorsa'";
    $adb->query($delete);
    
    $record_scritti = 0;
    
    $q_mansione = "SELECT mansione FROM {$table_prefix}_mansionirisorsa 
                    WHERE mansionirisorsaid = ".$risorsa_mansione_id;
    $res_mansione = $adb->query($q_mansione);
    if($adb->num_rows($res_mansione)>0){
        
        $mansione_id = $adb->query_result($res_mansione, 0, 'mansione');
        $mansione_id = html_entity_decode(strip_tags($mansione_id), ENT_QUOTES,$default_charset);
                
        $q_tipi_visite = "(SELECT rel1.relcrmid categoria_privacy FROM {$table_prefix}_crmentityrel rel1
                            INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                            WHERE ent1.deleted = 0 AND rel1.crmid = ".$mansione_id." AND rel1.relmodule = 'KpCategoriePrivacy')
                            UNION
                            (SELECT rel2.crmid categoria_privacy FROM {$table_prefix}_crmentityrel rel2
                            INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                            WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$mansione_id." AND rel2.module = 'KpCategoriePrivacy')";
        
        $res_tipi_visite = $adb->query($q_tipi_visite);
        $num_tipi_visite = $adb->num_rows($res_tipi_visite);
        for($i=0; $i<$num_tipi_visite; $i++){	

            $categoria_privacy = $adb->query_result($res_tipi_visite,$i,'categoria_privacy');
            $categoria_privacy = html_entity_decode(strip_tags($categoria_privacy), ENT_QUOTES,$default_charset);
            
            $q_ver_esistenza = "(SELECT * FROM {$table_prefix}_crmentityrel rel1
                                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                                    WHERE ent1.deleted = 0 AND rel1.crmid = ".$risorsa_mansione_id." AND rel1.relcrmid = ".$categoria_privacy.")
                                    UNION
                                    (SELECT * FROM {$table_prefix}_crmentityrel rel2
                                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                                    WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$risorsa_mansione_id." AND rel2.crmid = ".$categoria_privacy.")";
            $res_ver_esistenza = $adb->query($q_ver_esistenza);
            if($adb->num_rows($res_ver_esistenza)==0){
                
                $insert_relazione = "INSERT INTO {$table_prefix}_crmentityrel
                                        (crmid, module, relcrmid, relmodule)
                                        VALUES (".$risorsa_mansione_id.", 'MansioniRisorsa', ".$categoria_privacy.", 'KpCategoriePrivacy')";
                $adb->query($insert_relazione);
                
                $record_scritti++;
                
            }
            
        }    
        
    }
    
    return $record_scritti;
    
}

function recuperaProdottiDallaMansione($risorsa_mansione_id){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid04062018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     * 
     * Questa funzione recupera dalla mansione relazionata alla risorsa i relativi prodotti
     */
    
    $delete = "DELETE FROM {$table_prefix}_seproductsrel
                WHERE crmid = ".$risorsa_mansione_id;
    $adb->query($delete);
    
    $record_scritti = 0;
    
    $q_mansione = "SELECT mansione FROM {$table_prefix}_mansionirisorsa 
                    WHERE mansionirisorsaid = ".$risorsa_mansione_id;
    $res_mansione = $adb->query($q_mansione);
    if($adb->num_rows($res_mansione)>0){
        
        $mansione_id = $adb->query_result($res_mansione, 0, 'mansione');
        $mansione_id = html_entity_decode(strip_tags($mansione_id), ENT_QUOTES,$default_charset);
                
        $q_prodotti = "SELECT prodrel.productid prodotto
					FROM {$table_prefix}_seproductsrel prodrel
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = prodrel.productid
					WHERE ent.deleted = 0 AND prodrel.crmid = ".$mansione_id;
        
        $res_prodotti = $adb->query($q_prodotti);
        $num_prodotti = $adb->num_rows($res_prodotti);
        for($i=0; $i<$num_prodotti; $i++){	

            $prodotto = $adb->query_result($res_prodotti,$i,'prodotto');
            $prodotto = html_entity_decode(strip_tags($prodotto), ENT_QUOTES,$default_charset);
            
            $q_ver_esistenza = "SELECT *
							FROM {$table_prefix}_seproductsrel prodrel
							INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = prodrel.productid
							WHERE ent.deleted = 0 AND prodrel.crmid = ".$risorsa_mansione_id."
							AND prodrel.productid = ".$prodotto;
            $res_ver_esistenza = $adb->query($q_ver_esistenza);
            if($adb->num_rows($res_ver_esistenza)==0){
                
                $insert_relazione = "INSERT INTO {$table_prefix}_seproductsrel
                                        (crmid, productid, setype)
                                        VALUES (".$risorsa_mansione_id.", ".$prodotto.", 'Products')";
                $adb->query($insert_relazione);
                
                $record_scritti++;
                
            }
            
        }    
        
    }
    
    return $record_scritti;
    
}

function calcolaSituazioneFormazione(){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom010220170902 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2017, Kpro Consulting Srl
     */
	 
	printf("<br />Calcolo situazione formazione iniziato!");

	aggiornaStatoMansioniRisorseNonAttive();
	 
	$id_statici = getConfigurazioniIdStatici();
	$id_statico = $id_statici["Programmi Custom - Gestione Avvisi - Giorni per In Scadenza standard"];
	if( $id_statico["valore"] == "" && $id_statico["valore"] == 0){
		$default_in_scadenza = 150;
	}
	else{
		$default_in_scadenza = $id_statico["valore"];
	}
	 
	$lista_aziende = getAziendePerSituazioneFormazione();
	
	foreach($lista_aziende as $azienda){
		
		printf("<br />--- Azienda: ".$azienda['accountid']);
		
		$giorni_in_scadenza = getGiorniInScadenzaAzienda($azienda['accountid'], $default_in_scadenza, 'Formazione');
		
		printf(" Giorni in scadenza: ".$giorni_in_scadenza);
		
		calcolaSituazioneFormazioneAzienda($azienda['accountid'], $giorni_in_scadenza);
		
	}
	
	printf("<br />Calcolo situazione formazione terminato!");
    
}

function getAziendePerSituazioneFormazione(){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     * @package situazioneFormazione
     * @version 1.0
     */
	
	$result = array();
	
	$data_corrente = date("Y-m-d");
	
	$q_account = "SELECT 
					cont.accountid accountid
					FROM {$table_prefix}_mansionirisorsa mr
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = mr.mansionirisorsaid
                    INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = mr.risorsa
                    WHERE ent.deleted = 0 AND mr.stato_mansione = 'Attiva'
					AND (cont.data_fine_rap IS NULL OR cont.data_fine_rap = '' OR cont.data_fine_rap > '".$data_corrente."' OR cont.data_fine_rap = '0000-00-00')
                    GROUP BY cont.accountid";
					
	//printf("<br />Query lista Aziende: <br /> %s", $q_account); die;
	
    $res_account = $adb->query($q_account);
    $num_account = $adb->num_rows($res_account);

    for($i=0; $i<$num_account; $i++){		

        $account = $adb->query_result($res_account,$i,'accountid');
        $account = html_entity_decode(strip_tags($account), ENT_QUOTES, $default_charset);
		
		$result[] = array('accountid' => $account);
		
	}
	
	return $result;
	
}

function getGiorniInScadenzaAzienda($azienda, $default, $tipo_avviso, $record = 0){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$result = $default;

	$giorni_in_scadenza = 0;
	if($tipo_avviso == 'Documenti' && $record != 0){
		$dati_tipo_documento = getDatiTipoDocumento($record);
		$giorni_in_scadenza = $dati_tipo_documento['giorni_in_scadenza'];
	}
	
	if($giorni_in_scadenza == 0){
		$q_avvisi = "SELECT avv.giorni_in_scadenza
						FROM {$table_prefix}_gestioneavvisi avv
						INNER JOIN {$table_prefix}_account acc ON acc.accountid = avv.stabilimento
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
						WHERE ent.deleted = 0 AND avv.tipo_avviso = '{$tipo_avviso}' 
						AND acc.accountid = ".$azienda;
		$res_avvisi = $adb->query($q_avvisi);
		if($adb->num_rows($res_avvisi)>0){
			
			$giorni_in_scadenza = $adb->query_result($res_avvisi,0,'giorni_in_scadenza');
			$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
			
			if($giorni_in_scadenza == null || $giorni_in_scadenza == ''){
				
				$giorni_in_scadenza = 0;
				
			}
			
		}
	}

	if($giorni_in_scadenza == 0){
		$q_avvisi = "SELECT avv.giorni_in_scadenza
						FROM {$table_prefix}_gestioneavvisi avv
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
						WHERE ent.deleted = 0 AND avv.tipo_avviso = '{$tipo_avviso}' 
						AND (avv.stabilimento IS NULL OR avv.stabilimento = '' OR avv.stabilimento = 0) 
						AND avv.kp_tipo_sogg_avvisi = 'Aziende'";
		$res_avvisi = $adb->query($q_avvisi);
		if($adb->num_rows($res_avvisi)>0){
			
			$giorni_in_scadenza = $adb->query_result($res_avvisi,0,'giorni_in_scadenza');
			$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
			
			if($giorni_in_scadenza == null || $giorni_in_scadenza == ''){
				
				$giorni_in_scadenza = 0;
				
			}
			
		}
	}
	
	if($giorni_in_scadenza == 0){
		
		$giorni_in_scadenza = $default;
		
	}
	
	$result = $giorni_in_scadenza;
	
	return $result;
		
}

function getGiorniInScadenzaFornitore($fornitore, $default, $tipo_avviso, $record = 0){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid29052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = $default;

	$giorni_in_scadenza = 0;
	if($tipo_avviso == 'Documenti' && $record != 0){
		$dati_tipo_documento = getDatiTipoDocumento($record);
		$giorni_in_scadenza = $dati_tipo_documento['giorni_in_scadenza'];
	}
	
	if($giorni_in_scadenza == 0){
		$q_avvisi = "SELECT avv.giorni_in_scadenza
						FROM {$table_prefix}_gestioneavvisi avv
						INNER JOIN {$table_prefix}_vendor vend ON vend.vendorid = avv.stabilimento
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
						WHERE ent.deleted = 0 AND avv.tipo_avviso = '{$tipo_avviso}' 
						AND vend.vendorid = ".$fornitore;
		$res_avvisi = $adb->query($q_avvisi);
		if($adb->num_rows($res_avvisi)>0){
			
			$giorni_in_scadenza = $adb->query_result($res_avvisi,0,'giorni_in_scadenza');
			$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
			
			if($giorni_in_scadenza == null || $giorni_in_scadenza == ''){
				
				$giorni_in_scadenza = 0;
				
			}
			
		}
	}

	if($giorni_in_scadenza == 0){
		$q_avvisi = "SELECT avv.giorni_in_scadenza
					FROM {$table_prefix}_gestioneavvisi avv
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
					WHERE ent.deleted = 0 AND avv.tipo_avviso = '{$tipo_avviso}' 
					AND (avv.stabilimento IS NULL OR avv.stabilimento = '' OR avv.stabilimento = 0) 
					AND avv.kp_tipo_sogg_avvisi = 'Fornitori'";
		$res_avvisi = $adb->query($q_avvisi);
		if($adb->num_rows($res_avvisi)>0){
			
			$giorni_in_scadenza = $adb->query_result($res_avvisi,0,'giorni_in_scadenza');
			$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
			
			if($giorni_in_scadenza == null || $giorni_in_scadenza == ''){
				
				$giorni_in_scadenza = 0;
				
			}
			
		}
	}
	
	if($giorni_in_scadenza == 0){
		
		$giorni_in_scadenza = $default;
		
	}
	
	$result = $giorni_in_scadenza;
	
	return $result;
		
}

function getGiorniInScadenzaDocumenti($documento, $default, $tipo_documento){
	global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid29052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = $default;

	$giorni_in_scadenza = 0;
	if($tipo_documento != 0){
		$dati_tipo_documento = getDatiTipoDocumento($tipo_documento);
		$giorni_in_scadenza = $dati_tipo_documento['giorni_in_scadenza'];
	}

	if($giorni_in_scadenza == 0){
		$q_avvisi = "SELECT avv.giorni_in_scadenza
					FROM {$table_prefix}_gestioneavvisi avv
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
					WHERE ent.deleted = 0 AND avv.tipo_avviso = 'Documenti'
					AND avv.stabilimento IN (
						SELECT crmid 
						FROM {$table_prefix}_senotesrel
						WHERE notesid = {$documento} AND relmodule IN ('Accounts','Vendors')
						GROUP BY crmid
					)";
		$res_avvisi = $adb->query($q_avvisi);
		if($adb->num_rows($res_avvisi)>0){
			
			$giorni_in_scadenza = $adb->query_result($res_avvisi,0,'giorni_in_scadenza');
			$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
			
			if($giorni_in_scadenza == null || $giorni_in_scadenza == ''){
				
				$giorni_in_scadenza = 0;
				
			}
			
		}
	}

	if($giorni_in_scadenza == 0){
		$q_avvisi = "SELECT avv.giorni_in_scadenza
					FROM {$table_prefix}_gestioneavvisi avv
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
					WHERE ent.deleted = 0 AND avv.tipo_avviso = 'Documenti' 
					AND (avv.stabilimento IS NULL OR avv.stabilimento = '' OR avv.stabilimento = 0)";
		$res_avvisi = $adb->query($q_avvisi);
		if($adb->num_rows($res_avvisi)>0){
			
			$giorni_in_scadenza = $adb->query_result($res_avvisi,0,'giorni_in_scadenza');
			$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
			
			if($giorni_in_scadenza == null || $giorni_in_scadenza == ''){
				
				$giorni_in_scadenza = 0;
				
			}
			
		}
	}

	if($giorni_in_scadenza == 0){
		
		$giorni_in_scadenza = $default;
		
	}
	
	$result = $giorni_in_scadenza;
	
	return $result;
}

function calcolaSituazioneFormazioneAzienda($account, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     * @package situazioneFormazione
     * @version 1.0
     */
    
    $q_vecchi = "UPDATE {$table_prefix}_situazformaz SET
                    aggiornato = '0'
                    WHERE azienda = ".$account;
    $adb->query($q_vecchi);
	
	$lista_risorse = getRisorseAzienda($account);
	
	foreach($lista_risorse as $risorsa){
		
		printf("<br />----- Risorsa: ".$risorsa['risorsaid']);
		
		calcolaSituazioneFormazioneRisorsa($risorsa['risorsaid'], $giorni_in_scadenza);
		aggiornaSituazioneFormazioneRisorsaInAnagrafica($risorsa['risorsaid']);
		
	}
	
	$upd = "UPDATE {$table_prefix}_crmentity ent
			INNER JOIN {$table_prefix}_situazformaz sitform ON sitform.situazformazid = ent.crmid
			SET
			ent.deleted = 1
			WHERE sitform.aggiornato != '1' AND sitform.azienda = ".$account;
	$adb->query($upd);

	//Kpro@tom160420181414

	if( file_exists(__DIR__."/../../SDK/src/KpClienteUtils.php") ){

		include_once(__DIR__."/../../SDK/src/KpClienteUtils.php");

		try {

			kpPostElaborazioneCalcolaSituazioneFormazioneAzienda($account, $giorni_in_scadenza);

		}
		catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}

	}

	//Kpro@tom160420181414 end
    
}

function getRisorseAzienda($azienda){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$result = array();
	
	$data_corrente = date("Y-m-d");
	
	$q_risorse = "SELECT 
			mr.risorsa risorsa
			FROM {$table_prefix}_mansionirisorsa mr
			INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = mr.mansionirisorsaid
			INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = mr.risorsa
			WHERE ent.deleted = 0 AND mr.stato_mansione = 'Attiva' 
			AND (cont.data_fine_rap IS NULL OR cont.data_fine_rap = '' OR cont.data_fine_rap > '".$data_corrente."' OR cont.data_fine_rap = '0000-00-00') 
			AND cont.accountid = ".$azienda;
	
	$res_risorse = $adb->query($q_risorse);
	$num_risorse = $adb->num_rows($res_risorse);
	
	for($i = 0; $i < $num_risorse; $i++){	

		$risorsa = $adb->query_result($res_risorse, $i, 'risorsa');
		$risorsa = html_entity_decode(strip_tags($risorsa), ENT_QUOTES, $default_charset);
		
		$result[] = array('risorsaid' => $risorsa);
		
	}
	
	return $result;
	
}

function calcolaSituazioneFormazioneRisorsa($risorsa, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$lista_mansioni_risorsa = getMansioniRisorsa($risorsa);
	
	foreach($lista_mansioni_risorsa as $mansione_risorsa){
		
		printf("<br />------- Mansione-Risorsa: ".$mansione_risorsa['mansionirisorsaid']);
		
		calcolaSituazioneFormazioneMansioneRisorsa($risorsa, $mansione_risorsa['mansionirisorsaid'], $giorni_in_scadenza);
		
	}
      
}

function getMansioniRisorsa($risorsa){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$result = array();
	
	$q_mansioni_risorse = "SELECT 
							manris.mansionirisorsaid mansionirisorsaid 
                            FROM {$table_prefix}_mansionirisorsa manris
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = manris.mansionirisorsaid
                            WHERE ent.deleted = 0 AND manris.stato_mansione = 'Attiva' AND manris.risorsa = ".$risorsa;
    $res_mansioni_risorse = $adb->query($q_mansioni_risorse);
    $num_mansioni_risorse = $adb->num_rows($res_mansioni_risorse);
    
	for($i = 0; $i < $num_mansioni_risorse; $i++){
        
        $mansionirisorsaid = $adb->query_result($res_mansioni_risorse, $i, 'mansionirisorsaid');
        $mansionirisorsaid = html_entity_decode(strip_tags($mansionirisorsaid), ENT_QUOTES, $default_charset);
		
		$result[] = array('mansionirisorsaid' => $mansionirisorsaid);
		
	}
	
	return $result;
	
}

function calcolaSituazioneFormazioneMansioneRisorsa($risorsa, $mansionirisorsaid, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$lista_tipi_corso = getTipiCorsoMansioniRisorsa($mansionirisorsaid);
	
	foreach($lista_tipi_corso as $tipo_corso){
		
		printf("<br />--------- Tipo Corso: ".$tipo_corso['tipocorsoid']);
		
		calcolaSituazioneFormazioneTipoCorso($risorsa, $mansionirisorsaid, $tipo_corso['tipocorsoid'], $giorni_in_scadenza);
		
	}
    
}

function getTipiCorsoMansioniRisorsa($mansionirisorsa){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$result = array();
	
	$q_tipi_corso = "SELECT *FROM 
                    ((SELECT rel1.relcrmid tipo_corso,
                    tc1.aggiornamento_di aggiornamento_di
                    FROM {$table_prefix}_crmentityrel rel1
                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                    INNER JOIN {$table_prefix}_tipicorso tc1 ON tc1.tipicorsoid = rel1.relcrmid
                    WHERE ent1.deleted = 0 AND rel1.crmid = ".$mansionirisorsa." AND rel1.relmodule = 'TipiCorso')
                    UNION
                    (SELECT rel2.crmid tipo_corso,
                    tc2.aggiornamento_di aggiornamento_di
                    FROM {$table_prefix}_crmentityrel rel2
                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                    INNER JOIN {$table_prefix}_tipicorso tc2 ON tc2.tipicorsoid = rel2.crmid
                    WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$mansionirisorsa." AND rel2.module = 'TipiCorso')) AS t
                    ORDER BY t.aggiornamento_di DESC";
    //printf($q_tipi_corso);                

    $res_tipi_corso = $adb->query($q_tipi_corso);
    $num_tipi_corso = $adb->num_rows($res_tipi_corso);
    for($i=0; $i<$num_tipi_corso; $i++){	

        $tipo_corso = $adb->query_result($res_tipi_corso,$i,'tipo_corso');
        $tipo_corso = html_entity_decode(strip_tags($tipo_corso), ENT_QUOTES,$default_charset);
		
		$result[] = array('tipocorsoid' => $tipo_corso);
		
	}
	
	return $result;
	
}

function calcolaSituazioneFormazioneTipoCorso($risorsa,$mansionirisorsaid,$tipo_corso,$giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
    
    $corso_di_aggiornamento = verificaSeAggiornamentoDiAltroTipoCorso($tipo_corso);
    printf(", Corso di aggiornamento: ".$corso_di_aggiornamento);
    
    if($corso_di_aggiornamento == "no"){
		
        calcolaSituazioneTipoCorsoBase($risorsa, $mansionirisorsaid, $tipo_corso, $giorni_in_scadenza, "si");
		
    }
    elseif($corso_di_aggiornamento == "si"){
		
        calcolaSituazioneTipoCorsoAggiornamento($risorsa,$mansionirisorsaid,$tipo_corso,$giorni_in_scadenza, "si");
		
    }
    
}

function verificaSeAggiornamentoDiAltroTipoCorso($tipo_corso){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
    
    $q_aggiornamento_di = "SELECT tc.tipicorsoid tipicorsoid 
                            FROM {$table_prefix}_tipicorso tc
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = tc.tipicorsoid
							WHERE ent.deleted = 0 AND tc.aggiornamento_di = ".$tipo_corso;
	
	/* kpro@tom201220181609 */
	//Serve ad evitare che un corso sia indicato come aggiornamento di se stesso creando un loop
	$q_aggiornamento_di .= " AND tc.tipicorsoid != ".$tipo_corso;
	/* kpro@tom201220181609 end */

    //printf("  %s  ", $q_aggiornamento_di);                         
                          
    $res_aggiornamento_di = $adb->query($q_aggiornamento_di);
    if($adb->num_rows($res_aggiornamento_di)>0){
        
        $aggiornamento_di = "si";
        
    } 
    else{
        
        $aggiornamento_di = "no";
        
    }
    
    return $aggiornamento_di;
    
}

function getDatiTipoCorso($tipo_corso){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$result = "";
	 
	$q_tipo_corso = "SELECT 
                        tipicorso_name,
                        durata_corso,
                        aggiornamento_di,
                        formaz_scaglionata,
                        anni_rinnovo
                        FROM {$table_prefix}_tipicorso
                        WHERE tipicorsoid = ".$tipo_corso;
    $res_tipo_corso = $adb->query($q_tipo_corso);
    if($adb->num_rows($res_tipo_corso)>0){
        
        $tipicorso_name = $adb->query_result($res_tipo_corso,0,'tipicorso_name');
        $tipicorso_name = html_entity_decode(strip_tags($tipicorso_name), ENT_QUOTES,$default_charset);
        
        $durata_corso = $adb->query_result($res_tipo_corso,0,'durata_corso');
        $durata_corso = html_entity_decode(strip_tags($durata_corso), ENT_QUOTES,$default_charset);
        if($durata_corso == null || $durata_corso == ''){
            $durata_corso = 0;
        }
        
        $aggiornato_da = $adb->query_result($res_tipo_corso,0,'aggiornamento_di');
        $aggiornato_da = html_entity_decode(strip_tags($aggiornato_da), ENT_QUOTES,$default_charset);
        if($aggiornato_da == null || $aggiornato_da == ''){
            $aggiornato_da = 0;
		}
		
		/* kpro@tom201220181609 */
		//Serve ad evitare che un corso sia indicato come aggiornamento di se stesso creando un loop
		if( $aggiornato_da != 0 && $aggiornato_da == $tipo_corso ){
			$aggiornato_da = 0;
		}
		/* kpro@tom201220181609 end */
        
        $formaz_scaglionata = $adb->query_result($res_tipo_corso,0,'formaz_scaglionata');
        $formaz_scaglionata = html_entity_decode(strip_tags($formaz_scaglionata), ENT_QUOTES,$default_charset);
        if($formaz_scaglionata == '1'){
            $formaz_scaglionata = "si";
        }
        else{
            $formaz_scaglionata = "no";
        }
        
        $anni_rinnovo = $adb->query_result($res_tipo_corso,0,'anni_rinnovo');
        $anni_rinnovo = html_entity_decode(strip_tags($anni_rinnovo), ENT_QUOTES,$default_charset);
        if($anni_rinnovo == null || $anni_rinnovo == ''){
            $anni_rinnovo = 0;
        }
		
		$result = array('tipicorso_name' => $tipicorso_name,
						'durata_corso' => $durata_corso,
						'aggiornato_da' => $aggiornato_da,
						'formaz_scaglionata' => $formaz_scaglionata,
						'anni_rinnovo' => $anni_rinnovo);

    }
	
	return $result;
	 
}

function calcolaSituazioneTipoCorsoBase($risorsa, $mansionirisorsaid, $tipo_corso, $giorni_in_scadenza, $aggiorna){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$dati_tipo_corso = getDatiTipoCorso($tipo_corso);
        
    printf(", Nome: ".$dati_tipo_corso['tipicorso_name'].", Durata: ".$dati_tipo_corso['durata_corso'].", Scaglionato: ".$dati_tipo_corso['formaz_scaglionata']);
    
    $lista_formazione_eseguita = getFormazioneEseguitaRisorsaTipoCorso($risorsa, $tipo_corso, $dati_tipo_corso['aggiornato_da'], $giorni_in_scadenza);
 
	if(count($lista_formazione_eseguita) == 0){
		
		$stato_formazione = 'Non eseguita';
		$data_formazione = '';
		$validita_formazione = '';
		$durata_formazione = 0;
		$nota_stato = "Nota stato situazione formazione: La formazione NON e' stata eseguita in quanto per tale tipo corso e tale risorsa non risultano partecipazioni a corsi di formazione.";
		
	}
	else{
		
		$stato_formazione = $lista_formazione_eseguita[0]['stato_formazione'];
		$data_formazione = $lista_formazione_eseguita[0]['data_formazione'];
		$validita_formazione = $lista_formazione_eseguita[0]['data_scad_for'];
		$durata_formazione = $lista_formazione_eseguita[0]['tot_ore_effet'];
		$nota_stato = $lista_formazione_eseguita[0]['nota_stato'];
		
	}
	
	printf("<br />----------- Stato Formazione: %s <br />----------- Partecipazione ID: %s <br />----------- Data Formazione: %s <br />----------- Validita Formazione: %s <br />----------- Nota: %s", $stato_formazione, $partecipazioneid, $data_formazione, $durata_formazione, $nota_stato);
            
    if($aggiorna == "si"){
		
		setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, "", "no", "no", $lista_formazione_eseguita);
        
    }
    
}

function setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, $validita_formazione_prec, $formazione_scaglionata, $finestra_antecedente, $lista_formazione_eseguita, $ore_prossimo_rinnovo = 0){
	global $adb, $table_prefix, $current_user, $default_charset;
	
	if($durata_formazione == null || $durata_formazione == ''){
		$durata_formazione = 0;
	}
	
	if($formazione_scaglionata == "si"){
		$formazione_scaglionata = '1';
	}
	else{
		$formazione_scaglionata = '0';
	}
	
	if($finestra_antecedente == "si"){
		$finestra_antecedente = '1';
	}
	else{
		$finestra_antecedente = '0';
	}
	
	$dati_tipo_corso = getDatiTipoCorso($tipo_corso);
	
	if($dati_tipo_corso['durata_corso'] == null || $dati_tipo_corso['durata_corso'] == ''){
		$dati_tipo_corso['durata_corso'] = 0;
	}
	
	$dati_mansione_risorsa = getDatiMansioneRisorsa($mansionirisorsaid);
    
	$situazformazid = 0;

	//kpro@tom110620181038
	
	/*if($formazione_scaglionata == '1'){
		
		$q_verifica = "SELECT situazformazid FROM {$table_prefix}_situazformaz
						INNER JOIN {$table_prefix}_crmentity ON crmid = situazformazid
						WHERE deleted = 0 AND validita_formazione = '".$validita_formazione."' 
						AND tipo_corso = ".$tipo_corso." AND mansione_risorsa = ".$mansionirisorsaid;
		
	}
	else{
		
		$q_verifica = "SELECT situazformazid FROM {$table_prefix}_situazformaz
						INNER JOIN {$table_prefix}_crmentity ON crmid = situazformazid
						WHERE deleted = 0 AND tipo_corso = ".$tipo_corso." AND mansione_risorsa =".$mansionirisorsaid;
						
	}*/

	$q_verifica = "SELECT situazformazid FROM {$table_prefix}_situazformaz
					INNER JOIN {$table_prefix}_crmentity ON crmid = situazformazid
					WHERE deleted = 0 AND tipo_corso = ".$tipo_corso." AND mansione_risorsa =".$mansionirisorsaid;

	//kpro@tom110620181038 end
	
	$res_verifica = $adb->query($q_verifica);
	if($adb->num_rows($res_verifica)>0){
		
		$situazformazid = $adb->query_result($res_verifica,0,'situazformazid');
		$situazformazid = html_entity_decode(strip_tags($situazformazid), ENT_QUOTES,$default_charset);

		$nota_stato = addslashes($nota_stato);
		
		$upd = "UPDATE {$table_prefix}_situazformaz SET
				tipo_corso = ".$tipo_corso.",
				data_formazione = '".$data_formazione."',
				validita_formazione = '".$validita_formazione."',
				azienda = ".$dati_mansione_risorsa['accountid'].",
				stato_formazione = '".$stato_formazione."',
				risorsa = ".$risorsa.",
				mansione = ".$dati_mansione_risorsa['mansione'].",
				mansione_risorsa = ".$mansionirisorsaid.",
				stabilimento = ".$dati_mansione_risorsa['stabilimento'].",
				ore_previste = ".$dati_tipo_corso['durata_corso'].",
				ore_effettuate = ".$durata_formazione.",  
				kp_ore_prossimo_rin = ".$ore_prossimo_rinnovo.",
				data_prec_scadenza = '".$validita_formazione_prec."',
				kp_corso_scaglionat = '".$formazione_scaglionata."',
				kp_finestra_antec = '".$finestra_antecedente."',
				aggiornato = '1',
				description = '".$nota_stato."'
				WHERE situazformazid = ".$situazformazid;
		
		$adb->query($upd);
		
		pulisciRelatedPartecipazioniSituazioneFormazione($situazformazid);
			
		/*//Codice di verifica query
		if($risorsa == 359 && $tipo_corso ==  160){
			printf("<br />".$upd);die;
		}*/
		
	}
	else{
		
		$new_situazione_formazione = CRMEntity::getInstance('SituazFormaz'); 
		$new_situazione_formazione->column_fields['assigned_user_id'] = 1;
		$new_situazione_formazione->column_fields['creator'] = 1;
		if($mansionirisorsaid != "" && $mansionirisorsaid != 0){
			$new_situazione_formazione->column_fields['mansione_risorsa'] = $mansionirisorsaid;
		}
		if($risorsa != "" && $risorsa != 0){
			$new_situazione_formazione->column_fields['risorsa'] = $risorsa;
		}
		if($dati_mansione_risorsa['mansione'] != "" && $dati_mansione_risorsa['mansione'] != 0){
			$new_situazione_formazione->column_fields['mansione'] = $dati_mansione_risorsa['mansione'];
		}
		if($tipo_corso != "" && $tipo_corso != 0){
			$new_situazione_formazione->column_fields['tipo_corso'] = $tipo_corso;
		}
		if($data_formazione != ""){
			$new_situazione_formazione->column_fields['data_formazione'] = $data_formazione;
		}
		if($validita_formazione != ""){
			$new_situazione_formazione->column_fields['validita_formazione'] = $validita_formazione;
		}
		if($stato_formazione != ""){
			$new_situazione_formazione->column_fields['stato_formazione'] = $stato_formazione;
		}
		if($dati_mansione_risorsa['accountid']!= "" && $dati_mansione_risorsa['accountid'] != 0){
			$new_situazione_formazione->column_fields['azienda'] = $dati_mansione_risorsa['accountid'];
		}
		if($dati_mansione_risorsa['stabilimento']!= "" && $dati_mansione_risorsa['stabilimento'] != 0){
			$new_situazione_formazione->column_fields['stabilimento'] = $dati_mansione_risorsa['stabilimento'];
		}
		if($dati_tipo_corso['durata_corso'] != "" && $dati_tipo_corso['durata_corso'] != 0){
			$new_situazione_formazione->column_fields['ore_previste'] = $dati_tipo_corso['durata_corso'];
		}
		if($durata_formazione != "" && $durata_formazione != 0){
			$new_situazione_formazione->column_fields['ore_effettuate'] = $durata_formazione;
		}
		if($ore_prossimo_rinnovo != "" && $ore_prossimo_rinnovo != 0){
			$new_situazione_formazione->column_fields['kp_ore_prossimo_rin'] = $ore_prossimo_rinnovo;
		}
		if($nota_stato != ""){
			$new_situazione_formazione->column_fields['description'] = $nota_stato;
		}
		if($validita_formazione_prec != ""){
			$new_situazione_formazione->column_fields['data_prec_scadenza'] = $validita_formazione_prec;
		}
		if($formazione_scaglionata == '1'){
			$new_situazione_formazione->column_fields['kp_corso_scaglionat'] = $formazione_scaglionata;
		}
		if($finestra_antecedente == '1'){
			$new_situazione_formazione->column_fields['kp_finestra_antec'] = $finestra_antecedente;
		}
		$new_situazione_formazione->column_fields['aggiornato'] = '1';
		$new_situazione_formazione->save('SituazFormaz', $longdesc=true, $offline_update=false, $triggerEvent=false);

		$situazformazid = $new_situazione_formazione->id;	
		
		/*//Codice di verifica query
		if($risorsa == 361 && $tipo_corso ==  17057){
			printf("<br />".$nota_stato);die;
		}*/
		
	}
	
	if(count($lista_formazione_eseguita) > 0 && $situazformazid != "" && $situazformazid != 0){
		
		foreach($lista_formazione_eseguita as $formazione_eseguita){
			
			setRelatedPartecipazioniSituazioneFormazione($situazformazid, $formazione_eseguita['partecipformazid']);
			
		}
		
	}
	
}

function pulisciRelatedPartecipazioniSituazioneFormazione($situazioneformazione){
	global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$delete = "DELETE FROM {$table_prefix}_crmentityrel
				WHERE module = 'SituazFormaz' AND relmodule = 'KpPartecipFormaz' AND crmid = ".$situazioneformazione;
	$adb->query($delete);
	
	$delete2 = "DELETE FROM {$table_prefix}_crmentityrel
				WHERE module = 'KpPartecipFormaz' AND relmodule = 'SituazFormaz' AND relcrmid = ".$situazioneformazione;
	$adb->query($delete2);
	
	/*//Codice di verifica query
	if($situazioneformazione == 16078){
		printf("<br />".$delete);die;
	}*/
	
}

function setRelatedPartecipazioniSituazioneFormazione($situazioneformazione, $partecipazione){
	global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule)
				VALUES (".$situazioneformazione.", 'SituazFormaz', ".$partecipazione.", 'KpPartecipFormaz')";
	$adb->query($insert);
	
}

function getDatiMansioneRisorsa($mansionirisorsa){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$result = "";
	
	$q_dati = "SELECT 
				manris.mansione mansione,
				ris.accountid accountid,
				ris.stabilimento stabilimento
				FROM {$table_prefix}_mansionirisorsa manris
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = manris.mansionirisorsaid
				INNER JOIN {$table_prefix}_contactdetails ris ON ris.contactid = manris.risorsa
				WHERE ent.deleted = 0 AND manris.mansionirisorsaid = ".$mansionirisorsa;
	$res_dati = $adb->query($q_dati);
	if($adb->num_rows($res_dati)>0){
		
		$mansione = $adb->query_result($res_dati,0,'mansione');
		$mansione = html_entity_decode(strip_tags($mansione), ENT_QUOTES,$default_charset);
		
		$accountid = $adb->query_result($res_dati,0,'accountid');
		$accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
		
		$stabilimento = $adb->query_result($res_dati,0,'stabilimento');
		$stabilimento = html_entity_decode(strip_tags($stabilimento), ENT_QUOTES,$default_charset);
		if($stabilimento == null || $stabilimento == ''){
			$stabilimento = 0;
		}
		
		$result = array('mansione' => $mansione,
						'accountid' => $accountid,
						'stabilimento' => $stabilimento);
		
	} 
	
	return $result;
	
}

function getFormazioneEseguitaRisorsaTipoCorso($risorsa, $tipo_corso, $aggiornato_da, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$result = array();

	$data_corrente = date("Y-m-d");
	$data_corrente_inv = date("d-m-Y");
	list($anno, $mese, $giorno) = explode("-", $data_corrente);
	$in_scadenza = date("Y-m-d", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
	$in_scadenza_inv = date("d-m-Y", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
	 
	$q_formazione = "SELECT
						part.kppartecipformazid kppartecipformazid,
						part.kp_nome_partecipaz nome_partecipaz,
						part.kp_risorsa risorsa,
						part.kp_tipo_corso tipo_corso,
						part.kp_formazione formazione,
						part.kp_data_formazione data_formazione,
						part.kp_data_scad_for data_scad_for,
						part.kp_tot_ore_formazio tot_ore_formazio,
						part.kp_tot_ore_effet tot_ore_effet,
						part.kp_stato_partecip stato_partecip,
						part.kp_azienda azienda,
						part.kp_stabilimento stabilimento
						FROM {$table_prefix}_kppartecipformaz part
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = part.kppartecipformazid
						WHERE ent.deleted = 0 AND part.kp_risorsa = ".$risorsa." AND part.kp_tipo_corso = ".$tipo_corso." 
						AND part.kp_stato_partecip IN ('Eseguita', 'Eseguita parzialmente')
						ORDER BY part.kp_data_formazione DESC";
	
	/*//Codice di verifica query
	if($risorsa == 359 && $tipo_corso ==  160){
		printf("<br />".$q_formazione);die;
	}*/
		
	$res_formazione = $adb->query($q_formazione);
    $num_formazione = $adb->num_rows($res_formazione);
    for($i = 0; $i < $num_formazione; $i++){
		
		$partecipformazid = $adb->query_result($res_formazione, $i, 'kppartecipformazid');
        $partecipformazid = html_entity_decode(strip_tags($partecipformazid), ENT_QUOTES, $default_charset);
		
		$nome_partecipaz = $adb->query_result($res_formazione, $i, 'nome_partecipaz');
        $nome_partecipaz = html_entity_decode(strip_tags($nome_partecipaz), ENT_QUOTES, $default_charset);
		
		$data_formazione = $adb->query_result($res_formazione, $i, 'data_formazione');
        $data_formazione = html_entity_decode(strip_tags($data_formazione), ENT_QUOTES, $default_charset);
		
		$data_scad_for = $adb->query_result($res_formazione, $i, 'data_scad_for');
        $data_scad_for = html_entity_decode(strip_tags($data_scad_for), ENT_QUOTES, $default_charset);
		if( $data_scad_for == null ){
			$data_scad_for = "";
		}
		else{
			list($anno_scad, $mese_scad, $giorno_scad) = explode("-", $data_scad_for);
			$data_scad_for_inv = date("d-m-Y", mktime(0, 0, 0, $mese_scad, $giorno_scad, $anno_scad));
			$in_scadenza_reale_inv = date("d-m-Y", mktime(0, 0, 0, $mese_scad, (int)$giorno_scad - $giorni_in_scadenza, $anno_scad));
		}
		
		$tot_ore_formazio = $adb->query_result($res_formazione, $i, 'tot_ore_formazio');
        $tot_ore_formazio = html_entity_decode(strip_tags($tot_ore_formazio), ENT_QUOTES, $default_charset);
		
		$tot_ore_effet = $adb->query_result($res_formazione, $i, 'tot_ore_effet');
        $tot_ore_effet = html_entity_decode(strip_tags($tot_ore_effet), ENT_QUOTES, $default_charset);
		
		$stato_partecip = $adb->query_result($res_formazione, $i, 'stato_partecip');
        $stato_partecip = html_entity_decode(strip_tags($stato_partecip), ENT_QUOTES, $default_charset);
		
		if($aggiornato_da != null && $aggiornato_da != '' && $aggiornato_da != 0){
            $stato_formazione = 'Eseguita';
			$nota_stato = "Nota stato situazione formazione: La formazione e' stata eseguita e il tipo corso non sara' da ripetere in quanto sara' aggiornato da un altro tipo corso.";
        }
        elseif($data_scad_for == '2099-12-31' || $data_scad_for == '2999-12-31' || $data_scad_for == '9999-12-31' || $data_scad_for == ''){
			//$data_scad_for = '2099-12-31';	//kpro@tom06102017
			$data_scad_for = '';	//kpro@tom06102017
            $stato_formazione = 'Valida senza scadenza';
			$nota_stato = "Nota stato situazione formazione: La formazione e' 'Valida senza scadenza' in quanto l'ultimo corso eseguito ha data scadenza pari a '31-12-2099' oppure '31-12-2999'.";
        }
        elseif($data_scad_for > $data_corrente && $data_scad_for <= $in_scadenza){
            $stato_formazione = 'In scadenza';
			$nota_stato = "Nota stato situazione formazione: La formazione e' 'In scadenza' in quanto la data della scadenza dell'ultima formazione eseguita (".$data_scad_for_inv.") risulta compresa tra la data corrente (".$data_corrente_inv.") e la data in cui andra' 'In scadenza'(".$in_scadenza_reale_inv.").";
        }
        elseif($data_scad_for >= $in_scadenza){
            $stato_formazione = 'In corso di validita';
			$nota_stato = "Nota stato situazione formazione: La formazione e' 'In corso di validita' in quanto la data della scadenza dell'ultima formazione eseguita (".$data_scad_for_inv.") risulta maggiore della data ".$in_scadenza_reale_inv." in cui andra' 'In scadenza'.";
        }
        else{
            $stato_formazione = 'Scaduta';
			$nota_stato = "Nota stato situazione formazione: La formazione e' 'Scaduta' in quanto la data della scadenza dell'ultima formazione eseguita (".$data_scad_for_inv.") risulta inferiore alla data odierna (".$data_corrente_inv.").";
        }
		
		$result[] = array('partecipformazid' => $partecipformazid,
							'nome_partecipaz' => $nome_partecipaz,
							'data_formazione' => $data_formazione,
							'data_scad_for' => $data_scad_for,
							'tot_ore_formazio' => $tot_ore_formazio,
							'tot_ore_effet' => $tot_ore_effet,
							'stato_formazione' => $stato_formazione,
							'nota_stato' => $nota_stato);
		
	}
	
	return $result;
	
}

function calcolaSituazioneTipoCorsoAggiornamento($risorsa, $mansionirisorsaid, $tipo_corso, $giorni_in_scadenza, $aggiorna){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
    
    $a_data = '';
    $da_data = '';
    $da_data_prec = "";
    $fino_a_data_prec = "";
    $eseguita_formazione_precedente = "false";
        
    $dati_tipo_corso = getDatiTipoCorso($tipo_corso);
        
    printf(", Nome: ".$dati_tipo_corso['tipicorso_name'].", Durata: ".$dati_tipo_corso['durata_corso'].", Scaglionato: ".$dati_tipo_corso['formaz_scaglionata']);
    
    $dati_situazione_formazione_precedente = getFormazionePrecedente($risorsa, $mansionirisorsaid, $tipo_corso, $giorni_in_scadenza);
	
	if($dati_situazione_formazione_precedente['stato_formazione'] == "Non eseguita"){
		
        $stato_formazione = "Non eseguito corso base";
		$data_formazione = '';
		$validita_formazione = '';
		$durata_formazione = 0;
		$lista_formazione_eseguita = array(); 
		$nota_stato = "Nota stato situazione formazione: La formazione risulta non eseguita in quanto non e' stato eseguito il corso base.";
		
		$eseguita_formazione_precedente = "false";
		
		/*if($aggiorna == "si"){
		
			setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, "", "no", "no", $lista_formazione_eseguita);
			
		}*/
		
    }
	else{
		
		$eseguita_formazione_precedente = "true";
		//Devo quindi verificare lo stato della formazione in base operando in modo diverso a seconda che sia scaglionato o meno
        $data_precedente_scadenza = $dati_situazione_formazione_precedente['validita_formazione'];

		if($dati_tipo_corso['formaz_scaglionata'] == "no"){
            
			//Verifico se ha eseguito la formazione
			$lista_formazione_eseguita = getFormazioneEseguitaRisorsaTipoCorso($risorsa, $tipo_corso, $dati_tipo_corso['aggiornato_da'], $giorni_in_scadenza);
			
			if(count($lista_formazione_eseguita) == 0){
		
				$data_formazione = '';
				$validita_formazione = $dati_situazione_formazione_precedente["validita_formazione"];	//Kpro@tom160420181414
				$durata_formazione = 0;
				
				//Kpro@tom160420181414

				$data_corrente = date("Y-m-d");
				list($anno, $mese, $giorno) = explode("-", $data_corrente);
				$in_scadenza = date("Y-m-d", mktime(0, 0, 0,$mese, (int)$giorno + $giorni_in_scadenza, $anno));

				if( $dati_situazione_formazione_precedente["validita_formazione"] <= $data_corrente ){

					$stato_formazione = 'Non eseguita';
					$nota_stato = "Nota stato situazione formazione: La formazione NON e' stata eseguita in quanto per tale tipo corso e tale risorsa non risultano partecipazioni a corsi di formazione.";

				}
				elseif( $dati_situazione_formazione_precedente["validita_formazione"]  > $data_corrente && $dati_situazione_formazione_precedente["validita_formazione"] <= $in_scadenza ) {

					$stato_formazione = "In scadenza";
					$nota_stato = "Nota stato situazione formazione: La formazione NON e' stata eseguita in quanto per tale tipo corso e tale risorsa non risultano partecipazioni a corsi di formazione. Tuttavia la risorsa ha tempo fino il ".$dati_situazione_formazione_precedente["validita_formazione"]." per eseguire la formazione.";

				}
				else{
					
					$stato_formazione = 'Eseguire entro';
					$nota_stato = "Nota stato situazione formazione: La formazione NON e' stata eseguita in quanto per tale tipo corso e tale risorsa non risultano partecipazioni a corsi di formazione. Tuttavia la risorsa ha tempo fino il ".$dati_situazione_formazione_precedente["validita_formazione"]." per eseguire la formazione.";

				}

				//Kpro@tom160420181414 end

			}
			else{
				
				$stato_formazione = $lista_formazione_eseguita[0]['stato_formazione'];
				$data_formazione = $lista_formazione_eseguita[0]['data_formazione'];
				$validita_formazione = $lista_formazione_eseguita[0]['data_scad_for'];
				$durata_formazione = $lista_formazione_eseguita[0]['tot_ore_effet'];
				$nota_stato = $lista_formazione_eseguita[0]['nota_stato'];
				
			}
			
			printf("<br />----------- Stato Formazione: %s <br />----------- Partecipazione ID: %s <br />----------- Data Formazione: %s <br />----------- Validita Formazione: %s <br />----------- Nota: %s", $stato_formazione, $partecipazioneid, $data_formazione, $validita_formazione, $nota_stato);	
			
			if($aggiorna == "si" && $eseguita_formazione_precedente == "true"){

				setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, $data_precedente_scadenza, "no", "no", $lista_formazione_eseguita);
				
			}
			
		}
		elseif($dati_tipo_corso['formaz_scaglionata'] == "si"){
			
			$data_scadenza_corso_base = $data_precedente_scadenza;
			
			//kpro@tom110620181038
			//calcolaFormazioneScaglionataRisorsaTipoCorso_old($risorsa, $tipo_corso, $mansionirisorsaid, $dati_tipo_corso['aggiornato_da'], $data_scadenza_corso_base, $giorni_in_scadenza, $dati_tipo_corso['durata_corso'], $dati_tipo_corso['anni_rinnovo'], "si");
			calcolaFormazioneScaglionataRisorsaTipoCorso($risorsa, $tipo_corso, $mansionirisorsaid, $giorni_in_scadenza, $data_scadenza_corso_base, $dati_tipo_corso['durata_corso'], $dati_tipo_corso['anni_rinnovo'], "si", true);
			//kpro@tom110620181038 end	

		}
		
	}
    
}  

//kpro@tom110620181038

function calcolaFormazioneScaglionataRisorsaTipoCorso($risorsa, $tipo_corso, $mansionirisorsaid, $giorni_in_scadenza, $data_scadenza_corso_base, $durata_corso, $anni_rinnovo, $aggiorna, $riallinea){
	global $adb, $table_prefix, $current_user, $default_charset;

	/* kpro@tom11062018 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2018, Kpro Consulting Srl
     */

	require_once('modules/SproCore/KpPartecipFormaz/ClassKpPartecipFormazKp.php');

	$data_corrente = date("Y-m-d");
	$stato_formazione = 'Eseguire entro';
	$data_formazione = "";
	$validita_formazione = $data_scadenza_corso_base;
	$durata_formazione = 0;
	$lista_formazione_eseguita = array();
	$partecipazioneid = 0;
	$validita_formazione_prec = $data_scadenza_corso_base;
	$ore_prossimo_rinnovo = $durata_corso;

	if( $riallinea ){

		KpPartecipFormazKp::riallineamentoFormazioneScaglionata($tipo_corso, $risorsa);

	}
	
	$ultima_formaz_completata = KpPartecipFormazKp::getUltimaFormazioneScaglionata($tipo_corso, $risorsa);
	//print_r($ultima_formaz_completata);die;

	if( $ultima_formaz_completata['esiste'] ){

		$stato_formazione = 'Eseguita';
		$durata_formazione = $durata_corso;

		$data_formazione = $ultima_formaz_completata['data_formazione'];
		$partecipazioneid = $ultima_formaz_completata['id'];

		$lista_formazione_eseguita[] = array("partecipformazid" => $partecipazioneid);

		$data_scadenza = new DateTime($data_formazione);
		
		if($anni_rinnovo == null || $anni_rinnovo == ""){
			$anni_rinnovo = 0;
		}

		$incremento = new DateInterval('P'.$anni_rinnovo.'Y');

		$data_scadenza = $data_scadenza->add($incremento);

		$validita_formazione = $data_scadenza->format('Y-m-d');

		$validita_formazione_inv = $data_scadenza->format('d-m-Y');	
		
		$formaz_precedente = KpPartecipFormazKp::getUltimaFormazioneScaglionata($tipo_corso, $risorsa, $data_formazione, array($partecipazioneid) );

		if( $formaz_precedente['esiste'] ){

			$data_formazione_prec = $formaz_precedente['data_formazione'];

			$data_scadenza_prec = new DateTime($data_formazione_prec);

			$data_scadenza_prec = $data_scadenza_prec->add($incremento);

			$validita_formazione_prec = $data_scadenza_prec->format('Y-m-d');

			$validita_formazione_prec_inv = $data_scadenza_prec->format('d-m-Y');	

			$lista_formazione_passata = KpPartecipFormazKp::getListaFormazioneTipoCorso($tipo_corso, $risorsa, $data_formazione_prec, $data_formazione, array($partecipazioneid, $formaz_precedente['id']) );

			foreach( $lista_formazione_passata as $formazione_passata ){

				$lista_formazione_eseguita[] = array("partecipformazid" => $formazione_passata["id"]);

			}

		}
		else{
			//Se entro qui significa che è la prima volta che effettuo l'aggiornamento e quindi la data di scadenza precedente è quella del corso base;
			//Voglio comunque ottenere la lista di partecipazioni fatte fino ad ora.

			$lista_formazione_passata = KpPartecipFormazKp::getListaFormazioneTipoCorso($tipo_corso, $risorsa, "", $data_formazione, array($partecipazioneid) );

			//print_r($lista_formazione_passata); die;
			foreach( $lista_formazione_passata as $formazione_passata ){

				$lista_formazione_eseguita[] = array("partecipformazid" => $formazione_passata["id"]);

			}

		}

		//Verifico se ho fatto delle ore che però non chiudono ancora uno scaglione e quindi calcolo quante ore mancano alla prossima chiusura
		$lista_formazione_futura = KpPartecipFormazKp::getListaFormazioneTipoCorso($tipo_corso, $risorsa, $data_formazione, "", array($partecipazioneid) );
		$durata_formazione_futura = 0;

		//print_r($lista_formazione_passata); die;
		foreach( $lista_formazione_futura as $formazione_futura ){

			$durata_formazione_futura += $formazione_futura["ore_effettuate"];

		}

		$ore_prossimo_rinnovo = $durata_corso - $durata_formazione_futura;

	}
	else{
		//Se entro qui significa che non ho alcuna formazione scaglionata ultimata
		$stato_formazione = 'Eseguire entro';
		$validita_formazione = $data_scadenza_corso_base;

		$lista_formazione_passata = KpPartecipFormazKp::getListaFormazioneTipoCorso($tipo_corso, $risorsa);

		//print_r($lista_formazione_passata); die;
		foreach( $lista_formazione_passata as $formazione_passata ){

			$lista_formazione_eseguita[] = array("partecipformazid" => $formazione_passata["id"]);

			$durata_formazione += $formazione_passata["ore_effettuate"];

		}

		$ore_prossimo_rinnovo = $durata_corso - $durata_formazione;

	}

	if( $ore_prossimo_rinnovo < 0 ){
		$ore_prossimo_rinnovo = 0;
	}

	$decremento = new DateInterval('P'.$giorni_in_scadenza.'D');

	if( $validita_formazione == "" ){
		$validita_formazione = $data_corrente;
	}

	$data_scadenza = new DateTime($validita_formazione);

	$in_scadenza = $data_scadenza->sub($decremento);

	$in_scadenza_format = $in_scadenza->format('Y-m-d');

	if( $validita_formazione <= $data_corrente ){
		//Se ho superato la data di validita ma avevo completato il corso scaglionato la formazione diventa 'Scaduta'; se invece non avevo
		//completato il corso scaglionato la formazione diventa 'Non eseguita'

		if( $stato_formazione == 'Eseguita' ){

			$stato_formazione = 'Scaduta';

			$nota_stato = "Nota stato situazione formazione: La formazione e' in stato 'Scaduta' in quanto tale tipo corso per tale risorsa non è stato rinnovato entro la data di scadenza ".$validita_formazione_inv.".";
			$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dall'ultima formazione completata sommando gli anni di rinnovo del tipo corso.";

		}
		elseif( $stato_formazione == 'Eseguire entro' ){

			$stato_formazione = 'Non eseguita';

			$nota_stato = "Nota stato situazione formazione: La formazione e' in stato 'Non eseguita' in quanto non sono state eseguite tutte le ore richieste (".$durata_corso.") per tale tipo e tale risorsa entro la data di scadenza ".$validita_formazione_inv.".";
			$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dall'ultima formazione completata sommando gli anni di rinnovo del tipo corso.";

		}

	}
	elseif( $validita_formazione > $data_corrente && $in_scadenza_format <= $data_corrente ){

		$stato_formazione = "In scadenza";

		$nota_stato .= "Nota date situazione formazione scaglionata: La data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dall'ultima formazione completata sommando gli anni di rinnovo del tipo corso.";
			
	}
	else{

		if( $stato_formazione == 'Eseguita' ){

			$stato_formazione = 'In corso di validita';

			$nota_stato = "Nota stato situazione formazione: La formazione e' in stato 'In corso di validità' in quanto tutte le ore richieste (".$durata_corso.") per tale tipo corso e tale risorsa sono state effettuate e scadranno in data ".$validita_formazione_inv.".";
			$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dall'ultima formazione completata sommando gli anni di rinnovo del tipo corso.";

		}
		else{

			$nota_stato = "Nota stato situazione formazione: La formazione e' in stato 'Eseguire entro' in quanto non sono state eseguite tutte le ore richieste (".$durata_corso.") per tale tipo corso tuttavia la data di scadenza non è ancora giunta.";
			$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla formazione base.";

		}

	}

	printf("<br />----------- Stato Formazione: %s <br />----------- Partecipazione ID: %s <br />----------- Data Formazione: %s <br />----------- Validita Formazione: %s <br />----------- Nota: %s", $stato_formazione, $partecipazioneid, $data_formazione, $validita_formazione, $nota_stato);

	if( $aggiorna == "si" ){
		
		setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, $validita_formazione_prec, "si", "no", $lista_formazione_eseguita, $ore_prossimo_rinnovo);
	
	}

}

//kpro@tom110620181038 end

function calcolaFormazioneScaglionataRisorsaTipoCorso_old($risorsa, $tipo_corso, $mansionirisorsaid, $aggiornato_da, $data_scadenza_corso_base, $giorni_in_scadenza, $durata_corso, $anni_rinnovo, $aggiorna){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$data_corrente = date("Y-m-d");
	$stato_formazione = 'Eseguire entro';	//Kpro@tom160420181414
	$data_formazione = "";
	$validita_formazione = "";
	$durata_formazione = 0;
	$da_data = '';
    $fino_a_data = $data_scadenza_corso_base;
	$fino_a_data_prec = "";
	
	while($fino_a_data < $data_corrente){
		
		if($da_data == ''){
			
			$da_data = $fino_a_data;
			
		}else{
			
			list($anno, $mese, $giorno) = explode("-", $da_data);
			$da_data = date("Y-m-d", mktime(0, 0, 0, $mese, $giorno, (int)$anno + $anni_rinnovo));
			
		}
		
		$fino_a_data_prec = $fino_a_data;
		
		list($anno, $mese, $giorno) = explode("-", $da_data);
        $fino_a_data = date("Y-m-d", mktime(0, 0, 0, $mese, $giorno, (int)$anno + $anni_rinnovo));
		
	}
	
	//Codice di controllo
	/*if($risorsa == 362 && $tipo_corso == 22817){
		printf("<br />Data scaglione precedente: %s", $data_scadenza_corso_base); die;
	}*/

	if($fino_a_data_prec != ""){
		
		list($anno, $mese, $giorno) = explode("-", $fino_a_data_prec);
        $da_data_prec = date("Y-m-d", mktime(0, 0, 0, $mese, $giorno, (int)$anno - $anni_rinnovo));
		
		$da_data_inv = date("d-m-Y", mktime(0, 0, 0, $mese, $giorno, (int)$anno - $anni_rinnovo));
		
		$da_data_prec_inv = date("d-m-Y", mktime(0, 0, 0, $mese, $giorno, $anno));
		
		$lista_formazione_eseguita = getFormazioneScaglionataEseguitaRisorsaTipoCorso($risorsa, $tipo_corso, $giorni_in_scadenza, $da_data_prec, $fino_a_data_prec, $durata_corso, "si");
		
		if(count($lista_formazione_eseguita) == 0){

			/* kpro@tom160420181414 */

			$data_corrente = date("Y-m-d");
			list($anno, $mese, $giorno) = explode("-", $data_corrente);
			$in_scadenza = date("Y-m-d", mktime(0, 0, 0,$mese, (int)$giorno + $giorni_in_scadenza, $anno));

			if( $fino_a_data_prec <= $data_corrente ){
		
				$stato_formazione = 'Non eseguita';

			}
			elseif( $fino_a_data_prec > $data_corrente && $fino_a_data_prec <= $in_scadenza ){

				$stato_formazione = "In scadenza";

			}
			else{

				$stato_formazione = 'Eseguire entro';

			}

			/* kpro@tom160420181414 end */

			$data_formazione = $da_data_prec;
			$validita_formazione = $fino_a_data_prec;
			$durata_formazione = 0;
			$validita_formazione_prec = $da_data_prec;
			$nota_stato = "Nota stato situazione formazione: La formazione NON e' stata eseguita in quanto per tale tipo corso e tale risorsa non risultano partecipazioni a corsi di formazione all'interno della finestra temporale in esame (".$da_data_inv." - ".$da_data_prec_inv.").";
			$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla data di scadenza del corso base (".$data_scadenza_corso_base.") sommando gli anni di rinnovo del tipo corso (".$anni_rinnovo.").";
			
		}
		else{
			
			$stato_formazione = $lista_formazione_eseguita[0]['stato_formazione'];
			$data_formazione = $da_data_prec;
			$validita_formazione = $fino_a_data_prec;
			$validita_formazione_prec = $da_data_prec;
			$durata_formazione = $lista_formazione_eseguita[0]['tot_ore_effettuate'];
			$nota_stato = $lista_formazione_eseguita[0]['nota_stato'];
			
		}
		
		if($stato_formazione == "Eseguita"){
			
			$eseguito_scaglione_precedente = "si";
			
		}
		else{
			
			$eseguito_scaglione_precedente = "no";
			
		}
		
		printf("<br />----------- Stato Formazione: %s <br />----------- Partecipazione ID: %s <br />----------- Data Formazione: %s <br />----------- Validita Formazione: %s <br />----------- Nota: %s", $stato_formazione, $partecipazioneid, $data_formazione, $validita_formazione, $nota_stato);
		
		if($aggiorna == "si"){
			
			setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, $validita_formazione_prec, "si", "si", $lista_formazione_eseguita);
			
		}
		
	}
	else{
		
		$eseguito_scaglione_precedente = "si";
		
	}
	
	/*if($risorsa == 362 && $tipo_corso == 22817){
		printf("<br />Eseguito scaglione precedente: %s", $eseguito_scaglione_precedente); die;
	}*/
	
	if($da_data == ""){
		
		//Se il campo $da_data è vuoto significa che non esiste uno scaglione temporale antecedente a oggi quindi devo considerare
		//come scadenza la scadenza del corso base
		$fino_a_data = $data_scadenza_corso_base;
		
	}
	else{
		
		list($anno, $mese, $giorno) = explode("-", $da_data);
		
		$da_data_inv = date("d-m-Y", mktime(0, 0, 0, $mese, $giorno, $anno));
			
		$fino_a_data = date("Y-m-d", mktime(0, 0, 0, $mese, $giorno, (int)$anno + $anni_rinnovo));
		
		$fino_a_data_inv = date("d-m-Y", mktime(0, 0, 0, $mese, $giorno, (int)$anno + $anni_rinnovo));
		
	}
	
	/*if($risorsa == 362 && $tipo_corso == 22817){
		printf("<br />Calcola formazione scaglionata da data: %s, a data: %s", $da_data, $fino_a_data); die;
	}*/
	
	$lista_formazione_eseguita = getFormazioneScaglionataEseguitaRisorsaTipoCorso($risorsa, $tipo_corso, $giorni_in_scadenza, $da_data, $fino_a_data, $durata_corso, $eseguito_scaglione_precedente);
	
	if(count($lista_formazione_eseguita) == 0){
		
		if($eseguito_scaglione_precedente != "si"){
			
			$stato_formazione = "Non eseguita formazione precedente";
			$nota_stato = "Nota stato situazione formazione: Non risulta ultimata la formazione dello scaglione temporale precedente a quello in esame (".$da_data_inv." - ".$fino_a_data_inv.").";
	
		}
		else{

			/* kpro@tom160420181414 */

			$data_corrente = date("Y-m-d");
			list($anno, $mese, $giorno) = explode("-", $data_corrente);
			$in_scadenza = date("Y-m-d", mktime(0, 0, 0,$mese, (int)$giorno + $giorni_in_scadenza, $anno));
			
			if( $fino_a_data <= date("Y-m-d") ){

				$stato_formazione = 'Non eseguita';

			}
			elseif( $fino_a_data > $data_corrente && $fino_a_data <= $in_scadenza ){

				$stato_formazione = "In scadenza";

			}
			else{

				$stato_formazione = 'Eseguire entro';

			}

			/* kpro@tom160420181414 end */

			$nota_stato = "Nota stato situazione formazione: La formazione NON e' stata eseguita in quanto per tale tipo corso e tale risorsa non risultano partecipazioni a corsi di formazione all'interno della finestra temporale in esame (".$da_data_inv." - ".$fino_a_data_inv.").";

		}
		$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla data di scadenza del corso base (".$data_scadenza_corso_base.") sommando gli anni di rinnovo del tipo corso (".$anni_rinnovo.").";
		$data_formazione = $da_data;
		$validita_formazione = $fino_a_data;
		$durata_formazione = 0;
		$validita_formazione_prec = $da_data;
		
	}
	else{
		
		$stato_formazione = $lista_formazione_eseguita[0]['stato_formazione'];
		$data_formazione = $da_data;
		$validita_formazione = $fino_a_data;
		$validita_formazione_prec = $da_data;
		$durata_formazione = $lista_formazione_eseguita[0]['tot_ore_effettuate'];
		$nota_stato = $lista_formazione_eseguita[0]['nota_stato'];
		
	}
	
	printf("<br />----------- Stato Formazione: %s <br />----------- Partecipazione ID: %s <br />----------- Data Formazione: %s <br />----------- Validita Formazione: %s <br />----------- Nota: %s", $stato_formazione, $partecipazioneid, $data_formazione, $validita_formazione, $nota_stato);
		
	if($aggiorna == "si"){
		
		setSituazioneFormazione($tipo_corso, $risorsa, $mansionirisorsaid, $durata_formazione, $data_formazione, $validita_formazione, $stato_formazione, $nota_stato, $validita_formazione_prec, "si", "no", $lista_formazione_eseguita);
		
	}
	 
}  

function getFormazioneScaglionataEseguitaRisorsaTipoCorso($risorsa, $tipo_corso, $giorni_in_scadenza, $da_data, $a_data, $durata_corso, $eseguita_precedente){
    global $adb, $table_prefix, $current_user, $default_charset; 
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$result = array();
	
	$tot_ore_effettuate = 0;
	
	list($anno, $mese, $giorno) = explode("-", $da_data);
    $da_data_inv = date("d-m-Y", mktime(0, 0, 0, $mese, $giorno, $anno));
	
	list($anno, $mese, $giorno) = explode("-", $a_data);
	$a_data_inv = date("d-m-Y", mktime(0, 0, 0, $mese, $giorno, $anno));
	
	$data_corrente = date("Y-m-d");
	list($anno, $mese, $giorno) = explode("-", $data_corrente);
	$in_scadenza = date("Y-m-d", mktime(0, 0, 0,$mese, (int)$giorno + $giorni_in_scadenza, $anno));

	$q_tot_ore_formazione = "SELECT 
							COALESCE(SUM(part.kp_tot_ore_effet), 0) tot_ore_effet
							FROM {$table_prefix}_kppartecipformaz part
							INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = part.kppartecipformazid
							WHERE ent.deleted = 0 AND part.kp_stato_partecip IN ('Eseguita', 'Eseguita parzialmente') AND part.kp_risorsa = ".$risorsa." AND part.kp_tipo_corso = ".$tipo_corso."
							AND part.kp_data_formazione > '".$da_data."' AND part.kp_data_formazione <= '".$a_data."'
							ORDER BY part.kp_data_formazione DESC";
	
	/*//Codice di verifica
	if($risorsa == 363 && $tipo_corso == 17058 && $a_data == '2020-12-28'){
		printf("<br />".$q_tot_ore_formazione); die;
	}*/
	
	$res_tot_ore_formazione = $adb->query($q_tot_ore_formazione);
	
    if($adb->num_rows($res_tot_ore_formazione) > 0){	

        $tot_ore_effettuate = $adb->query_result($res_tot_ore_formazione, 0, 'tot_ore_effet');
        $tot_ore_effettuate = html_entity_decode(strip_tags($tot_ore_effettuate), ENT_QUOTES, $default_charset);
		
	}
	
	if($eseguita_precedente != "si"){
		
		$stato_formazione = "Non eseguita formazione precedente";
		$nota_stato = "Nota stato situazione formazione: Non risulta ultimata la formazione dello scaglio temporale precedente a quello in esame (".$da_data_inv." - ".$a_data_inv.").";
		$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla precedente scadenza (".$da_data_inv.") sommando gli anni di rinnovo del tipo corso.";
		
	}
	elseif($tot_ore_effettuate >= $durata_corso){
		
		$stato_formazione = "Eseguita";
		$nota_stato = "Nota stato situazione formazione: La formazione e' stata eseguita in quanto tutte le ore richieste (".$durata_corso.") per tale tipo corso e tale risorsa sono state effettuate all'interno della finestra temporale in esame (".$da_data_inv." - ".$a_data_inv.").";
		$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla precedente scadenza (".$da_data_inv.") sommando gli anni di rinnovo del tipo corso.";
			 
	}
	elseif($a_data > $data_corrente && $a_data <= $in_scadenza){
		
		$stato_formazione = "In scadenza";
		$nota_stato = "Nota stato situazione formazione: La formazione ha stato 'In scadenza' in quanto sono state eseguite solo ".$tot_ore_effettuate." delle ore richieste (".$durata_corso.") per tale tipo corso e tale risorsa all'interno della finestra temporale in esame (".$da_data_inv." - ".$a_data_inv.").";
		$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla precedente scadenza (".$da_data_inv.") sommando gli anni di rinnovo del tipo corso.";
			 
	}
	elseif($a_data > $in_scadenza){
		
		$stato_formazione = "In corso di validita";
		$nota_stato = "Nota stato situazione formazione: La formazione ha stato 'In corso di validita' in quanto sono state eseguite solo ".$tot_ore_effettuate." delle ore richieste (".$durata_corso.") per tale tipo corso e tale risorsa all'interno della finestra temporale in esame (".$da_data_inv." - ".$a_data_inv.").";
		$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla precedente scadenza (".$da_data_inv.") sommando gli anni di rinnovo del tipo corso.";
			 
	}
	elseif($a_data <= $data_corrente){
		
		$stato_formazione = "Non eseguita";
		$nota_stato = "Nota stato situazione formazione: La formazione ha stato 'Non eseguita' in quanto sono state eseguite solo ".$tot_ore_effettuate." delle ore richieste (".$durata_corso.") per tale tipo corso e tale risorsa all'interno della finestra temporale in esame (".$da_data_inv." - ".$a_data_inv.").";
		$nota_stato .= "<br />Nota date situazione formazione scaglionata: La data della formazione e la data di scadenza delle formazioni scaglionate non sono dettate dalle partecipazioni; sono bensi' calcolate a partire dalla precedente scadenza (".$da_data_inv.") sommando gli anni di rinnovo del tipo corso.";
		
	}
	else{
		
		$stato_formazione = "Non eseguita";
		$nota_stato = "";
		
	}
	
	/*//Codice di verifica
	if($risorsa == 363 && $tipo_corso == 17058 && $a_data == '2020-12-28'){
		printf("<br />Stato Formazione: ".$stato_formazione); die;
	}*/
	
	$q_formazione = "SELECT 
						part.kppartecipformazid partecipformazid,
						part.kp_nome_partecipaz nome_partecipaz,
						part.kp_formazione formazione,
						part.kp_data_formazione data_formazione,
						part.kp_data_scad_for data_scad_for,
						part.kp_tot_ore_formazio tot_ore_formazio,
						part.kp_tot_ore_effet tot_ore_effet,
						part.kp_stato_partecip stato_partecip
						FROM {$table_prefix}_kppartecipformaz part
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = part.kppartecipformazid
						WHERE ent.deleted = 0 AND part.kp_stato_partecip IN ('Eseguita', 'Eseguita parzialmente') AND part.kp_risorsa = ".$risorsa." AND part.kp_tipo_corso = ".$tipo_corso."
						AND part.kp_data_formazione > '".$da_data."' AND part.kp_data_formazione <= '".$a_data."'
						ORDER BY part.kp_data_formazione DESC";
	
	$res_formazione = $adb->query($q_formazione);
	$num_formazione = $adb->num_rows($res_formazione);
	for($i = 0; $i < $num_formazione; $i++){
		
		$partecipformazid = $adb->query_result($res_formazione, $i, 'partecipformazid');
        $partecipformazid = html_entity_decode(strip_tags($partecipformazid), ENT_QUOTES, $default_charset);
		
		$nome_partecipaz = $adb->query_result($res_formazione, $i, 'nome_partecipaz');
        $nome_partecipaz = html_entity_decode(strip_tags($nome_partecipaz), ENT_QUOTES, $default_charset);
		
		$formazione = $adb->query_result($res_formazione, $i, 'formazione');
        $formazione = html_entity_decode(strip_tags($formazione), ENT_QUOTES, $default_charset);
		
		$data_formazione = $adb->query_result($res_formazione, $i, 'data_formazione');
        $data_formazione = html_entity_decode(strip_tags($data_formazione), ENT_QUOTES, $default_charset);
		
		$data_scad_for = $adb->query_result($res_formazione, $i, 'data_scad_for');
        $data_scad_for = html_entity_decode(strip_tags($data_scad_for), ENT_QUOTES, $default_charset);
		
		$tot_ore_formazione_corso = $adb->query_result($res_formazione, $i, 'tot_ore_formazio');
        $tot_ore_formazione_corso = html_entity_decode(strip_tags($tot_ore_formazione_corso), ENT_QUOTES, $default_charset);
		
		$tot_ore_effettuate_corso = $adb->query_result($res_formazione, $i, 'tot_ore_effet');
        $tot_ore_effettuate_corso = html_entity_decode(strip_tags($tot_ore_effettuate_corso), ENT_QUOTES, $default_charset);
		
		$result[] = array('partecipformazid' => $partecipformazid,
							'nome_partecipaz' => $nome_partecipaz,
							'formazione' => $formazione,
							'data_formazione' => $data_formazione,
							'data_scad_for' => $data_scad_for,
							'tot_ore_formazione_corso' => $tot_ore_formazione_corso,
							'tot_ore_effettuate_corso' => $tot_ore_effettuate_corso,
							'tot_ore_effettuate' => $tot_ore_effettuate,
							'stato_formazione' => $stato_formazione,
							'nota_stato' => $nota_stato);
		
	}
	
	return $result;
	
}

function getFormazionePrecedente($risorsa, $mansionirisorsaid, $tipo_corso_aggiornamento, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset; 
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	 
	$result = "";
	
	$dati_tipo_corso_precedente = getTipoCorsoPrecedente($mansionirisorsaid, $tipo_corso_aggiornamento);
	
	printf("<br />----------- Stato Formazione Precedente: Tipo Corso: %s", $dati_tipo_corso_precedente);
	
	if($dati_tipo_corso_precedente != "" && $dati_tipo_corso_precedente != 0){
		
		$dati_situazione_formazione_prec = getSituazioneFormazionePrecedente($risorsa, $mansionirisorsaid, $dati_tipo_corso_precedente, $giorni_in_scadenza);
		
		$validita_formazione = $dati_situazione_formazione_prec['validita_formazione'];
		
		$data_formazione = $dati_situazione_formazione_prec['data_formazione'];
		
		$stato_formazione = $dati_situazione_formazione_prec['stato_formazione'];
		
		$ore_previste = $dati_situazione_formazione_prec['ore_previste'];
		
		$ore_effettuate = $dati_situazione_formazione_prec['ore_effettuate'];
		
	}
	else{
		
		$validita_formazione = "";
		
		$data_formazione = "";
		
		$stato_formazione = "Non eseguita";
		
		$ore_previste = 0;
		
		$ore_effettuate = 0;
						
	}
	
	$result = array('validita_formazione' => $validita_formazione,
					'data_formazione' => $data_formazione,
					'stato_formazione' => $stato_formazione,
					'ore_previste' => $ore_previste,
					'ore_effettuate' => $ore_effettuate);
	
	printf(" Validita Formazione: %s, Data Formazione: %s, Stato Formazione: %s", $validita_formazione, $data_formazione, $stato_formazione);
	
	return $result;
	
}

function getTipoCorsoPrecedente($mansionirisorsa, $tipo_corso_aggiornamento){
	global $adb, $table_prefix, $current_user, $default_charset; 
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
	$result = 0;
	
	$q_tipi_corso = "SELECT *FROM 
                    ((SELECT rel1.relcrmid tipo_corso,
                    tc1.aggiornamento_di aggiornamento_di
                    FROM {$table_prefix}_crmentityrel rel1
                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = rel1.relcrmid
                    INNER JOIN {$table_prefix}_tipicorso tc1 ON tc1.tipicorsoid = rel1.relcrmid
                    WHERE ent1.deleted = 0 AND rel1.crmid = ".$mansionirisorsa." AND rel1.relmodule = 'TipiCorso' AND tc1.aggiornamento_di = ".$tipo_corso_aggiornamento.")
                    UNION
                    (SELECT rel2.crmid tipo_corso,
                    tc2.aggiornamento_di aggiornamento_di
                    FROM {$table_prefix}_crmentityrel rel2
                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = rel2.crmid
                    INNER JOIN {$table_prefix}_tipicorso tc2 ON tc2.tipicorsoid = rel2.crmid
                    WHERE ent2.deleted = 0 AND rel2.relcrmid = ".$mansionirisorsa." AND rel2.module = 'TipiCorso' AND tc2.aggiornamento_di = ".$tipo_corso_aggiornamento.")) AS t
                    ORDER BY t.aggiornamento_di DESC";
    //printf($q_tipi_corso);
                    
    $res_tipi_corso = $adb->query($q_tipi_corso);
	
    if($adb->num_rows($res_tipi_corso)>0){	

        $tipo_corso = $adb->query_result($res_tipi_corso, 0, 'tipo_corso');
        $tipo_corso = html_entity_decode(strip_tags($tipo_corso), ENT_QUOTES, $default_charset);
		
		/* kpro@tom201220181609 */
		//Serve ad evitare che un corso sia indicato come aggiornamento di se stesso creando un loop
		if( $tipo_corso != $tipo_corso_aggiornamento ){
			$result = $tipo_corso;
		}
		/* kpro@tom201220181609 end */
		
	}
	
	return $result;
	
}

function getSituazioneFormazionePrecedente($risorsa, $mansionirisorsa, $tipo_corso, $giorni_in_scadenza){
	global $adb, $table_prefix, $current_user, $default_charset; 
	
	/* kpro@tom27122016 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     */
	
    $result = "";	
	
	$q_verifica_siturazione_prec = "SELECT 
									sitform.validita_formazione validita_formazione,
									sitform.data_formazione data_formazione,
									sitform.stato_formazione stato_formazione,
									sitform.ore_previste ore_previste,
									sitform.ore_effettuate ore_effettuate
									FROM {$table_prefix}_situazformaz sitform
									INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sitform.situazformazid
									WHERE ent.deleted = 0 AND sitform.aggiornato = '1' AND sitform.mansione_risorsa = ".$mansionirisorsa." AND tipo_corso = ".$tipo_corso;
	//printf($q_verifica_siturazione_prec);
									
	$res_verifica_siturazione_prec = $adb->query($q_verifica_siturazione_prec);
	if($adb->num_rows($res_verifica_siturazione_prec)==0){
		
		calcolaSituazioneFormazioneTipoCorso($risorsa, $mansionirisorsa, $tipo_corso, $giorni_in_scadenza);
		
	}
	
	$res_verifica_siturazione_prec = $adb->query($q_verifica_siturazione_prec);
	if($adb->num_rows($res_verifica_siturazione_prec)>0){
		
		$validita_formazione = $adb->query_result($res_verifica_siturazione_prec,0,'validita_formazione');
		$validita_formazione = html_entity_decode(strip_tags($validita_formazione), ENT_QUOTES,$default_charset);
		if($validita_formazione == null){
			$validita_formazione = '';
		}
		
		$data_formazione = $adb->query_result($res_verifica_siturazione_prec,0,'data_formazione');
		$data_formazione = html_entity_decode(strip_tags($data_formazione), ENT_QUOTES,$default_charset);
		if($data_formazione == null){
			$data_formazione = '';
		}
		
		$stato_formazione = $adb->query_result($res_verifica_siturazione_prec,0,'stato_formazione');
		$stato_formazione = html_entity_decode(strip_tags($stato_formazione), ENT_QUOTES,$default_charset);
		
		$ore_previste = $adb->query_result($res_verifica_siturazione_prec,0,'ore_previste');
		$ore_previste = html_entity_decode(strip_tags($ore_previste), ENT_QUOTES,$default_charset);
		if($ore_previste == null || $ore_previste == ''){
			$ore_previste = 0;
		}
		
		$ore_effettuate = $adb->query_result($res_verifica_siturazione_prec,0,'ore_effettuate');
		$ore_effettuate = html_entity_decode(strip_tags($ore_effettuate), ENT_QUOTES,$default_charset);
		if($ore_effettuate == null || $ore_effettuate == ''){
			$ore_effettuate = 0;
		}
		
		$result = array('validita_formazione' => $validita_formazione,
						'data_formazione' => $data_formazione,
						'stato_formazione' => $stato_formazione,
						'ore_previste' => $ore_previste,
						'ore_effettuate' => $ore_effettuate);
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiStandard(){
	global $adb, $table_prefix, $current_user, $default_charset;

	$id_statici = getConfigurazioniIdStatici();
	$id_statico = $id_statici["Programmi Custom - Gestione Avvisi - Giorni per In Scadenza standard"];
	if( $id_statico["valore"] == "" && $id_statico["valore"] == 0){
		$giorni_in_scadenza_default = 0;
	}
	else{
		$giorni_in_scadenza_default = $id_statico["valore"];
	}
    
    $q_documento = "SELECT note.notesid
				FROM {$table_prefix}_notes note
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = note.notesid
				WHERE ent.deleted = 0 AND note.notesid NOT IN (
					SELECT sitdoc.kp_documento
					FROM {$table_prefix}_kpsituazionedocumenti sitdoc
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sitdoc.kpsituazionedocumentiid
					WHERE ent.deleted = 0 AND sitdoc.kp_stato_sit_doc <> 'Non eseguito'
					GROUP BY sitdoc.kp_documento
				)
				AND note.notesid NOT IN (
					SELECT sitdoc.kp_documento
					FROM {$table_prefix}_kpsituazionedocfornit sitdoc
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sitdoc.kpsituazionedocfornitid
					WHERE ent.deleted = 0 AND sitdoc.kp_stato_sit_doc_f <> 'Non eseguito'
					GROUP BY sitdoc.kp_documento
				)";

    $res_documento = $adb->query($q_documento);
    $num_documento = $adb->num_rows($res_documento);

    for($i=0; $i<$num_documento; $i++){
        
        $notesid = $adb->query_result($res_documento,$i,'notesid');
        $notesid = html_entity_decode(strip_tags($notesid), ENT_QUOTES,$default_charset);
        
        calcolaSituazioneDocumentoStandard($notesid, $giorni_in_scadenza_default);
        calcolaAziendeRelazionateAlDocumento($notesid);
        calcolaStabilimentiRelazionateAlDocumento($notesid);
        
    }
    
}

function calcolaSituazioneDocumentoStandard($documento, $default){
    global $adb, $table_prefix, $current_user, $default_charset;
    
	$data_corrente = date("Y-m-d");
    
    $q_documento = "SELECT note.data_scadenza,
					note.kp_tipo_documento
					FROM {$table_prefix}_notes note
                    WHERE note.notesid = ".$documento;
    $res_documento = $adb->query($q_documento);
    if($adb->num_rows($res_documento)>0){
        
        $data_scadenza = $adb->query_result($res_documento,0,'data_scadenza');
		$data_scadenza = html_entity_decode(strip_tags($data_scadenza), ENT_QUOTES,$default_charset);
		if($data_scadenza == null || $data_scadenza == '0000-00-00'){
			$data_scadenza = '';
		}

		$tipo_documento = $adb->query_result($res_documento,0,'kp_tipo_documento');
		$tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES,$default_charset);
		if($tipo_documento == "" || $tipo_documento == null){
			$tipo_documento = 0;
		}

		$giorni_in_scadenza = getGiorniInScadenzaDocumenti($documento, $default, $tipo_documento);

		list($anno, $mese, $giorno) = explode("-", $data_corrente);
		$in_scadenza = date("Y-m-d", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
        
        if($data_scadenza != ''){
        
            if($data_scadenza == '2099-12-31' || $data_scadenza == '2999-12-31' || $data_scadenza == '9999-12-31'){
                $stato_documento = 'Valido senza scadenza';
			}
			elseif($data_scadenza > $data_corrente && $data_scadenza <= $in_scadenza){
				$stato_documento = 'In scadenza';
			}
            elseif($data_scadenza >= $in_scadenza){
                $stato_documento= 'In corso di validita';
            }
            else{				
				$stato_documento= 'Scaduto';
            }
    
        }
        else{
            $stato_documento = 'Valido senza scadenza';
        }
        
        $udp_documento = "UPDATE {$table_prefix}_notes SET
                            stato_documento = '".$stato_documento."'	
                            WHERE notesid =".$documento;
        $adb->query($udp_documento);

    }
    
}

function calcolaAziendeRelazionateAlDocumento($documento){
    global $adb, $table_prefix, $current_user, $default_charset;
    
	$lista_account = "";
	$array_account = array();
	
    $q_account_rel = "SELECT acc.accountid, acc.accountname 
						FROM {$table_prefix}_notes note
                        INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
                        INNER JOIN {$table_prefix}_account acc ON noterel.crmid = acc.accountid
                        WHERE noterel.relmodule = 'Accounts' AND note.notesid =".$documento;

    $res_account_rel = $adb->query($q_account_rel);
    $num_account_rel = $adb->num_rows($res_account_rel);

    for($i=0; $i<$num_account_rel; $i++){	

		$accountid = $adb->query_result($res_account_rel,$i,'accountid');
		$accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_account, $accountid)){

			$accountname = $adb->query_result($res_account_rel,$i,'accountname');
			$accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
			$accountname = addslashes($accountname);
			
			if($lista_account == ""){
				$lista_account = $accountname;
			}
			else{
				$lista_account .= ", ".$accountname;
			}
		}
	}
	
	$q_ticket_rel = "SELECT acc.accountid, acc.accountname 
				FROM {$table_prefix}_notes note
				INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
				INNER JOIN {$table_prefix}_troubletickets tick ON noterel.crmid = tick.ticketid
				INNER JOIN {$table_prefix}_account acc ON acc.accountid = tick.parent_id
				WHERE noterel.relmodule = 'HelpDesk' AND note.notesid = ".$documento;

    $res_ticket_rel = $adb->query($q_ticket_rel);
    $num_ticket_rel = $adb->num_rows($res_ticket_rel);

    for($i=0; $i<$num_ticket_rel; $i++){	

		$accountid = $adb->query_result($res_ticket_rel,$i,'accountid');
		$accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_account, $accountid)){

			$accountname = $adb->query_result($res_ticket_rel,$i,'accountname');
			$accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
			$accountname = addslashes($accountname);
			
			if($lista_account == ""){
				$lista_account = $accountname;
			}
			else{
				$lista_account .= ", ".$accountname;
			}
		}
	}
	
	$q_report_attivita_rel = "SELECT acc.accountid, acc.accountname 
							FROM {$table_prefix}_notes note
							INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
							INNER JOIN {$table_prefix}_visitreport ra ON noterel.crmid = ra.visitreportid
							INNER JOIN {$table_prefix}_account acc ON acc.accountid = ra.accountid
							WHERE noterel.relmodule = 'Visitreport' AND note.notesid = ".$documento;

    $res_report_attivita_rel = $adb->query($q_report_attivita_rel);
    $num_report_attivita_rel = $adb->num_rows($res_report_attivita_rel);

    for($i=0; $i<$num_report_attivita_rel; $i++){	

		$accountid = $adb->query_result($res_report_attivita_rel,$i,'accountid');
		$accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_account, $accountid)){

			$accountname = $adb->query_result($res_report_attivita_rel,$i,'accountname');
			$accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
			$accountname = addslashes($accountname);
			
			if($lista_account == ""){
				$lista_account = $accountname;
			}
			else{
				$lista_account .= ", ".$accountname;
			}
		}
	}

	$q_commesse_rel = "SELECT acc.accountid, acc.accountname 
					FROM {$table_prefix}_notes note
					INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
					INNER JOIN {$table_prefix}_commesse com ON noterel.crmid = com.commesseid
					INNER JOIN {$table_prefix}_account acc ON acc.accountid = com.account
					WHERE noterel.relmodule = 'Commesse' AND note.notesid = ".$documento;

    $res_commesse_rel = $adb->query($q_commesse_rel);
    $num_commesse_rel = $adb->num_rows($res_commesse_rel);

    for($i=0; $i<$num_commesse_rel; $i++){	

		$accountid = $adb->query_result($res_commesse_rel,$i,'accountid');
		$accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_account, $accountid)){

			$accountname = $adb->query_result($res_commesse_rel,$i,'accountname');
			$accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
			$accountname = addslashes($accountname);
			
			if($lista_account == ""){
				$lista_account = $accountname;
			}
			else{
				$lista_account .= ", ".$accountname;
			}
		}
	}
    
    $udp_documento = "UPDATE {$table_prefix}_notes SET
                        nome_azienda = '".$lista_account."'	
                        WHERE notesid =".$documento;
    $adb->query($udp_documento);

}

function controlloDuplicatiArray(&$array, $valore){
	$record_gia_passato = false;
                        
	if (empty($array)) {
		$array[] = $valore;
	} else {
		if (in_array($valore, $array)) {
			$record_gia_passato = true;
		} else {
			$array[] = $valore;
		}
	}
	
	return $record_gia_passato;
}

function calcolaStabilimentiRelazionateAlDocumento($documento){
    global $adb, $table_prefix, $current_user, $default_charset;
    
	$lista_stabilimenti = "";
	$array_stabilimenti = array();
    
    $q_stabilimento_rel = "SELECT stab.stabilimentiid, stab.nome_stabilimento stabilimentoname 
						FROM {$table_prefix}_notes note
						INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
						INNER JOIN {$table_prefix}_stabilimenti stab ON noterel.crmid = stab.stabilimentiid
						WHERE noterel.relmodule = 'Stabilimenti' AND note.notesid =".$documento;
	
    $res_stabilimento_rel = $adb->query($q_stabilimento_rel);
    $num_stabilimento_rel = $adb->num_rows($res_stabilimento_rel);

    for($i=0; $i<$num_stabilimento_rel; $i++){	

		$stabilimentiid = $adb->query_result($res_stabilimento_rel,$i,'stabilimentiid');
		$stabilimentiid = html_entity_decode(strip_tags($stabilimentiid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_stabilimenti, $stabilimentiid)){

			$stabilimentoname = $adb->query_result($res_stabilimento_rel,$i,'stabilimentoname');
			$stabilimentoname = html_entity_decode(strip_tags($stabilimentoname), ENT_QUOTES,$default_charset);
			$stabilimentoname = addslashes($stabilimentoname);
			
			if($lista_stabilimenti == ""){
				$lista_stabilimenti = $stabilimentoname;
			}
			else{
				$lista_stabilimenti .= ", ".$stabilimentoname;
			}
		}
	}
	
	$q_ticket_rel = "SELECT stab.stabilimentiid, stab.nome_stabilimento stabilimentoname 
				FROM {$table_prefix}_notes note
				INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
				INNER JOIN {$table_prefix}_troubletickets tick ON noterel.crmid = tick.ticketid
				INNER JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = tick.kp_stabilimento
				WHERE noterel.relmodule = 'HelpDesk' AND note.notesid = ".$documento;

    $res_ticket_rel = $adb->query($q_ticket_rel);
    $num_ticket_rel = $adb->num_rows($res_ticket_rel);

    for($i=0; $i<$num_ticket_rel; $i++){	

		$stabilimentiid = $adb->query_result($res_ticket_rel,$i,'stabilimentiid');
		$stabilimentiid = html_entity_decode(strip_tags($stabilimentiid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_stabilimenti, $stabilimentiid)){

			$stabilimentoname = $adb->query_result($res_ticket_rel,$i,'stabilimentoname');
			$stabilimentoname = html_entity_decode(strip_tags($stabilimentoname), ENT_QUOTES,$default_charset);
			$stabilimentoname = addslashes($stabilimentoname);
			
			if($lista_stabilimenti == ""){
				$lista_stabilimenti = $stabilimentoname;
			}
			else{
				$lista_stabilimenti .= ", ".$stabilimentoname;
			}
		}
	}
	
	$q_report_attivita_rel = "SELECT stab.stabilimentiid, stab.nome_stabilimento stabilimentoname 
							FROM {$table_prefix}_notes note
							INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
							INNER JOIN {$table_prefix}_visitreport ra ON noterel.crmid = ra.visitreportid
							INNER JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = ra.kp_stabilimento
							WHERE noterel.relmodule = 'Visitreport' AND note.notesid = ".$documento;

    $res_report_attivita_rel = $adb->query($q_report_attivita_rel);
    $num_report_attivita_rel = $adb->num_rows($res_report_attivita_rel);

    for($i=0; $i<$num_report_attivita_rel; $i++){	

		$stabilimentiid = $adb->query_result($res_report_attivita_rel,$i,'stabilimentiid');
		$stabilimentiid = html_entity_decode(strip_tags($stabilimentiid), ENT_QUOTES,$default_charset);
		
		if(!controlloDuplicatiArray($array_stabilimenti, $stabilimentiid)){

			$stabilimentoname = $adb->query_result($res_report_attivita_rel,$i,'stabilimentoname');
			$stabilimentoname = html_entity_decode(strip_tags($stabilimentoname), ENT_QUOTES,$default_charset);
			$stabilimentoname = addslashes($stabilimentoname);
			
			if($lista_stabilimenti == ""){
				$lista_stabilimenti = $stabilimentoname;
			}
			else{
				$lista_stabilimenti .= ", ".$stabilimentoname;
			}
		}
	}
    
    $udp_documento = "UPDATE {$table_prefix}_notes SET
                        nome_stabilimento = '".$lista_stabilimenti."'	
                        WHERE notesid =".$documento;
    $adb->query($udp_documento);

}

function aggiornaSituazioneFormazioneRisorsaInAnagrafica($risorsa){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    $situazione_form_contatto = 'Eseguita';
		
    $q_sit_form = "SELECT situazformazid,
                    tipo_corso,
                    stato_formazione 
                    FROM {$table_prefix}_situazformaz
                    INNER JOIN {$table_prefix}_crmentity ON crmid = situazformazid
                    WHERE deleted = 0 AND risorsa = ".$risorsa;

    $res_sit_form = $adb->query($q_sit_form);
    $num_sit_form = $adb->num_rows($res_sit_form);

    for($y=0; $y<$num_sit_form; $y++){
        $situazformazid = $adb->query_result($res_sit_form,$y,'situazformazid');
        $situazformazid = html_entity_decode(strip_tags($situazformazid), ENT_QUOTES,$default_charset);
        $situazformazid = addslashes($situazformazid);
        
        $stato_formazione = $adb->query_result($res_sit_form,$y,'stato_formazione');
        $stato_formazione = html_entity_decode(strip_tags($stato_formazione), ENT_QUOTES,$default_charset);
        $stato_formazione = addslashes($stato_formazione);
        
        $tipo_corso = $adb->query_result($res_sit_form,$y,'tipo_corso');
        $tipo_corso = html_entity_decode(strip_tags($tipo_corso), ENT_QUOTES,$default_charset);
        $tipo_corso = addslashes($tipo_corso);

        if($stato_formazione == 'Non eseguita' || $stato_formazione == 'Scaduta'){
            $situazione_form_contatto = 'Da eseguire';
        }
        elseif($stato_formazione == 'In scadenza' && $situazione_form_contatto == 'Eseguita'){
            $situazione_form_contatto = 'In scadenza';
        }

    }

    $upd_cont = "UPDATE {$table_prefix}_contactdetails SET
                    sit_form_cont = '".$situazione_form_contatto."'
                    WHERE contactid = ".$risorsa;
    $adb->query($upd_cont);
     
}

function aggiornaStatoMansioniRisorseNonAttive(){
	global $adb, $table_prefix, $current_user, $default_charset;

	/* kpro@tom010220170902 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2017, Kpro Consulting Srl
     */

	$data_corrente = date("Y-m-d");

	$lista_risorse_non_attive = getRisorseNonAttiveAllaData($data_corrente);

	foreach($lista_risorse_non_attive as $risorsa){

		//printf("\nRisorsa %s", $risorsa["contactid"]));

		$lista_mansioni_risorse = getMansioniRisorsa($risorsa["contactid"]);

		foreach($lista_mansioni_risorse as $mansione_risorsa){

			setMansioneRisorsaNonAttiva($mansione_risorsa["mansionirisorsaid"], $risorsa["data_fine_rap"]);

		}

	}

}

function getRisorseNonAttiveAllaData($data){
	global $adb, $table_prefix, $current_user, $default_charset;

	/* kpro@tom010220170902 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2017, Kpro Consulting Srl
     */

	$result = array();

	$q_query = "SELECT 
				cont.contactid contactid,
				cont.data_fine_rap data_fine_rap
				FROM {$table_prefix}_contactdetails cont
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cont.contactid
				WHERE ent.deleted = 0 AND (cont.data_fine_rap != '' AND cont.data_fine_rap != '0000-00-00' AND cont.data_fine_rap <= '".$data."')";
	
	$res_query = $adb->query($q_query);
    $num_result = $adb->num_rows($res_query);

    for($i = 0; $i < $num_result; $i++){

        $contactid = $adb->query_result($res_query, $i,'contactid');
        $contactid = html_entity_decode(strip_tags($contactid), ENT_QUOTES, $default_charset);
        //$contactid = addslashes($contactid);

		$data_fine_rap = $adb->query_result($res_query, $i,'data_fine_rap');
        $data_fine_rap = html_entity_decode(strip_tags($data_fine_rap), ENT_QUOTES, $default_charset);
        //$contactid = addslashes($contactid);

		$result[] = array("contactid" => $contactid,
							"data_fine_rap" => $data_fine_rap);

	}

	return $result;

}

function setMansioneRisorsaNonAttiva($mansionerisorsaid, $data_fine){
	global $adb, $table_prefix, $current_user, $default_charset;

	/* kpro@tom010220170902 */

    /**
     * @author Tomiello Marco
     * @copyright (c) 2017, Kpro Consulting Srl
     */

	$upd = "UPDATE {$table_prefix}_mansionirisorsa SET
			stato_mansione = 'Non attiva',
			data_fine = '".$data_fine."'
			WHERE stato_mansione != 'Non attiva' AND mansionirisorsaid = ".$mansionerisorsaid;
	$adb->query($upd);

}

function getConfigurazioneIdStatici($id_configurazione){	
	global $adb, $table_prefix, $current_user, $default_charset;

	/* kpro@bid16042018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */

	$valore = 0;

	$q_query = "SELECT valore
			FROM kp_settings_config_id_statici
			WHERE id_configurazione = ".$id_configurazione;
	
	$res_query = $adb->query($q_query);

    if($adb->num_rows($res_query) > 0){

        $valore = $adb->query_result($res_query, 0,'valore');
		$valore = html_entity_decode(strip_tags($valore), ENT_QUOTES, $default_charset);
		if($valore == '' || $valore == null){
			$valore = 0;
		}

	}

	return $valore;

}

function getConfigurazioniIdStatici(){
	global $adb, $table_prefix, $default_charset, $current_user, $dbconfig;

	$result = array();

	$query = "SELECT 
				* 
				FROM information_schema.tables
				WHERE table_schema = '".$dbconfig['db_name']."' AND table_name = 'kp_settings_config_id_statici'";

	$result_query = $adb->query($query);
	$num_result = $adb->num_rows($result_query);
		
	if( $num_result > 0 ){

		$query = "SELECT 
					* 
					FROM kp_settings_config_id_statici";

		$result_query = $adb->query($query);
		$num_result = $adb->num_rows($result_query);

		for( $i=0; $i < $num_result; $i++ ){

			$id_configurazione = $adb->query_result($result_query, $i, 'id_configurazione');
			$id_configurazione = html_entity_decode(strip_tags($id_configurazione), ENT_QUOTES,$default_charset);
			
			$nome_area_configurazione = $adb->query_result($result_query, $i, 'nome_area_configurazione');
			$nome_area_configurazione = html_entity_decode(strip_tags($nome_area_configurazione), ENT_QUOTES,$default_charset);

			$nome_configurazione = $adb->query_result($result_query, $i, 'nome_configurazione');
			$nome_configurazione = html_entity_decode(strip_tags($nome_configurazione), ENT_QUOTES,$default_charset);

			$valore = $adb->query_result($result_query, $i, 'valore');
			$valore = html_entity_decode(strip_tags($valore), ENT_QUOTES,$default_charset);

			$chiave = $nome_area_configurazione." - ".$nome_configurazione;

			$result[$chiave] = array("nome_area_configurazione" => $nome_area_configurazione,
									"nome_configurazione" => $nome_configurazione,
									"valore" => $valore);

		}

	}

	return $result;

}

function calcolaNumeroMesiIncremento($frequenza){
    global $adb, $table_prefix, $current_user;
    
    $numeroMesiIncremento = 0;
    
    switch($frequenza){
        case "Mensile":
            $numeroMesiIncremento = 1;
            break;

        case "Bimestrale":
            $numeroMesiIncremento = 2;
            break;

        case "Trimestrale":	
            $numeroMesiIncremento = 3;
            break;

        case "Quadrimestrale":	
            $numeroMesiIncremento = 4;
            break;

        case "Quinquemestrale":	
            $numeroMesiIncremento = 5;
            break;

        case "Semestrale":
            $numeroMesiIncremento = 6;			
            break;

        case "Annuale":	
            $numeroMesiIncremento = 12;
            break;

        case "Biennale":
            $numeroMesiIncremento = 24;			
            break;

        case "Triennale":	
            $numeroMesiIncremento = 36;	
            break;	

        case "Quadriennale":	
            $numeroMesiIncremento = 48;	
            break;
        
        case "Quinquennale":	
            $numeroMesiIncremento = 60;	
			break;
			
		case "Decennale":	
            $numeroMesiIncremento = 120;	
            break;

        case "Quindicennale":	
            $numeroMesiIncremento = 180;	
            break;

		case "Illimitata":
            $numeroMesiIncremento = 999;
            break;
    }
    
    return $numeroMesiIncremento;
    
}

function InvioNotificaPortale($utente_portale, $azione, $record, $modulo, $nome_record, $azione2="", $record2="", $modulo2="", $nome_record2=""){
	global $adb, $table_prefix, $default_charset, $current_user, $site_URL;

	$res_invio_notifica = false;

	if($utente_portale != 0 && $utente_portale != '' && $utente_portale != null){

		$q_utente = "SELECT last_name,
					first_name,
					email1
					FROM {$table_prefix}_users 
					WHERE id = 1";
		$res_utente = $adb->query($q_utente);

		$mail_utente = $adb->query_result($res_utente, 0, 'email1');
		$mail_utente = html_entity_decode(strip_tags($mail_utente), ENT_QUOTES, $default_charset);

		$dati_utente_portale = getDatiRisorsa($utente_portale);

		$q_utente_destinatario = "SELECT last_name,
								first_name,
								email1,
								notify_me_via
								FROM {$table_prefix}_users 
								WHERE id = ".$dati_utente_portale['assegnatario_azienda'];
		$res_utente_destinatario = $adb->query($q_utente_destinatario);
		if($adb->num_rows($res_utente_destinatario) > 0){
			$mail_utente_destinatario = $adb->query_result($res_utente_destinatario, 0, 'email1');
			$mail_utente_destinatario = html_entity_decode(strip_tags($mail_utente_destinatario), ENT_QUOTES, $default_charset);

			$notify_me_via = $adb->query_result($res_utente_destinatario, 0, 'notify_me_via');
			$notify_me_via = html_entity_decode(strip_tags($notify_me_via), ENT_QUOTES, $default_charset);
			
			//if($notify_me_via != 'ModNotifications'){
				$res = ComponiEmailNotificaPortale($mail_utente_destinatario, $mail_utente, $dati_utente_portale['nome_risorsa'], $dati_utente_portale['nome_azienda'], $azione, $record, $modulo, $nome_record, $azione2, $record2, $modulo2, $nome_record2);
			/*}
			else{
				$res = ComponiNotificaPortale($dati_utente_portale['assegnatario_azienda'], $dati_utente_portale['nome_risorsa'], $dati_utente_portale['nome_azienda'], $azione, $record, $nome_record, $azione2, $nome_record2);
			}*/

			if($res){
				$res_invio_notifica = true;
			}
		}
	}

    return $res_invio_notifica;
}

function ComponiEmailNotificaPortale($mail_utente_destinatario, $mail_utente, $nome_risorsa, $nome_azienda, $azione, $record, $modulo, $nome_record, $azione2, $record2, $modulo2, $nome_record2) {
	global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

	$id_statici = getConfigurazioniIdStatici();
	$id_statico = $id_statici["Template Email - Portale SPro - Invio notifiche creazione/modifica"];
	if( $id_statico["valore"] == "" && $id_statico["valore"] == 0){
		$templatemailid = 0;
	}
	else{
		$templatemailid = $id_statico["valore"];
	}
	
	if($templatemailid != 0){

		$q_select_template = "SELECT subject,body
			FROM {$table_prefix}_emailtemplates
			WHERE deleted = 0 AND templateid = ".$templatemailid;
		$res_select_template = $adb->query($q_select_template);
		if($adb->num_rows($res_select_template) > 0){
			$soggetto_mail = decode_html($adb->query_result($res_select_template, 0, 'subject'));

			$corpo_mail = decode_html($adb->query_result($res_select_template, 0, 'body'));

			$corpo_mail = str_replace("_nome_risorsa_",$nome_risorsa,$corpo_mail);
			$corpo_mail = str_replace("_nome_azienda_",$nome_azienda,$corpo_mail);
			$corpo_mail = str_replace("_azione_",$azione,$corpo_mail);
			$corpo_mail = str_replace("_site_url_",$site_URL,$corpo_mail);
			$corpo_mail = str_replace("_id_record_",$record,$corpo_mail);
			$corpo_mail = str_replace("_modulo_",$modulo,$corpo_mail);
			$corpo_mail = str_replace("_nome_record_",$nome_record,$corpo_mail);
			$corpo_mail = str_replace("_azione2_",$azione2,$corpo_mail);
			$corpo_mail = str_replace("_id_record2_",$record2,$corpo_mail);
			$corpo_mail = str_replace("_modulo2_",$modulo2,$corpo_mail);
			$corpo_mail = str_replace("_nome_record2_",$nome_record2,$corpo_mail);

			$nome_mittente = "SPRO";
			
			$mail = send_mail_kp("", $mail_utente_destinatario, $nome_mittente, $mail_utente, $soggetto_mail, $corpo_mail);

			if ($mail == 1) {
				return true;
			} else {
				return false;
			}
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function ComponiNotificaPortale($assegnatario, $nome_risorsa, $nome_azienda, $azione, $record, $nome_record, $azione2, $nome_record2) {
	global $adb, $table_prefix, $current_user, $site_URL, $default_charset;
	
	$corpo_notifica = "L'utente portale _nome_risorsa_ dell'azienda _nome_azienda_ ha _azione_ _nome_record_ _azione2_ _nome_record2_";

	$corpo_notifica = str_replace("_nome_risorsa_",$nome_risorsa,$corpo_notifica);
	$corpo_notifica = str_replace("_nome_azienda_",$nome_azienda,$corpo_notifica);
	$corpo_notifica = str_replace("_azione_",$azione,$corpo_notifica);
	$corpo_notifica = str_replace("_nome_record_",$nome_record,$corpo_notifica);
	$corpo_notifica = str_replace("_azione2_",$azione2,$corpo_notifica);
	$corpo_notifica = str_replace("_nome_record2_",$nome_record2,$corpo_notifica);
	
	send_notifica_kp($assegnatario, $record, $corpo_notifica);

	return true;

}

function getDatiRisorsa($risorsa){
	global $adb, $table_prefix, $current_user, $site_URL, $default_charset;
	
	$q_risorsa = "SELECT 
				CONCAT(cont.lastname, ' ', cont.firstname) nome_risorsa,
				acc.accountname nome_azienda,
				ent1.smownerid assegnatario_azienda
				FROM {$table_prefix}_contactdetails cont 
				INNER JOIN {$table_prefix}_account acc ON acc.accountid = cont.accountid
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cont.contactid
				INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = acc.accountid
				WHERE ent.deleted = 0 AND ent1.deleted = 0 AND cont.contactid = ".$risorsa;
	$res_risorsa = $adb->query($q_risorsa);
	if($adb->num_rows($res_risorsa) > 0){
		
		$nome_risorsa = $adb->query_result($res_risorsa, 0, 'nome_risorsa');
		$nome_risorsa = html_entity_decode(strip_tags($nome_risorsa), ENT_QUOTES,$default_charset);

		$nome_azienda = $adb->query_result($res_risorsa, 0, 'nome_azienda');
		$nome_azienda = html_entity_decode(strip_tags($nome_azienda), ENT_QUOTES,$default_charset);
		
		$assegnatario_azienda = $adb->query_result($res_risorsa, 0, 'assegnatario_azienda');
		$assegnatario_azienda = html_entity_decode(strip_tags($assegnatario_azienda), ENT_QUOTES,$default_charset);
	}
	else{
		$nome_risorsa = "";
		$nome_azienda = "";
		$assegnatario_azienda = 0;
	}

	$result = array(
		'nome_risorsa' => $nome_risorsa,
		'nome_azienda' => $nome_azienda,
		'assegnatario_azienda' => $assegnatario_azienda
	);
				
	return $result;
	
}

function send_notifica_kp($assegnatario, $record, $corpo_notifica){

	require_once('modules/ModNotifications/ModNotifications.php');

	$notifica = CRMEntity::getInstance('ModNotifications');
	$notifica->saveFastNotification(
		array(
			'assigned_user_id' => $assegnatario,
			'related_to' => $record,
			'mod_not_type' => 'Changed record',
			'createdtime' => date('Y-m-d H:i:s'),
			'modifiedtime' => date('Y-m-d H:i:s'),
			'description' => $corpo_notifica,
			),false
		);
	
}

function send_mail_kp($module, $to_email, $from_name, $from_email, $subject, $contents, $cc = '', $bcc = '', $attachment = '', $emailid = '', $logo = '', $newsletter_params = '', &$mail_tmp = '', $messageid = '', $message_mode = '') {

	include_once('modules/Emails/mail.php');
	include_once('modules/Emails/class.phpmailer.php');

    global $adb, $log, $table_prefix;
    global $root_directory;
    global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;

    $adb->println("To id => '" . $to_email . "'\nSubject ==>'" . $subject . "'\nContents ==> '" . $contents . "'");

    if ($from_email == '') {
        $from_email = getUserEmailId('user_name', $from_name);
    }

    $mail = new PHPMailer();

    $mail->Subject = $subject;

    if (is_array($contents)) {
        $mail->Body = $contents['html'];
        $mail->AltBody = $contents['text'];
    } else {
        $mail->Body = $contents;
        $mail->AltBody = strip_tags(preg_replace(array("/<p>/i", "/<br>/i", "/<br \/>/i"), array("\n", "\n", "\n"), $contents));
    }

    $mail->IsSMTP();
    setMailServerProperties($mail, $from_email);
    $mail->From = $from_email;
    $mail->FromName = $from_name;
    if ($to_email != '') {
        if (is_array($to_email)) {
            foreach ($to_email as $e) {
				$e = 
                $mail->addAddress($e);
            }
        } else {
            $_tmp = explode(",", trim($to_email, ","));
            foreach ($_tmp as $e) {
                $mail->addAddress($e);
            }
        }
    }

    $mail->AddReplyTo($from_email);
    $mail->WordWrap = 50;

    if ($attachment != '') {
        if (is_array($attachment)) {
			foreach($attachment as $e) {
				if(is_numeric($e)){
					$e = getAttachmentPathKp($e);
					if($e != ""){
						$mail->AddAttachment($e);
					}
				}
				else{
					$mail->AddAttachment($e);
				}
			}
        } else {				
			$_tmp = explode(",",trim($attachment,","));
			foreach($_tmp as $e) {
				if(is_numeric($e)){
					$e = getAttachmentPathKp($e);
					if($e != ""){
						$mail->AddAttachment($e);
					}
				}
				else{
					$mail->AddAttachment($e);
				}
			}
        }
    }

    $mail->IsHTML(true);

    if ($newsletter_params && $newsletter_params['smtp_config']['enable'] === true) {
        if ($newsletter_params['smtp_config']['smtp_auth'] == "true") {
            $mail->SMTPAuth = true;
        } else {
            $mail->SMTPAuth = false;
        }
        $mail->Host = $newsletter_params['smtp_config']['server'];
        $mail->Username = $newsletter_params['smtp_config']['server_username'];
        $mail->Password = $newsletter_params['smtp_config']['server_password'];
    }

    setCCAddress($mail, 'cc', $cc);
    setCCAddress($mail, 'bcc', $bcc);

    if (empty($mail->Host)) {
        return 0;
    }

    if ($newsletter_params) {
        if ($newsletter_params['sender'] != '') {
            $mail->Sender = $newsletter_params['sender'];
            $mail->addCustomHeader("Errors-To: " . $newsletter_params['sender']);
        }
        if ($newsletter_params['newsletterid'] != '') {
            $mail->addCustomHeader("X-MessageID: " . $newsletter_params['newsletterid']);
        }
        if ($newsletter_params['crmid'] != '') {
            $mail->addCustomHeader("X-ListMember: " . $newsletter_params['crmid']);
        }
        $mail->addCustomHeader("Precedence: bulk");
    }
    //crmv@22700e

    if (!empty($messageid) && in_array($message_mode, array('reply', 'reply_all', 'forward'))) {
        $focusMessage = CRMentity::getInstance('Messages');
        $result = $focusMessage->retrieve_entity_info_no_html($messageid, 'Messages', false);
        if (empty($result)) { // no errors
            $mail->addCustomHeader("In-Reply-To: " . $focusMessage->column_fields['messageid']);
            $mail->addCustomHeader("References: " . $focusMessage->column_fields['mreferences'] . $focusMessage->column_fields['messageid']);
            //TODO: $mail->addCustomHeader("Thread-Index: ");
        }
    }

    $mail_status = MailSend($mail);

    if ($mail_status == 1) {
        $mail_tmp = $mail;
    } else {
        $error_string = 'Send mail failed! from ' . $from_email . ' to ' . $to_email . ' subject ' . $subject . ' reason:' . $mail_status;
    }
    $mail_error = $mail_status;
    return $mail_error;
}

function getAttachmentPathKp($attachment){
	global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

	$link_allegato = "";

	$q_allegato = "SELECT 
					att.attachmentsid attachmentsid,
					att.name filename,
					att.path cartella
					FROM {$table_prefix}_seattachmentsrel attrel
					INNER JOIN {$table_prefix}_attachments att ON att.attachmentsid = attrel.attachmentsid
					WHERE attrel.crmid = ".$attachment;
	$res_allegato = $adb->query($q_allegato);
	if($adb->num_rows($res_allegato)>0){
		$attachmentsid = $adb->query_result($res_allegato,0,'attachmentsid');
		$filename = $adb->query_result($res_allegato,0,'filename');
		$cartella = $adb->query_result($res_allegato,0,'cartella');

		$link_allegato = $cartella.$attachmentsid."_".$filename;
	}

	return $link_allegato;
}

function calcolaSituazioneDocumenti(){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	printf("<br />Calcolo situazione documenti iniziato!");
	 
	$default_in_scadenza = 0;
	 
	$lista_aziende = getAziendePerSituazioneDocumenti();
	
	foreach($lista_aziende as $azienda){
		
		printf("<br />--- Azienda: ".$azienda['id']);
		
		calcolaSituazioneDocumentiAzienda($azienda['id'], $default_in_scadenza);
		
	}
	
	printf("<br />Calcolo situazione documenti terminato!");
    
}

function getAziendePerSituazioneDocumenti(){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = array();
	
	$data_corrente = date("Y-m-d");
	
	$q_account = "SELECT acc.accountid
				FROM {$table_prefix}_account acc
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
				WHERE ent.deleted = 0";
					
	//printf("<br />Query lista Aziende: <br /> %s", $q_account); die;
	
    $res_account = $adb->query($q_account);
    $num_account = $adb->num_rows($res_account);

    for($i=0; $i<$num_account; $i++){		

        $account = $adb->query_result($res_account,$i,'accountid');
        $account = html_entity_decode(strip_tags($account), ENT_QUOTES, $default_charset);
		
		$result[] = array('id' => $account);
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiAzienda($account, $default_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
    
    $q_vecchi = "UPDATE {$table_prefix}_kpsituazionedocumenti SET
                    kp_aggiornato = '0'
                    WHERE kp_azienda = ".$account;
    $adb->query($q_vecchi);
	
	$lista_stabilimenti = getStabilimentiAzienda($account);
	
	foreach($lista_stabilimenti as $stabilimento){
		
		printf("<br />----- Stabilimento: ".$stabilimento['id']);
		
		calcolaSituazioneDocumentiStabilimento($account, $stabilimento['id'], $default_in_scadenza);
		
	}
	
	$upd = "UPDATE {$table_prefix}_crmentity ent
			INNER JOIN {$table_prefix}_kpsituazionedocumenti sitdoc ON sitdoc.kpsituazionedocumentiid = ent.crmid
			SET
			ent.deleted = 1
			WHERE sitdoc.kp_aggiornato != '1' AND sitdoc.kp_azienda = ".$account;
	$adb->query($upd);
    
}

function getStabilimentiAzienda($azienda){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = array();
	
	$data_corrente = date("Y-m-d");
	
	$q = "SELECT stab.stabilimentiid
		FROM {$table_prefix}_stabilimenti stab
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = stab.stabilimentiid
		WHERE ent.deleted = 0 AND stab.azienda = ".$azienda;
	
	$res = $adb->query($q);
	$num = $adb->num_rows($res);
	
	for($i = 0; $i < $num; $i++){	

		$stabilimento = $adb->query_result($res, $i, 'stabilimentiid');
		$stabilimento = html_entity_decode(strip_tags($stabilimento), ENT_QUOTES, $default_charset);
		
		$result[] = array('id' => $stabilimento);
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiStabilimento($account, $stabilimento, $default_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	$lista_tipi_documenti = getTipiDocumentiAzienda($account, $stabilimento);
	
	foreach($lista_tipi_documenti as $tipo_documento){
		
		printf("<br />--------- Tipo Documento: ".$tipo_documento['id']);

		$giorni_in_scadenza = getGiorniInScadenzaAzienda($account, $default_in_scadenza, 'Documenti', $tipo_documento['id']);

		printf(" Giorni in scadenza: ".$giorni_in_scadenza);
		
		calcolaSituazioneDocumentiTipoDocumento($account, $stabilimento, $tipo_documento['id'], $tipo_documento['stato'], $giorni_in_scadenza, "si");
		
	}
    
}

function getTipiDocumentiAzienda($azienda, $stabilimento){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = array();

	$data_corrente = date('Y-m-d');
	
	$q = "SELECT td.tipidocumentiid,
		tda.kp_stato_tipo_doc_a,
		tda.kp_data_inizio,
		tda.kp_data_fine
		FROM {$table_prefix}_kptipidocumentiaziend tda
		INNER JOIN {$table_prefix}_tipidocumenti td ON td.tipidocumentiid = tda.kp_tipo_documento
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = tda.kptipidocumentiaziendid
		INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = td.tipidocumentiid
		WHERE ent.deleted = 0 AND ent1.deleted = 0
		AND tda.kp_azienda = ".$azienda."
		AND tda.kp_stabilimento = ".$stabilimento;
	
	$res = $adb->query($q);
	$num = $adb->num_rows($res);
	
	for($i = 0; $i < $num; $i++){	

		$tipo_documento = $adb->query_result($res, $i, 'tipidocumentiid');
		$tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES, $default_charset);

		$stato_tipo_documento = $adb->query_result($res, $i, 'kp_stato_tipo_doc_a');
		$stato_tipo_documento = html_entity_decode(strip_tags($stato_tipo_documento), ENT_QUOTES, $default_charset);

		$data_inizio = $adb->query_result($res, $i, 'kp_data_inizio');
		$data_inizio = html_entity_decode(strip_tags($data_inizio), ENT_QUOTES, $default_charset);
		if($data_inizio == null || $data_inizio == '0000-00-00'){
			$data_inizio = "";
		}

		$data_fine = $adb->query_result($res, $i, 'kp_data_fine');
		$data_fine = html_entity_decode(strip_tags($data_fine), ENT_QUOTES, $default_charset);
		if($data_fine == null || $data_fine == '0000-00-00'){
			$data_fine = "";
		}

		if($data_inizio != "" && $data_fine != "" && $data_corrente >= $data_inizio && $data_corrente <= $data_fine){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		elseif($data_inizio == "" && $data_fine != "" && $data_corrente <= $data_fine){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		elseif($data_inizio != "" && $data_fine == "" && $data_corrente >= $data_inizio){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		elseif($data_inizio == "" && $data_fine == ""){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiTipoDocumento($account, $stabilimento, $tipo_documento, $stato_tipo_documento, $giorni_in_scadenza, $aggiorna){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	$dati_tipo_documento = getDatiTipoDocumento($tipo_documento);
        
    printf(", Nome: ".$dati_tipo_documento['nome'].", Validità: ".$dati_tipo_documento['validita']);
    
    $lista_documenti_redatti = getDocumentiRedattiStabilimentoTipoDocumento($account, $stabilimento, $tipo_documento, $giorni_in_scadenza);
 
	if(count($lista_documenti_redatti) == 0){
		
		$stato_documento = 'Non eseguito';
		$data_documento = '';
		$validita_documento = '';
		$nota_stato = "Nota stato situazione documenti: Il documento NON è stato eseguito in quanto non sono stati trovati documenti di questo tipo collegati allo stabilimento.";
		
	}
	else{
		
		$stato_documento = $lista_documenti_redatti[0]['stato'];
		$data_documento = $lista_documenti_redatti[0]['data_documento'];
		$validita_documento = $lista_documenti_redatti[0]['data_validita'];
		$nota_stato = $lista_documenti_redatti[0]['nota_stato'];
		
	}
	
	printf("<br />----------- Stato: %s <br />----------- Data Documento: %s <br />----------- Data Validità: %s <br />----------- Nota: %s", $stato_documento, $data_documento, $validita_documento, $nota_stato);
            
    if($aggiorna == "si"){
		
		setSituazioneDocumenti($account, $stabilimento, $tipo_documento, $data_documento, $validita_documento, $stato_documento, $stato_tipo_documento, $nota_stato, $lista_documenti_redatti);
        
    }
    
}

function getDatiTipoDocumento($tipo_documento){
	global $adb, $table_prefix, $current_user, $default_charset;

	/* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */

	$result = array();
	
	$q = "SELECT td.nome_tipo_documento,
		td.validita_tipi_doc,
		td.kp_giorni_in_scad
		FROM {$table_prefix}_tipidocumenti td
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = td.tipidocumentiid
		WHERE ent.deleted = 0 AND td.tipidocumentiid = ".$tipo_documento;
	
	$res = $adb->query($q);
	if($adb->num_rows($res) > 0){	

		$nome_tipo_documento = $adb->query_result($res, 0, 'nome_tipo_documento');
		$nome_tipo_documento = html_entity_decode(strip_tags($nome_tipo_documento), ENT_QUOTES, $default_charset);

		$validita_tipi_doc = $adb->query_result($res, 0, 'validita_tipi_doc');
		$validita_tipi_doc = html_entity_decode(strip_tags($validita_tipi_doc), ENT_QUOTES, $default_charset);

		$giorni_in_scadenza = $adb->query_result($res, 0, 'kp_giorni_in_scad');
		$giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES, $default_charset);
		if($giorni_in_scadenza == '' || $giorni_in_scadenza == null){
			$giorni_in_scadenza = 0;
		}
	}
	else{
		$nome_tipo_documento = "";
		$validita_tipi_doc = "";
		$giorni_in_scadenza = 0;
	}

	$result = array(
		'nome' => $nome_tipo_documento,
		'validita' => $validita_tipi_doc,
		'giorni_in_scadenza' => $giorni_in_scadenza
	);
	
	return $result;
}

function getDocumentiRedattiStabilimentoTipoDocumento($account, $stabilimento, $tipo_documento, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid24052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	$result = array();

	$data_corrente = date("Y-m-d");
	$data_corrente_inv = date("d-m-Y");
	list($anno, $mese, $giorno) = explode("-", $data_corrente);
	$in_scadenza = date("Y-m-d", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
	$in_scadenza_inv = date("d-m-Y", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
	 
	$q_documenti = "SELECT note.notesid,
					note.title,
					note.kp_data_documento,
					note.data_scadenza
					FROM {$table_prefix}_notes note
					INNER JOIN {$table_prefix}_tipidocumenti td ON td.tipidocumentiid = note.kp_tipo_documento
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = note.notesid
					INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = td.tipidocumentiid
					WHERE ent.deleted = 0 AND ent1.deleted = 0
					AND td.tipidocumentiid = ".$tipo_documento."
					ORDER BY note.kp_data_documento DESC";
		
	$res_documenti = $adb->query($q_documenti);
    $num_documenti = $adb->num_rows($res_documenti);
    for($i = 0; $i < $num_documenti; $i++){
		
		$notesid = $adb->query_result($res_documenti, $i, 'notesid');
		$notesid = html_entity_decode(strip_tags($notesid), ENT_QUOTES, $default_charset);

		if($stabilimento != 0){
			$verifica_documento = verificaRelazioneDocumentoStabilimento($stabilimento, $notesid);
		}
		else{
			$verifica_documento = verificaRelazioneDocumentoAzienda($account, $notesid);
		}
		
		if($verifica_documento){

			$titolo_documento = $adb->query_result($res_documenti, $i, 'title');
			$titolo_documento = html_entity_decode(strip_tags($titolo_documento), ENT_QUOTES, $default_charset);
			
			$data_documento = $adb->query_result($res_documenti, $i, 'kp_data_documento');
			$data_documento = html_entity_decode(strip_tags($data_documento), ENT_QUOTES, $default_charset);
			
			$data_scadenza = $adb->query_result($res_documenti, $i, 'data_scadenza');
			$data_scadenza = html_entity_decode(strip_tags($data_scadenza), ENT_QUOTES, $default_charset);
			if($data_scadenza != "" && $data_scadenza != null && $data_scadenza != "0000-00-00"){ /* kpro@bid310820180850 */
				list($anno_scad, $mese_scad, $giorno_scad) = explode("-", $data_scadenza);
				$data_scadenza_inv = date("d-m-Y", mktime(0, 0, 0, $mese_scad, $giorno_scad, $anno_scad));
				$in_scadenza_reale_inv = date("d-m-Y", mktime(0, 0, 0, $mese_scad, (int)$giorno_scad - $giorni_in_scadenza, $anno_scad));

				if($data_scadenza == '2099-12-31' || $data_scadenza == '2999-12-31' || $data_scadenza == '9999-12-31'){
					$data_scadenza = '';
					$stato = 'Valido senza scadenza';
					$nota_stato = "Nota stato situazione documenti: Il documento e' 'Valido senza scadenza' in quanto l'ultimo documento redatto ha data scadenza vuota o pari a '31-12-2099' oppure '31-12-2999'.";
				}
				elseif($data_scadenza > $data_corrente && $data_scadenza <= $in_scadenza){
					$stato = 'In scadenza';
					$nota_stato = "Nota stato situazione documenti: Il documento e' 'In scadenza' in quanto la data della scadenza dell'ultimo documento redatto (".$data_scadenza_inv.") risulta compresa tra la data corrente (".$data_corrente_inv.") e la data in cui andra' 'In scadenza'(".$in_scadenza_reale_inv.").";
				}
				elseif($data_scadenza >= $in_scadenza){
					$stato = 'In corso di validita';
					$nota_stato = "Nota stato situazione documenti: Il documento e' 'In corso di validita' in quanto la data della scadenza dell'ultimo documento redatto (".$data_scadenza_inv.") risulta maggiore della data ".$in_scadenza_reale_inv." in cui andra' 'In scadenza'.";
				}
				else{
					$stato = 'Scaduto';
					$nota_stato = "Nota stato situazione documenti: Il documento e' 'Scaduto' in quanto la data della scadenza dell'ultimo documento redatto (".$data_scadenza_inv.") risulta inferiore alla data odierna (".$data_corrente_inv.").";
				}
			/* kpro@bid310820180850 */
			}
			else{
				$data_scadenza = '';
				$stato = 'Valido senza scadenza';
				$nota_stato = "Nota stato situazione documenti: Il documento e' 'Valido senza scadenza' in quanto l'ultimo documento redatto ha data scadenza vuota o pari a '31-12-2099' oppure '31-12-2999'.";
			}
			/* kpro@bid310820180850 end */
			
			$result[] = array(
				'id' => $notesid,
				'nome' => $titolo_documento,
				'data_documento' => $data_documento,
				'data_validita' => $data_scadenza,
				'stato' => $stato,
				'nota_stato' => $nota_stato
			);
		}
		
	}
	
	return $result;
	
}

function verificaRelazioneDocumentoAzienda($account, $notesid){
	global $adb, $table_prefix, $current_user, $default_charset;

	$result = false;

	$q = "SELECT note.notesid
		FROM {$table_prefix}_notes note
		INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
		INNER JOIN {$table_prefix}_account acc ON acc.accountid = noterel.crmid
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
		WHERE ent.deleted = 0 AND note.notesid = ".$notesid."
		AND acc.accountid = ".$account;
	$res = $adb->query($q);
	if($adb->num_rows($res) > 0){
		$result = true;
	}

	return $result;
}

function verificaRelazioneDocumentoStabilimento($stabilimento, $notesid){
	global $adb, $table_prefix, $current_user, $default_charset;

	$result = false;

	$q = "SELECT note.notesid
		FROM {$table_prefix}_notes note
		INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
		INNER JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = noterel.crmid
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = stab.stabilimentiid
		WHERE ent.deleted = 0 AND note.notesid = ".$notesid."
		AND stab.stabilimentiid = ".$stabilimento;
	$res = $adb->query($q);
	if($adb->num_rows($res) > 0){
		$result = true;
	}

	return $result;
}

function setSituazioneDocumenti($account, $stabilimento, $tipo_documento, $data_documento, $validita_documento, $stato_documento, $stato_tipo_documento, $nota_stato, $lista_documenti_redatti){
	global $adb, $table_prefix, $current_user, $default_charset;
	
	$kpsituazionedocumentiid = 0;
	$normativa = 0;	

	$documento = $lista_documenti_redatti[0]['id'];
	if($documento == '' || $documento == null){
		$documento = 0;
	}

	//print_r($account.' - '.$stabilimento.' - '.$tipo_documento.' - '.$documento.' - '.$data_documento.' - '.$validita_documento.' - '.$stato_documento.' - '.$nota_stato);
		
	$q_verifica = "SELECT sitdoc.kpsituazionedocumentiid 
				FROM {$table_prefix}_kpsituazionedocumenti sitdoc
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sitdoc.kpsituazionedocumentiid
				WHERE ent.deleted = 0 AND sitdoc.kp_azienda = ".$account."
				AND sitdoc.kp_stabilimento = ".$stabilimento." 
				AND sitdoc.kp_tipo_documento = ".$tipo_documento;

	$res_verifica = $adb->query($q_verifica);
	if($adb->num_rows($res_verifica)>0){
		
		$kpsituazionedocumentiid = $adb->query_result($res_verifica,0,'kpsituazionedocumentiid');
		$kpsituazionedocumentiid = html_entity_decode(strip_tags($kpsituazionedocumentiid), ENT_QUOTES,$default_charset);

		$nota_stato = addslashes($nota_stato); /* kpro@bid310820180850 */
		
		$upd = "UPDATE {$table_prefix}_kpsituazionedocumenti SET
				kp_azienda = ".$account.",
				kp_stabilimento = ".$stabilimento.",
				kp_tipo_documento = ".$tipo_documento.",
				kp_normativa = ".$normativa.",
				kp_documento = ".$documento.",
				kp_data_documento = '".$data_documento."',
				kp_validita_doc = '".$validita_documento."',
				kp_stato_sit_doc = '".$stato_documento."',
				kp_stato_tipo_doc_a = '".$stato_tipo_documento."',
				kp_aggiornato = '1',
				description = '".$nota_stato."'
				WHERE kpsituazionedocumentiid = ".$kpsituazionedocumentiid;
		$adb->query($upd);
		
		echo "<br>----------- AGGIORNATO record ".$kpsituazionedocumentiid."<br>";
	}
	else{
		
		$new_situazione_documenti = CRMEntity::getInstance('KpSituazioneDocumenti'); 
		$new_situazione_documenti->column_fields['assigned_user_id'] = 1;
		$new_situazione_documenti->column_fields['creator'] = 1;
		if($account != "" && $account != 0){
			$new_situazione_documenti->column_fields['kp_azienda'] = $account;
		}
		if($stabilimento != "" && $stabilimento != 0){
			$new_situazione_documenti->column_fields['kp_stabilimento'] = $stabilimento;
		}
		if($tipo_documento != "" && $tipo_documento != 0){
			$new_situazione_documenti->column_fields['kp_tipo_documento'] = $tipo_documento;
		}
		if($normativa != "" && $normativa != 0){
			$new_situazione_documenti->column_fields['kp_normativa'] = $normativa;
		}
		if($documento != "" && $documento != 0){
			$new_situazione_documenti->column_fields['kp_documento'] = $documento;
		}
		if($data_documento != ""){
			$new_situazione_documenti->column_fields['kp_data_documento'] = $data_documento;
		}
		if($validita_documento != ""){
			$new_situazione_documenti->column_fields['kp_validita_doc'] = $validita_documento;
		}
		if($stato_documento != ""){
			$new_situazione_documenti->column_fields['kp_stato_sit_doc'] = $stato_documento;
		}
		if($stato_tipo_documento != ""){
			$new_situazione_documenti->column_fields['kp_stato_tipo_doc_a'] = $stato_tipo_documento;
		}
		if($nota_stato != ""){
			$new_situazione_documenti->column_fields['description'] = $nota_stato;
		}
		$new_situazione_documenti->column_fields['kp_aggiornato'] = '1';
		$new_situazione_documenti->save('KpSituazioneDocumenti', $longdesc=true, $offline_update=false, $triggerEvent=false);

		$kpsituazionedocumentiid = $new_situazione_documenti->id;	
		
		echo "<br>----------- CREATO record ".$kpsituazionedocumentiid."<br>";
	}

	if($documento != "" && $documento != 0 && $stato_documento != ""){
		$upd = "UPDATE {$table_prefix}_notes
			SET stato_documento = '".$stato_documento."'
			WHERE notesid = ".$documento;
		$adb->query($upd);
	}
	
}

function calcolaSituazioneDocumentiFornitori(){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	printf("<br />Calcolo situazione documenti fornitori iniziato!");
	 
	$default_in_scadenza = 0;
	 
	$lista_fornitori = getFornitoriPerSituazioneDocumentiFornitori();
	
	foreach($lista_fornitori as $fornitore){
		
		printf("<br />--- Fornitore: ".$fornitore['id']);
		
		calcolaSituazioneDocumentiFornitoriFornitore($fornitore['id'], $default_in_scadenza);
		
	}
	
	printf("<br />Calcolo situazione documenti fornitori terminato!");
    
}

function getFornitoriPerSituazioneDocumentiFornitori(){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = array();
	
	$data_corrente = date("Y-m-d");
	
	$q_vendor = "SELECT vend.vendorid
				FROM {$table_prefix}_vendor vend
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = vend.vendorid
				WHERE ent.deleted = 0";
					
	//printf("<br />Query lista Fornitori: <br /> %s", $q_vendor); die;
	
    $res_vendor = $adb->query($q_vendor);
    $num_vendor = $adb->num_rows($res_vendor);

    for($i=0; $i<$num_vendor; $i++){		

        $vendorid = $adb->query_result($res_vendor,$i,'vendorid');
        $vendorid = html_entity_decode(strip_tags($vendorid), ENT_QUOTES, $default_charset);
		
		$result[] = array('id' => $vendorid);
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiFornitoriFornitore($fornitore, $default_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
    
    $q_vecchi = "UPDATE {$table_prefix}_kpsituazionedocfornit SET
                    kp_aggiornato = '0'
                    WHERE kp_fornitore = ".$fornitore;
    $adb->query($q_vecchi);
	
	$lista_risorse = getRisorseFornitori($fornitore);
	
	foreach($lista_risorse as $risorsa){
		
		printf("<br />----- Risorsa Fornitore: ".$risorsa['id']);
		
		calcolaSituazioneDocumentiFornitoriRisorsa($fornitore, $risorsa['id'], $default_in_scadenza);
		
	}

	calcolaSituazioneDocumentiFornitoriRisorsa($fornitore, 0, $default_in_scadenza);
	
	$upd = "UPDATE {$table_prefix}_crmentity ent
			INNER JOIN {$table_prefix}_kpsituazionedocfornit sitdoc ON sitdoc.kpsituazionedocfornitid = ent.crmid
			SET
			ent.deleted = 1
			WHERE sitdoc.kp_aggiornato != '1' AND sitdoc.kp_fornitore = ".$fornitore;
	$adb->query($upd);
    
}

function getRisorseFornitori($fornitore){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = array();
	
	$q = "SELECT cont.contactid
		FROM {$table_prefix}_contactdetails cont
		INNER JOIN {$table_prefix}_vendor vend ON vend.vendorid = cont.vendor_id
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cont.contactid
		WHERE ent.deleted = 0 AND vend.vendorid = ".$fornitore;
					
	//printf("<br />Query lista Risorse Fornitori: <br /> %s", $q); die;
	
    $res = $adb->query($q);
    $num = $adb->num_rows($res);

    for($i=0; $i<$num; $i++){		

        $contactid = $adb->query_result($res,$i,'contactid');
		$contactid = html_entity_decode(strip_tags($contactid), ENT_QUOTES, $default_charset);
		if($contactid == '' || $contactid == null){
			$contactid = 0;
		}
		
		$result[] = array('id' => $contactid);
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiFornitoriRisorsa($fornitore, $risorsa, $default_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	$lista_tipi_documenti = getTipiDocumentiFornitore($fornitore, $risorsa);
	
	foreach($lista_tipi_documenti as $tipo_documento){
		
		printf("<br />--------- Tipo Documento: ".$tipo_documento['id']);

		$giorni_in_scadenza = getGiorniInScadenzaFornitore($fornitore, $default_in_scadenza, 'Documenti', $tipo_documento['id']);

		printf(" Giorni in scadenza: ".$giorni_in_scadenza);
		
		calcolaSituazioneDocumentiFornitoriTipoDocumento($fornitore, $risorsa, $tipo_documento['id'], $tipo_documento['stato'], $giorni_in_scadenza, "si");
		
	}
    
}

function getTipiDocumentiFornitore($fornitore, $risorsa){
    global $adb, $table_prefix, $current_user, $default_charset;
	
	/* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	
	$result = array();

	$data_corrente = date('Y-m-d');
	
	$q = "SELECT td.tipidocumentiid,
		tdf.kp_stato_tipo_doc_a,
		tdf.kp_data_inizio,
		tdf.kp_data_fine
		FROM {$table_prefix}_kptipidocumentifornit tdf
		INNER JOIN {$table_prefix}_tipidocumenti td ON td.tipidocumentiid = tdf.kp_tipo_documento
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = tdf.kptipidocumentifornitid
		INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = td.tipidocumentiid
		WHERE ent.deleted = 0 AND ent1.deleted = 0
		AND tdf.kp_fornitore = ".$fornitore;
	if($risorsa != 0){
		$q .= " AND tdf.kp_risorsa_fornit = ".$risorsa;
	}
	else{
		$q .= " AND (tdf.kp_risorsa_fornit IS NULL OR tdf.kp_risorsa_fornit = 0 OR tdf.kp_risorsa_fornit = '')";
	}
	
	$res = $adb->query($q);
	$num = $adb->num_rows($res);
	
	for($i = 0; $i < $num; $i++){	

		$tipo_documento = $adb->query_result($res, $i, 'tipidocumentiid');
		$tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES, $default_charset);

		$stato_tipo_documento = $adb->query_result($res, $i, 'kp_stato_tipo_doc_a');
		$stato_tipo_documento = html_entity_decode(strip_tags($stato_tipo_documento), ENT_QUOTES, $default_charset);

		$data_inizio = $adb->query_result($res, $i, 'kp_data_inizio');
		$data_inizio = html_entity_decode(strip_tags($data_inizio), ENT_QUOTES, $default_charset);
		if($data_inizio == null || $data_inizio == '0000-00-00'){
			$data_inizio = "";
		}

		$data_fine = $adb->query_result($res, $i, 'kp_data_fine');
		$data_fine = html_entity_decode(strip_tags($data_fine), ENT_QUOTES, $default_charset);
		if($data_fine == null || $data_fine == '0000-00-00'){
			$data_fine = "";
		}

		if($data_inizio != "" && $data_fine != "" && $data_corrente >= $data_inizio && $data_corrente <= $data_fine){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		elseif($data_inizio == "" && $data_fine != "" && $data_corrente <= $data_fine){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		elseif($data_inizio != "" && $data_fine == "" && $data_corrente >= $data_inizio){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		elseif($data_inizio == "" && $data_fine == ""){
			$result[] = array(
				'id' => $tipo_documento,
				'stato' => $stato_tipo_documento
			);
		}
		
	}
	
	return $result;
	
}

function calcolaSituazioneDocumentiFornitoriTipoDocumento($fornitore, $risorsa, $tipo_documento, $stato_tipo_documento, $giorni_in_scadenza, $aggiorna){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	$dati_tipo_documento = getDatiTipoDocumento($tipo_documento);
        
    printf(", Nome: ".$dati_tipo_documento['nome'].", Validità: ".$dati_tipo_documento['validita']);
    
    $lista_documenti_redatti = getDocumentiFornitoreRedattiRisorsaTipoDocumento($fornitore, $risorsa, $tipo_documento, $giorni_in_scadenza);
 
	if(count($lista_documenti_redatti) == 0){
		
		$stato_documento = 'Non eseguito';
		$data_documento = '';
		$validita_documento = '';
		if($risorsa != 0){
			$nota_stato = "Nota stato situazione documenti fornitori: Il documento NON è stato eseguito in quanto non sono stati trovati documenti di questo tipo collegati alla risorsa.";
		}
		else{
			$nota_stato = "Nota stato situazione documenti fornitori: Il documento NON è stato eseguito in quanto non sono stati trovati documenti di questo tipo collegati al fornitore.";
		}
		
	}
	else{
		
		$stato_documento = $lista_documenti_redatti[0]['stato'];
		$data_documento = $lista_documenti_redatti[0]['data_documento'];
		$validita_documento = $lista_documenti_redatti[0]['data_validita'];
		$nota_stato = $lista_documenti_redatti[0]['nota_stato'];
		
	}
	
	printf("<br />----------- Stato: %s <br />----------- Data Documento: %s <br />----------- Data Validità: %s <br />----------- Nota: %s", $stato_documento, $data_documento, $validita_documento, $nota_stato);
            
    if($aggiorna == "si"){
		
		setSituazioneDocumentiFornitore($fornitore, $risorsa, $tipo_documento, $data_documento, $validita_documento, $stato_documento, $stato_tipo_documento, $nota_stato, $lista_documenti_redatti);
        
    }
    
}

function getDocumentiFornitoreRedattiRisorsaTipoDocumento($fornitore, $risorsa, $tipo_documento, $giorni_in_scadenza){
    global $adb, $table_prefix, $current_user, $default_charset;
    
    /* kpro@bid28052018 */

    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     */
	 
	$result = array();

	$data_corrente = date("Y-m-d");
	$data_corrente_inv = date("d-m-Y");
	list($anno, $mese, $giorno) = explode("-", $data_corrente);
	$in_scadenza = date("Y-m-d", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
	$in_scadenza_inv = date("d-m-Y", mktime(0, 0, 0, $mese, (int)$giorno + $giorni_in_scadenza, $anno));
	 
	$q_documenti = "SELECT note.notesid,
					note.title,
					note.kp_data_documento,
					note.data_scadenza
					FROM {$table_prefix}_notes note
					INNER JOIN {$table_prefix}_tipidocumenti td ON td.tipidocumentiid = note.kp_tipo_documento
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = note.notesid
					INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = td.tipidocumentiid
					WHERE ent.deleted = 0 AND ent1.deleted = 0
					AND td.tipidocumentiid = ".$tipo_documento."
					ORDER BY note.kp_data_documento DESC";
		
	$res_documenti = $adb->query($q_documenti);
    $num_documenti = $adb->num_rows($res_documenti);
    for($i = 0; $i < $num_documenti; $i++){
		
		$notesid = $adb->query_result($res_documenti, $i, 'notesid');
		$notesid = html_entity_decode(strip_tags($notesid), ENT_QUOTES, $default_charset);
		
		if($risorsa != 0){
			$verifica_documento = verificaRelazioneDocumentoRisorsa($risorsa, $notesid);
		}
		else{
			$verifica_documento = verificaRelazioneDocumentoFornitore($fornitore, $notesid);
		}
		
		if($verifica_documento){

			$titolo_documento = $adb->query_result($res_documenti, $i, 'title');
			$titolo_documento = html_entity_decode(strip_tags($titolo_documento), ENT_QUOTES, $default_charset);
			
			$data_documento = $adb->query_result($res_documenti, $i, 'kp_data_documento');
			$data_documento = html_entity_decode(strip_tags($data_documento), ENT_QUOTES, $default_charset);
			
			$data_scadenza = $adb->query_result($res_documenti, $i, 'data_scadenza');
			$data_scadenza = html_entity_decode(strip_tags($data_scadenza), ENT_QUOTES, $default_charset);
			if($data_scadenza != "" && $data_scadenza != null && $data_scadenza != "0000-00-00"){ /* kpro@bid310820180850 */
				list($anno_scad, $mese_scad, $giorno_scad) = explode("-", $data_scadenza);
				$data_scadenza_inv = date("d-m-Y", mktime(0, 0, 0, $mese_scad, $giorno_scad, $anno_scad));
				$in_scadenza_reale_inv = date("d-m-Y", mktime(0, 0, 0, $mese_scad, (int)$giorno_scad - $giorni_in_scadenza, $anno_scad));
				
				if($data_scadenza == '2099-12-31' || $data_scadenza == '2999-12-31' || $data_scadenza == '9999-12-31'){
					$data_scadenza = '';
					$stato = 'Valido senza scadenza';
					$nota_stato = "Nota stato situazione documenti fornitori: Il documento e' 'Valido senza scadenza' in quanto l'ultimo documento redatto ha data scadenza vuota o pari a '31-12-2099' oppure '31-12-2999'.";
				}
				elseif($data_scadenza > $data_corrente && $data_scadenza <= $in_scadenza){
					$stato = 'In scadenza';
					$nota_stato = "Nota stato situazione documenti fornitori: Il documento e' 'In scadenza' in quanto la data della scadenza dell'ultimo documento redatto (".$data_scadenza_inv.") risulta compresa tra la data corrente (".$data_corrente_inv.") e la data in cui andra' 'In scadenza'(".$in_scadenza_reale_inv.").";
				}
				elseif($data_scadenza >= $in_scadenza){
					$stato = 'In corso di validita';
					$nota_stato = "Nota stato situazione documenti fornitori: Il documento e' 'In corso di validita' in quanto la data della scadenza dell'ultimo documento redatto (".$data_scadenza_inv.") risulta maggiore della data ".$in_scadenza_reale_inv." in cui andra' 'In scadenza'.";
				}
				else{
					$stato = 'Scaduto';
					$nota_stato = "Nota stato situazione documenti fornitori: Il documento e' 'Scaduto' in quanto la data della scadenza dell'ultimo documento redatto (".$data_scadenza_inv.") risulta inferiore alla data odierna (".$data_corrente_inv.").";
				}
			/* kpro@bid310820180850 */
			}
			else{
				$data_scadenza = '';
				$stato = 'Valido senza scadenza';
				$nota_stato = "Nota stato situazione documenti fornitori: Il documento e' 'Valido senza scadenza' in quanto l'ultimo documento redatto ha data scadenza vuota o pari a '31-12-2099' oppure '31-12-2999'.";
			}
			/* kpro@bid310820180850 end */
			
			$result[] = array(
				'id' => $notesid,
				'nome' => $titolo_documento,
				'data_documento' => $data_documento,
				'data_validita' => $data_scadenza,
				'stato' => $stato,
				'nota_stato' => $nota_stato
			);
		}
		
	}
	
	return $result;
	
}

function verificaRelazioneDocumentoFornitore($fornitore, $notesid){
	global $adb, $table_prefix, $current_user, $default_charset;

	$result = false;

	$q = "SELECT note.notesid
		FROM {$table_prefix}_notes note
		INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
		INNER JOIN {$table_prefix}_vendor vend ON vend.vendorid = noterel.crmid
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = vend.vendorid
		WHERE ent.deleted = 0 AND note.notesid = ".$notesid."
		AND vend.vendorid = ".$fornitore;
	$res = $adb->query($q);
	if($adb->num_rows($res) > 0){
		$result = true;
	}

	return $result;
}

function verificaRelazioneDocumentoRisorsa($risorsa, $notesid){
	global $adb, $table_prefix, $current_user, $default_charset;

	$result = false;

	$q = "SELECT note.notesid
		FROM {$table_prefix}_notes note
		INNER JOIN {$table_prefix}_senotesrel noterel ON noterel.notesid = note.notesid
		INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = noterel.crmid
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cont.contactid
		WHERE ent.deleted = 0 AND note.notesid = ".$notesid."
		AND cont.contactid = ".$risorsa;
	$res = $adb->query($q);
	if($adb->num_rows($res) > 0){
		$result = true;
	}

	return $result;
}

function setSituazioneDocumentiFornitore($fornitore, $risorsa, $tipo_documento, $data_documento, $validita_documento, $stato_documento, $stato_tipo_documento, $nota_stato, $lista_documenti_redatti){
	global $adb, $table_prefix, $current_user, $default_charset;
	
	$kpsituazionedocfornitid = 0;

	$documento = $lista_documenti_redatti[0]['id'];
	if($documento == '' || $documento == null){
		$documento = 0;
	}

	//print_r($fornitore.' - '.$risorsa.' - '.$tipo_documento.' - '.$documento.' - '.$data_documento.' - '.$validita_documento.' - '.$stato_documento.' - '.$nota_stato);
		
	$q_verifica = "SELECT sitdoc.kpsituazionedocfornitid 
				FROM {$table_prefix}_kpsituazionedocfornit sitdoc
				INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sitdoc.kpsituazionedocfornitid
				WHERE ent.deleted = 0 AND sitdoc.kp_fornitore = ".$fornitore."
				AND sitdoc.kp_tipo_documento = ".$tipo_documento;
	if($risorsa != 0){
		$q .= " AND sitdoc.kp_risorsa_fornit = ".$risorsa;
	}
	else{
		$q .= " AND (sitdoc.kp_risorsa_fornit IS NULL OR sitdoc.kp_risorsa_fornit = 0 OR sitdoc.kp_risorsa_fornit = '')";
	}

	$res_verifica = $adb->query($q_verifica);
	if($adb->num_rows($res_verifica)>0){
		
		$kpsituazionedocfornitid = $adb->query_result($res_verifica,0,'kpsituazionedocfornitid');
		$kpsituazionedocfornitid = html_entity_decode(strip_tags($kpsituazionedocfornitid), ENT_QUOTES,$default_charset);

		$nota_stato = addslashes($nota_stato); /* kpro@bid310820180850 */
		
		$upd = "UPDATE {$table_prefix}_kpsituazionedocfornit SET
				kp_fornitore = ".$fornitore.",
				kp_risorsa_fornit = ".$risorsa.",
				kp_tipo_documento = ".$tipo_documento.",
				kp_documento = ".$documento.",
				kp_data_documento = '".$data_documento."',
				kp_validita_doc = '".$validita_documento."',
				kp_stato_sit_doc_f = '".$stato_documento."',
				kp_stato_tipo_doc_a = '".$stato_tipo_documento."',
				kp_aggiornato = '1',
				description = '".$nota_stato."'
				WHERE kpsituazionedocfornitid = ".$kpsituazionedocfornitid;
		$adb->query($upd);
		
		echo "<br>----------- AGGIORNATO record ".$kpsituazionedocfornitid."<br>";
	}
	else{
		
		$new_situazione_documenti = CRMEntity::getInstance('KpSituazioneDocFornit'); 
		$new_situazione_documenti->column_fields['assigned_user_id'] = 1;
		$new_situazione_documenti->column_fields['creator'] = 1;
		if($fornitore != "" && $fornitore != 0){
			$new_situazione_documenti->column_fields['kp_fornitore'] = $fornitore;
		}
		if($risorsa != "" && $risorsa != 0){
			$new_situazione_documenti->column_fields['kp_risorsa_fornit'] = $risorsa;
		}
		if($tipo_documento != "" && $tipo_documento != 0){
			$new_situazione_documenti->column_fields['kp_tipo_documento'] = $tipo_documento;
		}
		if($documento != "" && $documento != 0){
			$new_situazione_documenti->column_fields['kp_documento'] = $documento;
		}
		if($data_documento != ""){
			$new_situazione_documenti->column_fields['kp_data_documento'] = $data_documento;
		}
		if($validita_documento != ""){
			$new_situazione_documenti->column_fields['kp_validita_doc'] = $validita_documento;
		}
		if($stato_documento != ""){
			$new_situazione_documenti->column_fields['kp_stato_sit_doc_f'] = $stato_documento;
		}
		if($stato_tipo_documento != ""){
			$new_situazione_documenti->column_fields['kp_stato_tipo_doc_a'] = $stato_tipo_documento;
		}
		if($nota_stato != ""){
			$new_situazione_documenti->column_fields['description'] = $nota_stato;
		}
		$new_situazione_documenti->column_fields['kp_aggiornato'] = '1';
		$new_situazione_documenti->save('KpSituazioneDocFornit', $longdesc=true, $offline_update=false, $triggerEvent=false);

		$kpsituazionedocfornitid = $new_situazione_documenti->id;	
		
		echo "<br>----------- CREATO record ".$kpsituazionedocfornitid."<br>";
	}

	if($documento != "" && $documento != 0 && $stato_documento != ""){
		$upd = "UPDATE {$table_prefix}_notes
			SET stato_documento = '".$stato_documento."'
			WHERE notesid = ".$documento;
		$adb->query($upd);
	}
	
}

function recuperaNumeroAttestato($documento){
    global $adb, $table_prefix,$current_user;
    
    /**
     * @author Bidese Jacopo
     * @copyright (c) 2018, Kpro Consulting Srl
     * 
     * Questo script recupera il numero dell'attestato
     */

	$doc_number = "";
            
	$q_numeratore = "SELECT num.use_prefix, 
					num.start_sequence, 
					num.modulenumberingid
					FROM {$table_prefix}_modulenumbering num
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
					WHERE ent.deleted = 0 AND num.select_module = '8A'";

	$res_numeratore = $adb->query($q_numeratore);
	if($adb->num_rows($res_numeratore)>0){
		$use_prefix = $adb->query_result($res_numeratore, 0, 'use_prefix'); 
		$use_prefix = html_entity_decode(strip_tags($use_prefix), ENT_QUOTES,$default_charset);

		$start_sequence = $adb->query_result($res_numeratore, 0, 'start_sequence'); 
		$start_sequence = html_entity_decode(strip_tags($start_sequence), ENT_QUOTES,$default_charset);
		
		$modulenumberingid = $adb->query_result($res_numeratore, 0, 'modulenumberingid'); 
		$modulenumberingid = html_entity_decode(strip_tags($modulenumberingid), ENT_QUOTES,$default_charset);
		
		$doc_number = $use_prefix.$start_sequence;
					
		$upd_doc = "UPDATE {$table_prefix}_notes
					SET kp_num_doc_spec = '".$doc_number."'
					WHERE notesid =".$documento;
		$adb->query($upd_doc);
		
		$length_sequence = strlen($start_sequence);			
		$start_sequence = (int)$start_sequence;

		$start_sequence++;
		$start_sequence = str_pad($start_sequence, $length_sequence, "0", STR_PAD_LEFT);
		
		$upd_numeratore = "UPDATE {$table_prefix}_modulenumbering
							SET start_sequence ='".$start_sequence."'
							WHERE modulenumberingid =".$modulenumberingid;
		$adb->query($upd_numeratore);
						
	}

	return $doc_number;
 
}

?>