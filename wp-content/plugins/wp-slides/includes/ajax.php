<?php
function company_name() {
		$term = strtolower( $_GET['term'] );
		$response = wp_remote_get('https://autocomplete.clearbit.com/v1/companies/suggest?query='. $term);
		$response_body = wp_remote_retrieve_body($response);
		$temp = json_decode($response_body);
		$result = [];

		if(!empty($temp)){
			foreach ($temp as $key => $value) {
				$result[$key]['name'] = $value->name;
				$result[$key]['domain'] = $value->domain;
				$result[$key]['logo'] = $value->logo;
				$result[$key]['id'] = $key;
	    	}
		}

		echo json_encode($result);
    	exit();

}

add_action( 'wp_ajax_company_name', 'company_name' );
add_action( 'wp_ajax_nopriv_company_name', 'company_name' );